const UserAgent = require('user-agents');
const randomUseragent = require('random-useragent');

class AntiDetectionManager {
  constructor(payload) {
    this.payload = payload;
  }

  buildUserAgent(hint) {
    const filters = {
      'chrome-windows': (value) => /Windows NT/.test(value) && /Chrome\//.test(value) && !/Edg\//.test(value),
      'chrome-mac': (value) => /Mac OS X/.test(value) && /Chrome\//.test(value),
      'edge-windows': (value) => /Windows NT/.test(value) && /Edg\//.test(value),
    };

    const filtered = randomUseragent.getRandom(filters[hint] || (() => true));
    if (filtered) {
      return filtered;
    }

    return new UserAgent({ deviceCategory: 'desktop' }).toString();
  }

  async buildContextOptions() {
    const antiDetection = this.payload.anti_detection || {};
    const locale = antiDetection.locale || 'en-CA';
    const timezoneId = antiDetection.timezone || 'America/Toronto';
    const viewport = antiDetection.viewport || { width: 1440, height: 900 };

    return {
      viewport,
      locale,
      timezoneId,
      userAgent: this.buildUserAgent(antiDetection.user_agent_family || 'chrome-windows'),
      colorScheme: Math.random() > 0.7 ? 'dark' : 'light',
      ignoreHTTPSErrors: true,
    };
  }

  async applyPageDefaults(page) {
    const locale = this.payload.anti_detection?.locale || 'en-CA';

    await page.setExtraHTTPHeaders({
      'Accept-Language': `${locale},en;q=0.9`,
      'DNT': '1',
      'Upgrade-Insecure-Requests': '1',
    });

    page.setDefaultTimeout(45000);
    page.setDefaultNavigationTimeout(60000);
  }
}

module.exports = AntiDetectionManager;
