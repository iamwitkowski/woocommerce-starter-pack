import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { wpCli } from './wp-cli.mjs';

const OPTION = 'starter_pack_quick_checkout';

async function storeCart( page ) {
	const response = await page.request.get( '/wp-json/wc/store/v1/cart' );
	return response.json();
}

function drawerOf( page ) {
	return page.getByRole( 'dialog', { name: 'Shopping cart' } );
}

async function addFirstListingProduct( page ) {
	await page.goto( '/shop/' );
	const button = page
		.locator( '.product-grid .product-card__button .wp-block-button__link' )
		.first();
	await button.click();
	return button;
}

test.describe( 'mini-cart drawer', () => {
	test( 'opens after add to cart as a named dialog; Escape returns focus', async ( {
		page,
	} ) => {
		const button = await addFirstListingProduct( page );
		const drawer = drawerOf( page );
		await expect( drawer ).toBeVisible();
		await expect
			.poll( async () => ( await storeCart( page ) ).items_count )
			.toBe( 1 );
		await expect( drawer ).toContainText( 'Album' );

		const axe = await new AxeBuilder( { page } )
			.include( '.wc-block-mini-cart__drawer' )
			.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa' ] )
			.analyze();
		expect(
			axe.violations.map(
				( { id, nodes } ) =>
					`${ id }: ${ nodes
						.map( ( node ) => node.target.join( ' ' ) )
						.join( ', ' ) }`
			)
		).toEqual( [] );

		await expect(
			page.locator( '.wc-block-components-drawer__close' )
		).toBeFocused();
		await page.keyboard.press( 'Escape' );
		await expect( drawer ).toBeHidden();
		await expect( button ).toBeFocused();
	} );

	test( 'quantity and removal stay in sync with the store', async ( {
		page,
	} ) => {
		await addFirstListingProduct( page );
		const drawer = drawerOf( page );
		await expect( drawer ).toBeVisible();

		await drawer
			.getByRole( 'button', { name: /increase quantity of album/i } )
			.click();
		await expect
			.poll(
				async () => ( await storeCart( page ) ).items[ 0 ]?.quantity
			)
			.toBe( 2 );
		await expect(
			drawer.locator( '.wc-block-mini-cart__footer' )
		).toContainText( '30,00' );

		await drawer
			.getByRole( 'button', { name: /remove album from cart/i } )
			.click();
		await expect
			.poll( async () => ( await storeCart( page ) ).items_count )
			.toBe( 0 );
		await expect(
			drawer.locator( '.wc-block-mini-cart__empty-cart-wrapper' )
		).toBeVisible();
	} );
} );

test.describe( 'quick path to checkout option', () => {
	test.describe.configure( { mode: 'serial' } );
	test.afterAll( () => wpCli( `wp option update ${ OPTION } no` ) );

	test( 'off (default): drawer offers both cart and checkout', async ( {
		page,
	} ) => {
		wpCli( `wp option update ${ OPTION } no` );
		await addFirstListingProduct( page );
		const drawer = drawerOf( page );
		await expect(
			drawer.getByRole( 'link', { name: 'View cart' } )
		).toBeVisible();
		await expect(
			drawer.getByRole( 'link', { name: 'Go to checkout' } )
		).toBeVisible();
	} );

	test( 'on: drawer goes straight to checkout, cart page still works', async ( {
		page,
	} ) => {
		wpCli( `wp option update ${ OPTION } yes` );
		await addFirstListingProduct( page );
		const drawer = drawerOf( page );
		await expect(
			drawer.getByRole( 'link', { name: 'Go to checkout' } )
		).toBeVisible();
		await expect(
			drawer.getByRole( 'link', { name: 'View cart' } )
		).toHaveCount( 0 );

		const response = await page.goto( '/cart/' );
		expect( response?.status() ).toBe( 200 );
		await expect(
			page.locator( '.wc-block-cart-items' ).getByText( 'Album' )
		).toBeVisible();
	} );

	test( 'setting is available in WooCommerce → Settings → Advanced → Theme', async ( {
		page,
		isMobile,
	} ) => {
		test.skip( isMobile, 'Admin settings are checked on desktop.' );
		wpCli( `wp option update ${ OPTION } no` );
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', 'admin' );
		await page.fill( '#user_pass', 'password' );
		await page.click( '#wp-submit' );
		await page.waitForURL( /wp-admin/ );

		await page.goto(
			'/wp-admin/admin.php?page=wc-settings&tab=advanced&section=starter_pack'
		);
		const checkbox = page.getByLabel( /skip the cart page/i );
		await expect( checkbox ).not.toBeChecked();
		await checkbox.check();
		await page.getByRole( 'button', { name: /save changes/i } ).click();
		await expect( page.getByLabel( /skip the cart page/i ) ).toBeChecked();
		expect( wpCli( `wp option get ${ OPTION }` ) ).toBe( 'yes' );
	} );
} );
