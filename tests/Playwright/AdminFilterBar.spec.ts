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
    await expect(stats).toHaveCount(2);
    const firstStat = stats.first();
    await expect(firstStat).toBeVisible();
    await expect(firstStat).toContainText("In sospeso");

    const filtersPanel = filterBox.locator(".filters-panel");
    await expect(filtersPanel).toBeVisible();
    await expect(filtersPanel.locator('#filterStatus')).toBeVisible();
    await expect(filtersPanel.locator('#filterTipo')).toBeVisible();
  });

  test("legend and info box are on same row", async ({ page }) => {
    const filterBox = page.locator('.filter-box').first();
    const row = filterBox.locator('.row').nth(1);
    await expect(row).toBeVisible();

    const legendCol = row.locator('.col-md-8');
    const infoCol = row.locator('.col-md-4');

    await expect(legendCol.locator('.legend')).toBeVisible();
    await expect(infoCol.locator('.info-card')).toBeVisible();
  });
});

test.describe("Admin filter bar", () => {
  test.beforeEach(async ({ page }) => {
    await seedTestDataAndLogin();
    // Admin page redirects to login if not authenticated, so we need to handle this
    await page.goto("/admin/segnalazioni", { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);
  });

  test("admin stats bar and cards are present", async ({ page }) => {
    // Admin should redirect to login if not authenticated
    const title = await page.title();
    if (title.includes('Login') || page.url().includes('login')) {
      await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
      await page.fill('input[name="password"]', 'admin');
      await page.getByRole('button', { name: /accedi/i }).click();
      await page.waitForURL(/admin\/segnalazioni/);
    }
    
    const statsBar = page.locator('.stats-bar');
    await expect(statsBar).toBeVisible();
  });
});
