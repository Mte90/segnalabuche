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
    // Navigate to admin page - it will redirect to login
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    
    // Fill login form
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    
    // Wait for redirect to admin page
    await page.waitForURL(/admin\/segnalazioni/);
    
    // Verify we're on the admin page
    await expect(page).toHaveURL(/admin\/segnalazioni/);
  });

  test("filter select dropdown and stat items are present", async ({ page }) => {
    // Login
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await page.waitForTimeout(500);

    // Verify stat-items exist (4 items for Totali, In Sospeso, Approvate, Rifiutate)
    const statItems = page.locator('.stat-item');
    await expect(statItems).toHaveCount(4);
    
    // Verify stat labels
    await expect(statItems.nth(0)).toContainText('Totali');
    await expect(statItems.nth(1)).toContainText('In Sospeso');
    await expect(statItems.nth(2)).toContainText('Approvate');
    await expect(statItems.nth(3)).toContainText('Rifiutate');
  });

  test("select 'Approvato' filter via stat item updates select value", async ({ page }) => {
    // Login
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await page.waitForTimeout(500);

    // Verify initial select value
    const select = page.locator('#adminFilterStatus');
    await expect(select).toHaveValue('all');
    
    // Click the "Approvato" stat item (index 2) to filter
    const statItems = page.locator('.stat-item');
    const approvatoItem = statItems.nth(2);
    await approvatoItem.click();
    
    // Wait for the select value to update
    await page.waitForFunction(() => {
      const select = document.getElementById('adminFilterStatus');
      return select && select.value === 'approved';
    }, { timeout: 5000 });
    
    // Verify select value changed
    await expect(select).toHaveValue('approved');
  });

  test("select 'Approvato' filter and verify badge text changes to 'Segnalazioni approvate'", async ({ page }) => {
    // Login
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await page.waitForTimeout(500);

    // Click the "Approvato" stat item (index 2) to filter
    const statItems = page.locator('.stat-item');
    const approvatoItem = statItems.nth(2);
    await approvatoItem.click();
    
    // Wait for the select value to update
    await page.waitForFunction(() => {
      const select = document.getElementById('adminFilterStatus');
      return select && select.value === 'approved';
    }, { timeout: 5000 });
    
    // Wait for title to update via updateAdminMap
    await page.waitForTimeout(1000);
    
    // Verify status text in header changes to 'Segnalazioni approvate'
    const cardHeader = page.locator('.card-header span:first-child');
    await expect(cardHeader).toContainText('Segnalazioni approvate');
    
    // Verify stats bar is still visible
    const statsBar = page.locator('.stats-bar');
    await expect(statsBar).toBeVisible();
  });

  test("stats bar remains visible after filtering", async ({ page }) => {
    // Login
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    await page.waitForURL(/admin\/segnalazioni/);
    await page.waitForTimeout(500);

    // Verify initial stats bar visibility
    const statsBar = page.locator('.stats-bar');
    await expect(statsBar).toBeVisible();

    // Click Approvato filter
    const statItems = page.locator('.stat-item');
    await statItems.nth(2).click();
    await page.waitForTimeout(500);

    // Verify stats bar is still visible after filtering
    await expect(statsBar).toBeVisible();
  });
});
