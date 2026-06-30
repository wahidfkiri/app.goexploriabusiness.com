#!/usr/bin/env node
const fs = require('fs-extra');
const path = require('path');
const BrowserManager = require('../browser/BrowserManager');
const AntiDetectionManager = require('../stealth/AntiDetectionManager');
const ProxyManager = require('../proxy/ProxyManager');
const HumanBehavior = require('../utils/HumanBehavior');
const GoogleMapsScraper = require('./GoogleMapsScraper');

async function ensureDirectories(payload) {
  const directories = [
    payload.runtime?.screenshots_dir,
    payload.runtime?.result_dir,
    payload.runtime?.sessions_dir,
  ].filter(Boolean);

  await Promise.all(directories.map((directory) => fs.ensureDir(directory)));
}

async function writeResultCopy(payload, result) {
  const resultDir = payload.runtime?.result_dir;
  if (!resultDir) {
    return null;
  }

  await fs.ensureDir(resultDir);
  const filePath = path.join(resultDir, `${payload.session?.uuid || Date.now()}.json`);
  await fs.writeJson(filePath, result, { spaces: 2 });
  return filePath;
}

async function main() {
  const payloadPath = process.argv[2];
  if (!payloadPath) {
    throw new Error('Payload file path is required.');
  }

  const payload = await fs.readJson(payloadPath);
  await ensureDirectories(payload);

  const antiDetectionManager = new AntiDetectionManager(payload);
  const proxyManager = new ProxyManager();
  const browserManager = new BrowserManager(payload, antiDetectionManager, proxyManager);
  const humanBehavior = new HumanBehavior(payload.human_profile || {});

  let resources = null;

  try {
    resources = await browserManager.launch();
    const scraper = new GoogleMapsScraper(resources.page, payload, humanBehavior);
    const result = await scraper.run();

    const storageStatePath = await browserManager.persistStorageState(resources.context, resources.storageStatePath);
    const storageState = resources.context ? await resources.context.storageState() : null;
    const finalized = {
      ...result,
      storage_state: storageState,
      storage_state_path: storageStatePath,
    };

    finalized.result_file = await writeResultCopy(payload, finalized);

    await browserManager.close(resources, { persistState: false });
    process.stdout.write(JSON.stringify(finalized));
  } catch (error) {
    if (resources?.page && payload.runtime?.screenshots_dir) {
      const incidentPath = path.join(payload.runtime.screenshots_dir, `${Date.now()}-worker-error.png`);
      await resources.page.screenshot({ path: incidentPath, fullPage: true }).catch(() => {});
    }

    await browserManager.close(resources, { persistState: true }).catch(() => {});
    const message = error && error.message ? error.message : 'Unknown Playwright runtime error.';
    process.stderr.write(message);
    process.exit(1);
  }
}

main();
