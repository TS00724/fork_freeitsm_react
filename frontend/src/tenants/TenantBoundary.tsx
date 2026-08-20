import { createContext, useContext, type ReactNode } from 'react';

export interface TenantBoundaryState {
  status: 'unresolved';
  tenant: null;
}

const unresolvedTenant: TenantBoundaryState = { status: 'unresolved', tenant: null };
const TenantContext = createContext<TenantBoundaryState | null>(null);

export function TenantBoundary({ children }: { children: ReactNode }) {
  return <TenantContext.Provider value={unresolvedTenant}>{children}</TenantContext.Provider>;
}

export function useTenantBoundary(): TenantBoundaryState {
  const value = useContext(TenantContext);
  if (!value) throw new Error('TenantBoundary is missing');
  return value;
}
