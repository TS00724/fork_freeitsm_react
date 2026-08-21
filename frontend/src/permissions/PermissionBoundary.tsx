import { createContext, useContext, type ReactNode } from 'react';

export interface PermissionBoundaryState {
  status: 'unresolved';
  hasCapability: (capability: string) => boolean;
}

const unresolvedPermissions: PermissionBoundaryState = {
  status: 'unresolved',
  hasCapability: () => false
};
const PermissionContext = createContext<PermissionBoundaryState | null>(null);

export function PermissionBoundary({ children }: { children: ReactNode }) {
  return (
    <PermissionContext.Provider value={unresolvedPermissions}>
      {children}
    </PermissionContext.Provider>
  );
}

export function usePermissionBoundary(): PermissionBoundaryState {
  const value = useContext(PermissionContext);
  if (!value) throw new Error('PermissionBoundary is missing');
  return value;
}
