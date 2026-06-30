const { anonymizeProxy, closeAnonymizedProxy } = require('proxy-chain');

class ProxyManager {
  async prepare(proxy) {
    if (!proxy?.server) {
      return null;
    }

    if (!proxy.username && !proxy.password) {
      return {
        playwrightProxy: { server: proxy.server },
        cleanupUrl: null,
      };
    }

    const authenticatedUrl = this.buildAuthenticatedUrl(proxy);
    const anonymizedUrl = await anonymizeProxy(authenticatedUrl, true);

    return {
      playwrightProxy: { server: anonymizedUrl },
      cleanupUrl: anonymizedUrl,
    };
  }

  buildAuthenticatedUrl(proxy) {
    const server = new URL(proxy.server);
    if (proxy.username) {
      server.username = encodeURIComponent(proxy.username);
    }
    if (proxy.password) {
      server.password = encodeURIComponent(proxy.password);
    }
    return server.toString();
  }

  async dispose(configuration) {
    if (configuration?.cleanupUrl) {
      await closeAnonymizedProxy(configuration.cleanupUrl, true).catch(() => {});
    }
  }
}

module.exports = ProxyManager;
