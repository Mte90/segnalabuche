import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/Playwright',
  fullyParallel: true,
  timeout: 30000,
  use: {
    baseURL: 'http://localhost:8000',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
