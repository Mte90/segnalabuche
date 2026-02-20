import { test, expect } from "@playwright/test";
import { execSync } from 'child_process';

async function seedTestDataAndLogin() {
  execSync('php artisan db:seed --class=TestSegnalazioneSeeder', { stdio: 'ignore' });
  return 'admin@comune.bugliano.it';
}

test.describe("Admin list filter interaction & status-text update", () => {
  test.beforeEach(async ({ page }) => {
    await seedTestDataAndLogin();
  });

  test("admin logs in and navigates to admin segnalazioni page", async ({ page }) => {
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await expect(page).toHaveURL(/admin\/segnalazioni/);
  });

  test("filter select dropdown and stat items are present", async ({ page }) => {
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await page.waitForTimeout(500);

    const statItems = page.locator('.stat-item');
    await expect(statItems).toHaveCount(4);
    
    await expect(statItems.nth(0)).toContainText('Totali');
    await expect(statItems.nth(1)).toContainText('In Sospeso');
    await expect(statItems.nth(2)).toContainText('Approvate');
    await expect(statItems.nth(3)).toContainText('Rifiutate');
  });

  test("select 'Approvato' filter via stat item updates select value", async ({ page }) => {
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await page.waitForTimeout(500);

    const select = page.locator('#adminFilterStatus');
    await expect(select).toHaveValue('all');
    
    const statItems = page.locator('.stat-item');
    const approvatoItem = statItems.nth(2);
    await approvatoItem.click();
    
    await page.waitForFunction(() => {
      const select = document.getElementById('adminFilterStatus');
      return select && select.value === 'approved';
    }, { timeout: 5000 });
    
    await expect(select).toHaveValue('approved');
  });

  test("select 'Approvato' filter and verify badge text changes to 'Segnalazioni approvate'", async ({ page }) => {
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await page.waitForTimeout(500);

    const statItems = page.locator('.stat-item');
    const approvatoItem = statItems.nth(2);
    await approvatoItem.click();
    
    await page.waitForFunction(() => {
      const select = document.getElementById('adminFilterStatus');
      return select && select.value === 'approved';
    }, { timeout: 5000 });
    
    await page.waitForTimeout(1000);
    
    const cardHeader = page.locator('.card-header span:first-child');
    await expect(cardHeader).toContainText('Segnalazioni approvate');
    
    const statsBar = page.locator('.stats-bar');
    await expect(statsBar).toBeVisible();
  });

  test("stats bar remains visible after filtering", async ({ page }) => {
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await page.waitForTimeout(500);

    const statsBar = page.locator('.stats-bar');
    await expect(statsBar).toBeVisible();

    const statItems = page.locator('.stat-item');
    await statItems.nth(2).click();
    await page.waitForTimeout(500);

    await expect(statsBar).toBeVisible();
  });
});
