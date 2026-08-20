import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './app/App';
import { loadRuntimeConfig } from './config/runtimeConfig';
import './styles/global.css';

const rootElement = document.getElementById('root');
if (!rootElement) throw new Error('Missing #root mount element');

const config = loadRuntimeConfig(window.__FREEITSM_RUNTIME_CONFIG__);
document.documentElement.lang = config.locale;
document.documentElement.dataset.timezone = config.timezone;

createRoot(rootElement).render(
  <StrictMode>
    <App config={config} />
  </StrictMode>
);
