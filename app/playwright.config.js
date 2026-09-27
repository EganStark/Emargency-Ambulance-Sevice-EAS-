const { defineConfig, devices } = require('@playwright/test');
const path = require('node:path');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 30000,
  fullyParallel: false,
  reporter: [['list'], ['html', { outputFolder: 'playwright-report', open: 'never' }]],
  webServer: process.env.TEST_URL ? undefined : {
    command: 'node test-server.js',
    url: 'http://127.0.0.1:3100',
    reuseExistingServer: false,
    timeout: 15000
  },
  use: {
    baseURL: process.env.TEST_URL || 'http://127.0.0.1:3100',
    launchOptions: { executablePath: process.env.BROWSER_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe' },
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure'
  },
  projects: [
    { name: 'desktop', use: { viewport: { width: 1440, height: 900 } } },
    { name: 'mobile', use: { ...devices['Pixel 7'] } }
  ]
});
