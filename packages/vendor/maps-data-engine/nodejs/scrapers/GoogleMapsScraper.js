const fs = require('fs-extra');
const path = require('path');

class GoogleMapsScraper {
  constructor(page, payload, humanBehavior) {
    this.page = page;
    this.payload = payload;
    this.humanBehavior = humanBehavior;
    this.screenshotsDir = payload.runtime?.screenshots_dir || null;
  }

  async run() {
    await this.openMapsHome();

    if (await this.isCaptchaPage()) {
      return await this.captchaResult('captcha-on-home');
    }

    const query = this.payload.segment?.query || this.payload.session?.category || 'businesses';
    await this.search(query);

    if (await this.isCaptchaPage()) {
      return await this.captchaResult('captcha-after-search');
    }

    let listingUrls = await this.collectListingUrls();
    if (!listingUrls.length && await this.isDetailsPage()) {
      listingUrls = [this.page.url()];
    }

    const items = [];
    const limit = Number(this.payload.session?.limit || 25);

    for (const url of listingUrls.slice(0, limit)) {
      await this.page.goto(url, { waitUntil: 'domcontentloaded' });
      await this.page.waitForLoadState('networkidle').catch(() => {});
      await this.humanBehavior.warmUp(this.page);

      if (await this.isCaptchaPage()) {
        return await this.captchaResult('captcha-on-detail', items);
      }

      const item = await this.extractCurrentListing();
      if (item?.name) {
        items.push(item);
      }
    }

    return {
      status: 'ok',
      items,
      business_status: 'active',
    };
  }

  async openMapsHome() {
    const locale = this.payload.anti_detection?.locale || 'en-CA';
    const latitude = this.payload.segment?.latitude;
    const longitude = this.payload.segment?.longitude;
    const baseUrl = latitude && longitude
      ? `https://www.google.com/maps/@${latitude},${longitude},12z?hl=${encodeURIComponent(locale)}`
      : `https://www.google.com/maps?hl=${encodeURIComponent(locale)}`;

    await this.page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
    await this.page.waitForLoadState('networkidle').catch(() => {});
    await this.acceptConsentIfPresent();
    await this.humanBehavior.warmUp(this.page);
  }

  async acceptConsentIfPresent() {
    const selectors = [
      'button[aria-label="Accept all"]',
      'button[aria-label="Tout accepter"]',
      'button:has-text("Accept all")',
      'button:has-text("Tout accepter")'
    ];

    for (const selector of selectors) {
      const locator = this.page.locator(selector).first();
      if (await locator.count()) {
        await locator.click({ delay: 100 }).catch(() => {});
        await this.humanBehavior.pause(500, 1200);
        return;
      }
    }
  }

  async search(query) {
    const inputSelector = await this.resolveSearchInputSelector();
    if (!inputSelector) {
      throw new Error('Google Maps search input not found.');
    }

    await this.humanBehavior.typeLikeHuman(this.page, inputSelector, query);
    await this.humanBehavior.pause(250, 700);

    const searchButtonSelector = await this.resolveSearchButtonSelector();
    if (searchButtonSelector) {
      await this.page.locator(searchButtonSelector).first().click({ delay: 90 }).catch(async () => {
        await this.page.keyboard.press('Enter');
      });
    } else {
      await this.page.keyboard.press('Enter');
    }

    await this.page.waitForLoadState('networkidle').catch(() => {});
    await this.page.waitForTimeout(1500).catch(() => {});
    await this.ensureSearchResultsLoaded(query);
  }

  async resolveSearchInputSelector() {
    const selectors = [
      'input#searchboxinput',
      'input[aria-label="Rechercher dans Google Maps"]',
      'input[aria-label="Search Google Maps"]',
      'input[placeholder*="Google Maps"]',
      'input[role="combobox"]'
    ];

    for (const selector of selectors) {
      const locator = this.page.locator(selector).first();
      const isVisible = await locator.isVisible().catch(() => false);
      if (isVisible) {
        return selector;
      }

      const count = await locator.count().catch(() => 0);
      if (count > 0) {
        await locator.waitFor({ state: 'visible', timeout: 5000 }).catch(() => {});
        if (await locator.isVisible().catch(() => false)) {
          return selector;
        }
      }
    }

    return null;
  }

