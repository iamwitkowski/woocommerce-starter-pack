import { test, expect } from '@playwright/test';
import { wpCli } from './wp-cli.mjs';

const PAGES = [
	[ 'home', '/' ],
	[ 'content page', '/sample-page/' ],
	[ 'shop', '/shop/' ],
	[ 'simple product', '/product/beanie/' ],
	[ 'variable product', '/product/hoodie/' ],
	[ 'cart', '/cart/' ],
	[ 'checkout', '/checkout/' ],
	[ 'my account', '/my-account/' ],
	[ 'product search', '/?s=hoodie&post_type=product' ],
];

test.describe( 'smoke', () => {
	test( 'Starter Pack is the active theme', () => {
		expect( wpCli( 'wp theme list --status=active --field=name' ) ).toBe(
			'starter-pack'
		);
	} );

	for ( const [ name, path ] of PAGES ) {
		test( `${ name } renders without JS errors`, async ( { page } ) => {
			const errors = [];
			page.on( 'pageerror', ( error ) => errors.push( String( error ) ) );
			page.on( 'console', ( message ) => {
				if ( message.type() === 'error' ) {
					errors.push( message.text() );
				}
			} );

			const response = await page.goto( path, {
				waitUntil: 'networkidle',
			} );

			expect( response?.status() ).toBe( 200 );
			await expect( page.locator( 'main' ).first() ).toBeVisible();
			expect( errors ).toEqual( [] );
		} );
	}

	test( 'unknown URL returns 404', async ( { page } ) => {
		const response = await page.goto( '/this-page-does-not-exist/' );
		expect( response?.status() ).toBe( 404 );
	} );
} );
