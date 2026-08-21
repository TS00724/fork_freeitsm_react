import { describe, expect, it } from 'vitest';
import { loadRuntimeConfig, normalizeBaseUrl } from '../src/config/runtimeConfig';

describe('runtime config', () => {
  it('normalizes a subdirectory deployment', () => {
    expect(loadRuntimeConfig({ baseUrl: '/freeitsm-app', appPath: '/ui/' })).toMatchObject({
      baseUrl: '/freeitsm-app/',
      appPath: 'ui/',
      routerBasePath: '/freeitsm-app/ui',
      apiBaseUrl: '/freeitsm-app/api/ui/v1/'
    });
  });

  it('uses approved G1 defaults', () => {
    expect(loadRuntimeConfig()).toMatchObject({
      appPath: 'ui/',
      routerBasePath: '/ui',
      locale: 'en',
      timezone: 'UTC',
      colorMode: 'light'
    });
  });

  it('normalizes root deployment', () => {
    expect(normalizeBaseUrl('/')).toBe('/');
  });

  it('rejects a cross-origin UI API base', () => {
    expect(() => loadRuntimeConfig({ apiBaseUrl: 'https://example.invalid/api/' })).toThrow('same-origin');
  });
});