  async resolveSearchButtonSelector() {
    const selectors = [
      'button#searchbox-searchbutton',
      'button[aria-label="Rechercher"]',
      'button[aria-label="Search"]'
    ];

    for (const selector of selectors) {
      const locator = this.page.locator(selector).first();
      if (await locator.count().catch(() => 0)) {
        return selector;
      }
    }

    return null;
  }

  async ensureSearchResultsLoaded(query) {
    for (let attempt = 0; attempt < 3; attempt += 1) {
      if (await this.hasSearchResults()) {
        return;
      }

      await this.clickSearchThisAreaIfPresent();

      if (!(await this.hasSearchResults()) && attempt === 1) {
        const locale = this.payload.anti_detection?.locale || 'en-CA';
        const encodedQuery = encodeURIComponent(String(query || '').trim());
        if (encodedQuery) {
          await this.page.goto(`https://www.google.com/maps/search/${encodedQuery}?hl=${encodeURIComponent(locale)}`, {
            waitUntil: 'domcontentloaded',
          }).catch(() => {});
        }
      }

      if (!(await this.hasSearchResults())) {
        await this.page.keyboard.press('Enter').catch(() => {});
      }

      await this.page.waitForLoadState('networkidle').catch(() => {});
      await this.page.waitForTimeout(1400).catch(() => {});
    }
  }

  async hasSearchResults() {
    return await this.page.evaluate(() => {
      const feedCount = document.querySelectorAll('div[role="feed"]').length;
      const placeLinks = document.querySelectorAll('a[href*="/place/"]').length;
      return feedCount > 0 || placeLinks > 0;
    }).catch(() => false);
  }

  async clickSearchThisAreaIfPresent() {
    const selectors = [
      'button:has-text("Rechercher dans cette zone")',
      'button:has-text("Search this area")',
      'button[aria-label="Rechercher dans cette zone"]',
      'button[aria-label="Search this area"]'
    ];

    for (const selector of selectors) {
      const locator = this.page.locator(selector).first();
      if (await locator.count().catch(() => 0)) {
        await locator.click({ delay: 80 }).catch(() => {});
        await this.page.waitForTimeout(800).catch(() => {});
        return;
      }
    }
  }

  async collectListingUrls() {
    const feedSelector = 'div[role="feed"]';
    const urls = new Set();
    let stablePasses = 0;
    let previousSize = 0;
    const requestedLimit = Number(this.payload.session?.limit || 25);
    const iterationLimit = Math.max(6, Math.min(Math.max(requestedLimit * 2, 6), 16));

    await this.page.waitForSelector(feedSelector, { timeout: 20000 }).catch(() => {});
    await this.page.waitForTimeout(1200).catch(() => {});

    for (let index = 0; index < iterationLimit; index += 1) {
      const currentUrls = await this.page.evaluate(() => {
        return Array.from(document.querySelectorAll('a[href*="/place/"]'))
          .map((link) => link.href)
          .filter(Boolean);
      }).catch(() => []);

      currentUrls.forEach((url) => urls.add(url));

      if (urls.size === previousSize) {
        stablePasses += 1;
      } else {
        stablePasses = 0;
        previousSize = urls.size;
      }

      if (stablePasses >= 3) {
        break;
      }

      await this.humanBehavior.progressiveFeedScroll(this.page, feedSelector, 1);
      await this.page.waitForTimeout(350).catch(() => {});
    }

    return Array.from(urls);
  }

  async isDetailsPage() {
    return await this.page.locator('h1').count().then((count) => count > 0).catch(() => false);
  }

