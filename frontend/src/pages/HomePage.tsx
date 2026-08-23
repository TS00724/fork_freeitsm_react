import { EuiCallOut, EuiSpacer, EuiText, EuiTitle } from '@elastic/eui';
import { useAuthBoundary } from '../auth/AuthBoundary';
import { usePermissionBoundary } from '../permissions/PermissionBoundary';
import { useTenantBoundary } from '../tenants/TenantBoundary';

export function HomePage() {
  const auth = useAuthBoundary();
  const tenant = useTenantBoundary();
  const permissions = usePermissionBoundary();
  const boundaryState = JSON.stringify(
    { auth: auth.status, tenant: tenant.status, permissions: permissions.status },
    null,
    2
  );

  return (
    <section>
      <EuiSpacer size="l" />
      <EuiTitle><h2>WP-02 architecture review shell</h2></EuiTitle>
      <EuiSpacer size="m" />
      <EuiCallOut title="Stopped before BFF and business migration" color="primary" iconType="info">
        <p>This shell proves the frontend boundaries only. It performs no business request.</p>
      </EuiCallOut>
      <EuiSpacer size="l" />
      <EuiText>
        <p>Auth, tenant, and permission data remain unresolved until contracts are reviewed after G1.</p>
      </EuiText>
      <pre className="foundationJson" aria-label="Foundation boundary state">
        <code>{boundaryState}</code>
      </pre>
    </section>
  );
}
