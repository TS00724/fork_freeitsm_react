import { EuiButton, EuiEmptyPrompt, EuiSpacer } from '@elastic/eui';

export function ErrorStatePage() {
  return (
    <>
      <EuiSpacer size="xl" />
      <EuiEmptyPrompt
        iconType="error"
        color="danger"
        title={<h2>Something went wrong</h2>}
        body={<p>This generic error skeleton does not expose server or stack details.</p>}
        actions={<EuiButton onClick={() => window.location.reload()}>Reload</EuiButton>}
      />
    </>
  );
}
