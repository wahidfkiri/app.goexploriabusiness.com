const fs = require('fs-extra');
const path = require('path');
const { chromium } = require('playwright');

class BrowserManager {
  constructor(payload, antiDetectionManager, proxyManager) {
    this.payload = payload;
    this.antiDetectionManager = antiDetectionManager;
    this.proxyManager = proxyManager;
  }

  resolveStorageStatePath() {
    const configured = this.payload.browser_session?.storage_state_path;
    if (configured) {
      return configured;
    }

    const sessionKey = this.payload.browser_session?.session_key;
    const sessionsDir = this.payload.runtime?.sessions_dir;

    if (!sessionKey || !sessionsDir) {
      return null;
    }

    return path.join(sessionsDir, `${sessionKey}.json`);
  }

  async launch() {
    const proxyConfiguration = await this.proxyManager.prepare(this.payload.proxy || null);
    const launchOptions = {
      headless: Boolean(this.payload.runtime?.headless ?? true),
      args: [
        '--disable-dev-shm-usage',
        '--disable-features=TranslateUI',
        '--no-first-run',
        '--no-default-browser-check'
      ]
    };

    if (proxyConfiguration?.playwrightProxy) {
      launchOptions.proxy = proxyConfiguration.playwrightProxy;
    }

    const browser = await chromium.launch(launchOptions);
    const contextOptions = await this.antiDetectionManager.buildContextOptions();
    const storageStatePath = this.resolveStorageStatePath();

    if (storageStatePath && await fs.pathExists(storageStatePath)) {
      contextOptions.storageState = storageStatePath;
    }

    const context = await browser.newContext(contextOptions);
    const page = await context.newPage();

    await this.antiDetectionManager.applyPageDefaults(page);

    return {
      browser,
      context,
      page,
      storageStatePath,
      proxyConfiguration,
    };
  }

  async persistStorageState(context, storageStatePath) {
    if (!storageStatePath) {
      return null;
    }

    await fs.ensureDir(path.dirname(storageStatePath));
    await context.storageState({ path: storageStatePath });
    return storageStatePath;
  }

  async close(resources, options = {}) {
    const { browser, context, storageStatePath, proxyConfiguration } = resources || {};

    try {
      if (context && options.persistState !== false) {
        await this.persistStorageState(context, storageStatePath);
      }
    } finally {
      if (browser) {
        await browser.close().catch(() => {});
      }
      await this.proxyManager.dispose(proxyConfiguration).catch(() => {});
    }
  }
}

module.exports = BrowserManager;
