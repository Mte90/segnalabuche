import { test, expect } from '@playwright/test';

test.describe('Admin list filter interaction & status-text update', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to admin page - it will redirect to login
    await page.goto('/admin/segnalazioni', { waitUntil: 'networkidle' });
    
    // Fill login form
    await page.fill('input[name="email"]', 'admin@comune.bugliano.it');
    await page.fill('input[name="password"]', 'admin');
    await page.getByRole('button', { name: /accedi/i }).click();
    
    // Wait for redirect to admin page
    await page.waitForURL('/admin/segnalazioni');
  });

  test('admin logs in and navigates to admin segnalazioni page', async ({ page }) => {
    // Verify we're on the admin page
    await expect(page).toHaveURL(/admin\/segnalazioni/);
    
    // Verify stats bar is visible
    await expect(page.locator('.stats-bar')).toBeVisible();
  });

  test('filter buttons are present (Filtra, Reset, Esporta CSV)', async ({ page }) => {
    // Verify filter button
    await expect(page.getByRole('button', { name: /Filtra/i })).toBeVisible();
    
    // Verify reset button
    await expect(page.getByRole('button', { name: /Reset/i })).toBeVisible();
    
    // Verify export CSV button
    await expect(page.getByRole('button', { name: /Esporta CSV/i })).toBeVisible();
  });

  test('select "Approvato" via stat item and verify status-text update', async ({ page }) => {
    // Get initial header title
    const initialTitle = page.locator('.card-header span:first-child');
    const initialText = await initialTitle.textContent();
    
    // Click the "Approvate" stat item to filter
    await page.locator('.stat-item', { hasText: 'Approvate' }).click();
    
    // Wait for the AJAX request to complete
    await page.waitForTimeout(1000);
    
    // Verify status text has changed
    const newTitle = page.locator('.card-header span:first-child');
    await expect(newTitle).not.toHaveText(initialText);
    await expect(newTitle).toContainText('Approvate');
  });

  test('select "Approvato" via dropdown, click Filter and verify badge updates', async ({ page }) => {
    // Locate the select dropdown and change selection to "Approvato"
    const statusSelect = page.locator('#adminFilterStatus');
    await statusSelect.selectOption({ value: 'approved' });
    
    // Click the Filter button
    await page.getByRole('button', { name: /Filtra/i }).click();
    
    // Wait for the AJAX request to complete
    await page.waitForTimeout(500);
    
    // Verify status text in header changes to 'Approvate'
    await expect(page.locator('.card-header span:first-child')).toContainText('Approvate');
    
    // Verify badge shows count
    const badge = page.locator('.card-header span.badge');
    await expect(badge).toBeVisible();
    await expect(badge).not.toHaveText('0');
  });

  test('reset filters clears all selections', async ({ page }) => {
    // First, apply a filter
    await page.locator('#adminFilterStatus').selectOption({ value: 'approved' });
    await page.getByRole('button', { name: /Filtra/i }).click();
    await page.waitForTimeout(500);
    
    // Verify filter is applied
    await expect(page.locator('.card-header span:first-child')).toContainText('Approvate');
    
    // Click Reset button
    await page.getByRole('button', { name: /Reset/i }).click();
    
    // Wait for the reset to complete
    await page.waitForTimeout(500);
    
    // Verify all filters are cleared (dropdown should show "all")
    await expect(page.locator('#adminFilterStatus')).toHaveValue('all');
  });
});
