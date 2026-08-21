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

  it('accepts an explicit UI API path contained by the deployment base', () => {
    expect(loadRuntimeConfig({
      baseUrl: '/freeitsm-app/',
      apiBaseUrl: '/freeitsm-app/api/ui/v1/'
    }).apiBaseUrl).toBe('/freeitsm-app/api/ui/v1/');
  });

  it('rejects a UI API path outside the deployment base', () => {
    expect(() => loadRuntimeConfig({
      baseUrl: '/freeitsm-app/',
      apiBaseUrl: '/api/ui/v1/'
    })).toThrow('within the configured base URL');
  });

  it.each([
    { apiBaseUrl: '/\\example.invalid/api/' },
    { apiBaseUrl: 'api/ui/v1/../../outside/' },
    { apiBaseUrl: 'api/ui/v1/%252e%252e/outside/' }
  ])('rejects unsafe UI API path input: $apiBaseUrl', ({ apiBaseUrl }) => {
    expect(() => loadRuntimeConfig({ apiBaseUrl })).toThrow();
  });

  it.each(['../ui/', '%2e%2e/ui/', 'ui/\\admin/'])(
    'rejects an unsafe runtime app path: %s',
    (appPath) => {
      expect(() => loadRuntimeConfig({ appPath })).toThrow();
    }
  );

  it.each([
    'https://example.invalid/freeitsm/',
    '//example.invalid/freeitsm/',
    '/\\example.invalid/freeitsm/',
    '/freeitsm/../other/'
  ])('rejects an unsafe deployment base URL: %s', (baseUrl) => {
    expect(() => loadRuntimeConfig({ baseUrl })).toThrow();
  });

  it('canonicalizes a supported locale and validates an IANA timezone', () => {
    expect(loadRuntimeConfig({ locale: 'zh-cn', timezone: 'Asia/Taipei' })).toMatchObject({
      locale: 'zh-CN',
      timezone: 'Asia/Taipei'
    });
  });

  it.each(['en_US', 'fr'])(`rejects invalid or unsupported locale: %s`, (locale) => {
    expect(() => loadRuntimeConfig({ locale })).toThrow('Locale');
  });

  it('rejects an invalid timezone', () => {
    expect(() => loadRuntimeConfig({ timezone: 'Not/A_Timezone' })).toThrow('Timezone');
  });

  it('rejects an invalid runtime color mode', () => {
    expect(() => loadRuntimeConfig({ colorMode: 'sepia' as never })).toThrow('Color mode');
  });
});
