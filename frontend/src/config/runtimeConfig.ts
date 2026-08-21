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

const PATH_VALIDATION_ORIGIN = 'https://freeitsm-runtime.invalid';
const SUPPORTED_LOCALES = new Set(['en', 'zh-CN', 'zh-TW']);
const COLOR_MODES = new Set<ColorModePreference>(['light', 'dark', 'system']);

function requireString(value: unknown, label: string): string {
  if (typeof value !== 'string') throw new Error(`${label} must be a string`);
  return value.trim();
}

function decodePathSegment(segment: string, label: string): string {
  let decoded = segment;
  for (let pass = 0; pass < 3; pass += 1) {
    let next: string;
    try {
      next = decodeURIComponent(decoded);
    } catch {
      throw new Error(`${label} contains invalid percent encoding`);
    }
    if (next === decoded) break;
    decoded = next;
  }
  return decoded;
}

function containsControlCharacter(value: string): boolean {
  return [...value].some((character) => {
    const codePoint = character.codePointAt(0) ?? 0;
    return codePoint <= 0x1f || codePoint === 0x7f;
  });
}

function assertSafePath(value: string, label: string): void {
  if (value.includes('\\') || containsControlCharacter(value) || /%5c/i.test(value)) {
    throw new Error(`${label} must not contain backslashes or control characters`);
  }
  if (/^[a-z][a-z\d+.-]*:/i.test(value) || value.startsWith('//')) {
    throw new Error(`${label} must remain same-origin`);
  }
  if (/[?#]/.test(value)) throw new Error(`${label} must not contain a query or fragment`);

  for (const segment of value.split('/')) {
    const decoded = decodePathSegment(segment, label);
    if (decoded === '.' || decoded === '..') {
      throw new Error(`${label} must not contain dot segments`);
    }
    if (/[\\/]/.test(decoded)) {
      throw new Error(`${label} must not contain encoded path separators`);
    }
  }
}

function directoryPath(pathname: string): string {
  return pathname === '/' || pathname.endsWith('/') ? pathname : `${pathname}/`;
}

function isContainedPath(pathname: string, basePathname: string): boolean {
  const base = directoryPath(basePathname);
  return base === '/' || pathname === base.slice(0, -1) || pathname.startsWith(base);
}

function normalizePath(value: string, label: string): string {
  assertSafePath(value, label);
  const cleaned = value.replace(/^\/+|\/+$/g, '');
  const parsed = new URL(cleaned ? `/${cleaned}/` : '/', PATH_VALIDATION_ORIGIN);
  if (parsed.origin !== PATH_VALIDATION_ORIGIN) throw new Error(`${label} must remain same-origin`);
  return directoryPath(parsed.pathname);
}

export function normalizeBaseUrl(value = '/'): string {
  return normalizePath(requireString(value, 'Base URL'), 'Base URL');
}

export function normalizeRelativePath(value: string, fallback: string): string {
  const candidate = requireString(value || fallback, 'Relative path');
  assertSafePath(candidate, 'Relative path');
  return candidate.replace(/^\/+|\/+$/g, '').replace(/\/{2,}/g, '/').replace(/([^/])$/, '$1/');
}

export function joinUrlPath(baseUrl: string, relativePath: string): string {
  const base = normalizeBaseUrl(baseUrl);
  const relative = normalizeRelativePath(relativePath, '');
  const resolved = new URL(relative, new URL(base, PATH_VALIDATION_ORIGIN));
  if (resolved.origin !== PATH_VALIDATION_ORIGIN || !isContainedPath(resolved.pathname, base)) {
    throw new Error('Relative path must remain within the configured base URL');
  }
  return directoryPath(resolved.pathname);
}

function normalizeApiBaseUrl(value: string | undefined, baseUrl: string): string {
  const candidate = value === undefined ? '' : requireString(value, 'UI API base URL');
  if (!candidate) return joinUrlPath(baseUrl, 'api/ui/v1');
  assertSafePath(candidate, 'UI API base URL');

  const deploymentBase = new URL(normalizeBaseUrl(baseUrl), PATH_VALIDATION_ORIGIN);
  const apiUrl = candidate.startsWith('/')
    ? new URL(directoryPath(candidate), PATH_VALIDATION_ORIGIN)
    : new URL(directoryPath(candidate), deploymentBase);
  if (apiUrl.origin !== PATH_VALIDATION_ORIGIN) {
    throw new Error('UI API base URL must remain same-origin');
  }
  if (!isContainedPath(apiUrl.pathname, deploymentBase.pathname)) {
    throw new Error('UI API base URL must remain within the configured base URL');
  }
  return directoryPath(apiUrl.pathname);
}

function normalizeLocale(value: unknown): string {
  const candidate = value === undefined ? 'en' : requireString(value, 'Locale');
  let canonical: string[];
  try {
    canonical = Intl.getCanonicalLocales(candidate);
  } catch {
    throw new Error('Locale must be a valid BCP 47 language tag');
  }
  const locale = canonical[0];
  if (!locale || !SUPPORTED_LOCALES.has(locale)) {
    throw new Error(`Locale must be one of: ${[...SUPPORTED_LOCALES].join(', ')}`);
  }
  return locale;
}

function normalizeTimezone(value: unknown): string {
  const candidate = value === undefined ? 'UTC' : requireString(value, 'Timezone');
  try {
    return new Intl.DateTimeFormat('en', { timeZone: candidate }).resolvedOptions().timeZone;
  } catch {
    throw new Error('Timezone must be a valid IANA timezone');
  }
}

function normalizeColorMode(value: unknown): ColorModePreference {
  const candidate = value ?? 'light';
  if (typeof candidate !== 'string' || !COLOR_MODES.has(candidate as ColorModePreference)) {
    throw new Error('Color mode must be one of: light, dark, system');
  }
  return candidate as ColorModePreference;
}

export function loadRuntimeConfig(input: RuntimeConfigInput = {}): RuntimeConfig {
  const baseUrl = normalizeBaseUrl(input.baseUrl ?? '/');
  const appPath = normalizeRelativePath(input.appPath ?? 'ui/', 'ui/');
  const routerPath = joinUrlPath(baseUrl, appPath);
  const routerBasePath = routerPath === '/' ? '/' : routerPath.replace(/\/$/, '');

  return {
    baseUrl,
    appPath,
    routerBasePath,
    apiBaseUrl: normalizeApiBaseUrl(input.apiBaseUrl, baseUrl),
    locale: normalizeLocale(input.locale),
    timezone: normalizeTimezone(input.timezone),
    colorMode: normalizeColorMode(input.colorMode)
  };
}
