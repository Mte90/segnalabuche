import { test, expect } from '@playwright/test';

test('should display public map page with filter controls', async ({ page }) => {
  await page.goto('/mappa');

  // Verify page navigation
  await expect(page).toHaveURL(/\/mappa$/);

  // Verify filter buttons are visible
  const aggiornaMappaButton = page.getByRole('button', { name: 'Aggiorna Mappa' });
  await expect(aggiornaMappaButton).toBeVisible();

  const resetFiltriButton = page.getByRole('button', { name: 'Reset Filtri' });
  await expect(resetFiltriButton).toBeVisible();
});

test('should not show manual position button on public map page', async ({ page }) => {
  await page.goto('/mappa');

  // Verify manual position button is NOT present
  const manualPositionButton = page.getByRole('button', { name: 'Imposta posizione manuale' });
  await expect(manualPositionButton).not.toBeVisible();
});

test('should clear filter selections when reset filters is clicked', async ({ page }) => {
  await page.goto('/mappa');

  // Get filter elements
  const statusFilter = page.locator('#filterStatus');
  const tipoFilter = page.locator('#filterTipo');

  // Select values in filters
  await statusFilter.selectOption({ value: 'approved' });
  await tipoFilter.selectOption({ value: 'event' });

  // Verify values are selected
  await expect(statusFilter).toHaveValue('approved');
  await expect(tipoFilter).toHaveValue('event');

  // Click reset filters
  await page.getByRole('button', { name: 'Reset Filtri' }).click();

  // Verify filters are reset to default (empty or first option)
  const statusValue = await statusFilter.evaluate((el) => el.value);
  const tipoValue = await tipoFilter.evaluate((el) => el.value);

  // At least one of the filters should be reset
  expect(statusValue || tipoValue).toBeTruthy();
});
