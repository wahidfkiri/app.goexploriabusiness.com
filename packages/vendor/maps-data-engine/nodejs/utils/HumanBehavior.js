class HumanBehavior {
  constructor(profile = {}) {
    this.profile = profile;
  }

  randomInt(min, max) {
    const lower = Math.min(min, max);
    const upper = Math.max(min, max);
    return Math.floor(Math.random() * (upper - lower + 1)) + lower;
  }

  async pause(min, max) {
    const duration = this.randomInt(min, max);
    await new Promise((resolve) => setTimeout(resolve, duration));
  }

  async warmUp(page) {
    await this.pause(this.profile.open_delay_min || 700, this.profile.open_delay_max || 1600);
    await this.moveMouseRandomly(page);
  }

  async moveMouseRandomly(page) {
    const viewport = page.viewportSize() || { width: 1366, height: 768 };
    const steps = this.profile.mouse_move_steps || 10;

    for (let index = 0; index < steps; index += 1) {
      const x = this.randomInt(20, Math.max(40, viewport.width - 20));
      const y = this.randomInt(20, Math.max(40, viewport.height - 20));
      await page.mouse.move(x, y, { steps: this.randomInt(4, 12) });
      await this.pause(35, 120);
    }
  }

  async typeLikeHuman(page, selector, text) {
    await page.locator(selector).click({ delay: this.randomInt(50, 120) });
    await page.locator(selector).fill('');

    const minDelay = this.profile.typing_delay_min || 60;
    const maxDelay = this.profile.typing_delay_max || 180;
    for (const character of String(text)) {
      await page.keyboard.type(character, { delay: this.randomInt(minDelay, maxDelay) });
    }
  }

  async progressiveFeedScroll(page, selector, iterations = 8) {
    const pauseMin = this.profile.scroll_pause_min || 450;
    const pauseMax = this.profile.scroll_pause_max || 1800;
    const feed = page.locator(selector).first();

    for (let index = 0; index < iterations; index += 1) {
      if (!(await feed.count())) {
        return;
      }

      await feed.hover();
      await page.mouse.wheel(0, this.randomInt(800, 1800));
      await this.pause(pauseMin, pauseMax);
    }
  }
}

module.exports = HumanBehavior;
