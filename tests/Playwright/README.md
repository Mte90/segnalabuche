# Playwright End‑to‑End Tests

This folder contains Playwright tests for the admin filter bar and map page.

## Running the tests

1. Ensure the Laravel app uses the in‑memory SQLite database for testing.
2. Start the Laravel development server (or use `php artisan serve`).
3. Install Playwright if not already present:
   ```bash
   npx playwright install
   ```
4. Run the tests with:
   ```bash
   npx playwright test
   ```

The `AdminFilterBar.spec.ts` file seeds the required reports using a Laravel artisan seeder (`TestSegnalazioneSeeder`). Ensure this seeder creates the appropriate pending, approved, and rejected records.

## Test overview

* **Map page filter box** – checks that the filter box, stats, and dropdowns are present.
* **Admin filter bar** – clicks each status card, verifies the list updates via AJAX, and ensures the stats bar stays visible.

Screenshots are automatically captured on failure by Playwright.
