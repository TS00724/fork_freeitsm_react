import { createContext, useContext, type ReactNode } from 'react';
import type { RuntimeConfig } from './runtimeConfig';

const RuntimeConfigContext = createContext<RuntimeConfig | null>(null);

export function RuntimeConfigProvider({
  config,
  children
}: {
  config: RuntimeConfig;
  children: ReactNode;
}) {
  return (
    <RuntimeConfigContext.Provider value={config}>
      {children}
    </RuntimeConfigContext.Provider>
  );
}

export function useRuntimeConfig(): RuntimeConfig {
  const value = useContext(RuntimeConfigContext);
  if (!value) throw new Error('RuntimeConfigProvider is missing');
  return value;
}
