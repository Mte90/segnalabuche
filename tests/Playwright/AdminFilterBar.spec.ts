
import { test, expect } from "@playwright/test";

import { execSync } from 'child_process';

function seedTestData() {
  execSync('php artisan db:seed --class=TestSegnalazioneSeeder', { stdio: 'ignore' });
}

test.describe("Map page filter box", () => {
  test.beforeEach(async ({ page }) => {
    seedTestData();
    await page.goto("/mappa");
  });

  test("filter box structure and classes are present", async ({ page }) => {
    const filterBox = page.locator(".filter-box");
    await expect(filterBox).toBeVisible();

    const stats = filterBox.locator(".stats-container .stat-item");
    await expect(stats).toHaveCount(3);
    await expect(stats).toContainText("In sospeso");
    await expect(stats).toContainText("Approvato");
    await expect(stats).toContainText("Rifiutato");

    const filtersPanel = filterBox.locator(".filters-panel");
    await expect(filtersPanel).toBeVisible();
    await expect(filtersPanel.locator('#filterStatus')).toBeVisible();
    await expect(filtersPanel.locator('#filterTipo')).toBeVisible();
  });
});

test.describe("Admin filter bar", () => {
  test.beforeEach(async ({ page }) => {
    seedTestData();
    await page.goto("/admin/segnalazioni");
  });

  test("clicking status cards updates list via AJAX", async ({ page }) => {
    const cardBody = page.locator('.card-body:has-text("Segnalazioni in sospeso")');
    const statsBar = page.locator(".stats-bar");
    await expect(statsBar).toBeVisible();

    const clickCardByLabel = async (label: string, expectedCount: number) => {
      const card = page.locator(".stat-item", { hasText: label });
      await expect(card).toBeVisible();
      await card.click();
      await expect(cardBody.locator('.segnalazione-item')).toHaveCount(expectedCount);
    };

    await clickCardByLabel("Totali", 5);
    await clickCardByLabel("In Sospeso", 2);
    await clickCardByLabel("Approvate", 2);
    await clickCardByLabel("Rifiutate", 1);
  });
});
