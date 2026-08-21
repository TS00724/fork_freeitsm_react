import { expect, test } from '@playwright/test';

test.describe('G1 React shell', () => {
  test('loads the /ui/ shell with Light as the approved default', async ({ page }) => {
    await page.goto('./');
    await expect(page.getByRole('banner')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Switch to dark mode' })).toBeVisible();
    await expect(page.getByText(/Runtime basename:/)).toContainText('/ui');
  });

  test('supports direct 403 navigation under /ui/', async ({ page }) => {
    await page.goto('forbidden');
    await expect(page.getByRole('heading', { name: /forbidden|permission/i })).toBeVisible();
  });

  test('supports direct unknown-route navigation under /ui/', async ({ page }) => {
    await page.goto('this-route-does-not-exist');
    await expect(page.getByRole('heading', { name: /not found|404/i })).toBeVisible();
  });
});