  async extractCurrentListing() {
    await this.page.waitForSelector('h1', { timeout: 25000 }).catch(() => {});
    await this.humanBehavior.pause(600, 1500);

    return await this.page.evaluate(() => {
      const text = (selector) => document.querySelector(selector)?.textContent?.trim() || null;
      const href = (selector) => document.querySelector(selector)?.href || null;
      const ratingLabel = document.querySelector('div[role="img"][aria-label*="star"]')?.getAttribute('aria-label') || null;
      const mapsUrl = window.location.href;
      const coordinatesMatch = mapsUrl.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/) || mapsUrl.match(/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/);
      const hoursButton = document.querySelector('button[data-item-id="oh"]');
      const socialCandidates = Array.from(document.querySelectorAll('a[href]')).map((node) => node.href).filter(Boolean);

      const categories = Array.from(document.querySelectorAll('button[jsaction*="pane.rating.category"], button[aria-label*="Category"], a[href*="/search/"]'))
        .map((node) => node.textContent?.trim())
        .filter(Boolean)
        .slice(0, 6);

      const images = Array.from(document.querySelectorAll('img[src*="googleusercontent"], img[src*="gstatic.com"]'))
        .map((img) => img.src)
        .filter(Boolean)
        .slice(0, 12);

      const reviewsPreview = Array.from(document.querySelectorAll('div[data-review-id], div.jftiEf'))
        .slice(0, 3)
        .map((node) => {
          const author = node.querySelector('button, a')?.textContent?.trim() || null;
          const textNode = Array.from(node.querySelectorAll('span, div')).find((child) => (child.textContent || '').trim().length > 40);
          return {
            author,
            text: textNode?.textContent?.trim() || null,
          };
        })
        .filter((review) => review.author || review.text);

      const socialLinks = {};
      for (const candidate of socialCandidates) {
        const normalized = candidate.toLowerCase();
        if (normalized.includes('facebook.com') && !socialLinks.facebook) socialLinks.facebook = candidate;
        if (normalized.includes('instagram.com') && !socialLinks.instagram) socialLinks.instagram = candidate;
        if (normalized.includes('linkedin.com') && !socialLinks.linkedin) socialLinks.linkedin = candidate;
        if (normalized.includes('youtube.com') && !socialLinks.youtube) socialLinks.youtube = candidate;
        if (normalized.includes('tiktok.com') && !socialLinks.tiktok) socialLinks.tiktok = candidate;
        if (normalized.includes('wa.me') && !socialLinks.whatsapp) socialLinks.whatsapp = candidate;
      }

      const ratingMatch = ratingLabel ? ratingLabel.match(/(\d+[\.,]?\d*)/) : null;
      const reviewsMatch = ratingLabel ? ratingLabel.match(/([\d,\.]+)\s+reviews?/i) : null;

      return {
        name: text('h1'),
        address: text('button[data-item-id="address"], [data-item-id="address"]'),
        latitude: coordinatesMatch ? Number(coordinatesMatch[1]) : null,
        longitude: coordinatesMatch ? Number(coordinatesMatch[2]) : null,
        website: href('a[data-item-id="authority"], a[href^="http"]:not([href*="google.com"])'),
        phone: text('button[data-item-id*="phone"], [data-item-id*="phone"]'),
        rating: ratingMatch ? Number(ratingMatch[1].replace(',', '.')) : null,
        reviews_count: reviewsMatch ? Number(reviewsMatch[1].replace(/[^\d]/g, '')) : 0,
        categories: Array.from(new Set(categories)),
        opening_hours: hoursButton ? [hoursButton.textContent.trim()] : [],
        google_maps_url: mapsUrl,
        images: Array.from(new Set(images)),
        reviews_preview: reviewsPreview,
        social_links: socialLinks,
      };
    });
  }

  async isCaptchaPage() {
    const title = (await this.page.title().catch(() => '')).toLowerCase();
    const bodyText = (await this.page.locator('body').innerText().catch(() => '')).toLowerCase();
    const markers = [
      'not a robot',
      'unusual traffic',
      'suspicious activity',
      'sorry, but your computer or network may be sending automated queries',
      'captcha',
      'recaptcha'
    ];

    return markers.some((marker) => title.includes(marker) || bodyText.includes(marker));
  }

  async captchaResult(label, items = []) {
    const screenshot = await this.takeDebugScreenshot(label);
    return {
      status: 'captcha',
      items,
      business_status: 'captcha',
      incident_screenshot: screenshot,
    };
  }

  async takeDebugScreenshot(label) {
    if (!this.screenshotsDir) {
      return null;
    }

    const fileName = `${Date.now()}-${label}.png`;
    const filePath = path.join(this.screenshotsDir, fileName);
    await fs.ensureDir(this.screenshotsDir);
    await this.page.screenshot({ path: filePath, fullPage: true }).catch(() => {});
    return filePath;
  }
}

module.exports = GoogleMapsScraper;
