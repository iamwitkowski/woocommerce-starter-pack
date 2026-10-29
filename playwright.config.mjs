import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.WP_BASE_URL || 'http://localhost:8890';

export default defineConfig( {
	testDir: './tests/e2e',
	globalSetup: './tests/e2e/global-setup.mjs',
	globalTeardown: './tests/e2e/global-teardown.mjs',
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	// Tests share one WooCommerce store and session-based carts.
	workers: 1,
	reporter: process.env.CI
		? [ [ 'github' ], [ 'html', { open: 'never' } ] ]
		: 'list',
	use: {
		baseURL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{ name: 'mobile', use: { ...devices[ 'Pixel 7' ] } },
		{ name: 'desktop', use: { ...devices[ 'Desktop Chrome' ] } },
	],
} );
