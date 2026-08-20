import { Component, type ErrorInfo, type ReactNode } from 'react';
import { EuiButton, EuiEmptyPrompt, EuiPageTemplate } from '@elastic/eui';

interface State {
  hasError: boolean;
}

export class ErrorBoundary extends Component<{ children: ReactNode }, State> {
  state: State = { hasError: false };

  static getDerivedStateFromError(): State {
    return { hasError: true };
  }

  override componentDidCatch(error: Error, info: ErrorInfo): void {
    // A reviewed telemetry adapter may replace this after G1. WP-02 sends nothing.
    console.error('Unhandled React render error', error, info.componentStack);
  }

  override render(): ReactNode {
    if (!this.state.hasError) return this.props.children;

    return (
      <EuiPageTemplate restrictWidth={800} paddingSize="xl">
        <EuiPageTemplate.Section>
          <EuiEmptyPrompt
            iconType="error"
            color="danger"
            title={<h1>React application error</h1>}
            body={<p>The error boundary caught a rendering failure.</p>}
            actions={<EuiButton onClick={() => window.location.reload()}>Reload</EuiButton>}
          />
        </EuiPageTemplate.Section>
      </EuiPageTemplate>
    );
  }
}
