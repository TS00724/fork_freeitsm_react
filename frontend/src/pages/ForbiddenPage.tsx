import { EuiEmptyPrompt, EuiSpacer } from '@elastic/eui';

export function ForbiddenPage() {
  return (
    <>
      <EuiSpacer size="xl" />
      <EuiEmptyPrompt
        iconType="lock"
        color="danger"
        title={<h2>403 — Permission denied</h2>}
        body={<p>This is a UI state only. Future authorization must be enforced by PHP on every request.</p>}
      />
    </>
  );
}
