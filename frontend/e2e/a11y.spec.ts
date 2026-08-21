import AxeBuilder from '@axe-core/playwright';
import { expect, test } from '@playwright/test';

const routes = ['./', 'forbidden', 'this-route-does-not-exist'];

for (const route of routes) {
  test(`has no serious or critical axe violations: ${route}`, async ({ page }) => {
    await page.goto(route);
    const results = await new AxeBuilder({ page }).analyze();
    const blocking = results.violations.filter((violation) =>
      violation.impact === 'critical' || violation.impact === 'serious'
    );
    expect(blocking).toEqual([]);
  });
}
