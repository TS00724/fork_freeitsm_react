import { describe, expect, it } from 'vitest';
import { loadRuntimeConfig, normalizeBaseUrl } from '../src/config/runtimeConfig';

describe('runtime config', () => {
  it('normalizes a subdirectory deployment', () => {
    expect(loadRuntimeConfig({ baseUrl: '/freeitsm-app', appPath: '/app/' })).toMatchObject({
      baseUrl: '/freeitsm-app/',
      appPath: 'app/',
      routerBasePath: '/freeitsm-app/app',
      apiBaseUrl: '/freeitsm-app/api/ui/v1/'
    });
  });

  it('normalizes root deployment', () => {
    expect(normalizeBaseUrl('/')).toBe('/');
  });

  it('rejects a cross-origin UI API base', () => {
    expect(() => loadRuntimeConfig({ apiBaseUrl: 'https://example.invalid/api/' })).toThrow('same-origin');
  });
});
