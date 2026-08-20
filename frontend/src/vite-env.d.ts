/// <reference types="vite/client" />

interface FreeItsmRuntimeConfigInput {
  baseUrl?: string;
  appPath?: string;
  apiBaseUrl?: string;
  locale?: string;
  timezone?: string;
  colorMode?: 'light' | 'dark' | 'system';
}

interface Window {
  __FREEITSM_RUNTIME_CONFIG__?: FreeItsmRuntimeConfigInput;
}
