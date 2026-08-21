export type ColorModePreference = 'light' | 'dark' | 'system';

export interface RuntimeConfigInput {
  baseUrl?: string;
  appPath?: string;
  apiBaseUrl?: string;
  locale?: string;
  timezone?: string;
  colorMode?: ColorModePreference;
}

export interface RuntimeConfig {
  baseUrl: string;
  appPath: string;
  routerBasePath: string;
  apiBaseUrl: string;
  locale: string;
  timezone: string;
  colorMode: ColorModePreference;
}

function cleanSegment(value: string): string {
  return value.trim().replace(/^\/+|\/+$/g, '');
}

export function normalizeBaseUrl(value = '/'): string {
  const cleaned = cleanSegment(value);
  return cleaned ? `/${cleaned}/` : '/';
}

export function normalizeRelativePath(value: string, fallback: string): string {
  const cleaned = cleanSegment(value || fallback);
  return cleaned ? `${cleaned}/` : '';
}

export function joinUrlPath(baseUrl: string, relativePath: string): string {
  const base = normalizeBaseUrl(baseUrl);
  const relative = cleanSegment(relativePath);
  return relative ? `${base}${relative}` : base;
}

function normalizeApiBaseUrl(value: string | undefined, baseUrl: string): string {
  const candidate = value?.trim();
  if (!candidate) return `${joinUrlPath(baseUrl, 'api/ui/v1')}/`;
  if (/^https?:\/\//i.test(candidate)) {
    throw new Error('UI API base URL must remain same-origin');
  }
  const absolute = candidate.startsWith('/')
    ? normalizeBaseUrl(candidate)
    : `${joinUrlPath(baseUrl, candidate)}/`;
  return absolute.replace(/\/{2,}/g, '/');
}

export function loadRuntimeConfig(input: RuntimeConfigInput = {}): RuntimeConfig {
  const baseUrl = normalizeBaseUrl(input.baseUrl);
  const appPath = normalizeRelativePath(input.appPath ?? 'ui/', 'ui/');
  const routerPath = joinUrlPath(baseUrl, appPath);
  const routerBasePath = routerPath === '/' ? '/' : routerPath.replace(/\/$/, '');

  return {
    baseUrl,
    appPath,
    routerBasePath,
    apiBaseUrl: normalizeApiBaseUrl(input.apiBaseUrl, baseUrl),
    locale: input.locale?.trim() || 'en',
    timezone: input.timezone?.trim() || 'UTC',
    colorMode: input.colorMode ?? 'light'
  };
}
