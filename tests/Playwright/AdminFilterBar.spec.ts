import { test, expect } from "@playwright/test";

import { execSync } from 'child_process';

async function seedTestDataAndLogin() {
  execSync('php artisan db:seed --class=TestSegnalazioneSeeder', { stdio: 'ignore' });
  return 'admin@comune.bugliano.it';
}

test.describe("Map page filter box", () => {
  test.beforeEach(async ({ page }) => {
    await seedTestDataAndLogin();
    await page.goto("/mappa");
  });

  test("filter box structure and classes are present", async ({ page }) => {
    const filterBox = page.locator('.filter-box').first();
    await expect(filterBox).toBeVisible();

    const stats = filterBox.locator(".stats-container .stat-item");
    await expect(stats).toHaveCount(3);
    const firstStat = stats.first();
    await expect(firstStat).toBeVisible();
    await expect(firstStat).toContainText("In sospeso");

    const filtersPanel = filterBox.locator(".filters-panel");
    await expect(filtersPanel).toBeVisible();
    await expect(filtersPanel.locator('#filterStatus')).toBeVisible();
    await expect(filtersPanel.locator('#filterTipo')).toBeVisible();
  });
});

test.describe("Admin login form", () => {
  test.beforeEach(async ({ page }) => {
    await seedTestDataAndLogin();
    // Navigate to login page
    await page.goto("/login");
  });

  test("admin login form fields are present", async ({ page }) => {
    // Check that the login form fields exist
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.getByRole('button', { name: /accedi/i })).toBeVisible();
  });

  test("admin can login with valid credentials", async ({ page }) => {
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    
    await page.click('button[type="submit"]');
    
    await page.waitForURL(/admin\/segnalazioni/);
    
    await expect(page).toHaveURL(/admin\/segnalazioni/);
    
    await expect(page.locator('.stats-bar')).toBeVisible();
    await expect(page.locator('.card:has-text("Segnalazioni in sospeso")')).toBeVisible();
  });
});
