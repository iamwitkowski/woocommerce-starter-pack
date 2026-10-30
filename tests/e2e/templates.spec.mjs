import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

// [name, path, expected status, expected h1 (string or RegExp)]. Content comes from bin/seed.php.
const TEMPLATES = [
	[ 'index (blog)', '/', 200, 'Blog' ],
	[ 'single', '/how-to-choose-a-hoodie/', 200, 'How to choose a hoodie' ],
	[ 'page', '/sample-page/', 200, 'Sample Page' ],
	[ 'archive', '/category/guides/', 200, 'Guides' ],
	[ 'search with results', '/?s=cotton', 200, /cotton/i ],
	[ 'search without results', '/?s=zzzqqq', 200, /zzzqqq/i ],
	[ '404', '/this-page-does-not-exist/', 404, 'Page not found' ],
];

test.describe( 'WordPress templates', () => {
	for ( const [ name, path, status, heading ] of TEMPLATES ) {
		test( `${ name }: landmarks, single h1, a11y, no overflow`, async ( {
			page,
		} ) => {
			const errors = [];
			page.on( 'pageerror', ( error ) => errors.push( String( error ) ) );

			const response = await page.goto( path );
			expect( response?.status() ).toBe( status );

			await expect( page.getByRole( 'banner' ) ).toHaveCount( 1 );
			await expect( page.getByRole( 'main' ) ).toHaveCount( 1 );
			await expect( page.getByRole( 'contentinfo' ) ).toHaveCount( 1 );

			const h1 = page.locator( 'h1' );
			await expect( h1 ).toHaveCount( 1 );
			await expect( h1 ).toHaveText( heading );

			// Skip link targets <main>.
			const skip = page.locator( '#wp-skip-link' );
			await expect( skip ).toHaveAttribute( 'href', /^#/ );
			const target = ( await skip.getAttribute( 'href' ) ).slice( 1 );
			await expect( page.locator( `main#${ target }` ) ).toHaveCount( 1 );

			const axe = await new AxeBuilder( { page } )
				.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa' ] )
				.analyze();
			expect( axe.violations ).toEqual( [] );

			const overflow = await page.evaluate(
				() =>
					document.documentElement.scrollWidth >
					document.documentElement.clientWidth
			);
			expect( overflow ).toBe( false );
			expect( errors ).toEqual( [] );
		} );
	}

	test( 'archive lists posts as linked cards', async ( { page } ) => {
		await page.goto( '/category/guides/' );
		const cards = page.locator( 'main article' );
		await expect( cards ).toHaveCount( 3 );
		await expect(
			cards.first().getByRole( 'link', { name: /.+/ } ).first()
		).toBeVisible();
	} );

	test( 'search without results offers a new search', async ( { page } ) => {
		await page.goto( '/?s=zzzqqq' );
		await expect( page.locator( 'main' ) ).toContainText( 'Nothing found' );
		await expect(
			page.locator( 'main' ).getByRole( 'searchbox' )
		).toBeVisible();
	} );

	test( 'single post has post navigation and comment form', async ( {
		page,
	} ) => {
		await page.goto( '/how-to-choose-a-hoodie/' );
		await expect(
			page.locator( '.wp-block-post-navigation-link a' )
		).not.toHaveCount( 0 );
		await expect( page.locator( '#commentform' ) ).toBeVisible();
	} );
} );

test.describe( 'header and footer', () => {
	test( 'header has one account link and one mini-cart (no auto-inserted duplicates)', async ( {
		page,
	} ) => {
		await page.goto( '/' );
		const header = page.getByRole( 'banner' );
		await expect(
			header.locator( '.wp-block-woocommerce-customer-account' )
		).toHaveCount( 1 );
		await expect(
			header.locator( '.wp-block-woocommerce-mini-cart' )
		).toHaveCount( 1 );
	} );

	test( 'closed mini-cart drawer is out of the tab order; opening it still works', async ( {
		page,
	} ) => {
		await page.goto( '/' );
		const drawer = page.locator( '.wc-block-mini-cart__drawer' );

		for ( let i = 0; i < 40; i++ ) {
			await page.keyboard.press( 'Tab' );
			await expect( drawer.locator( ':focus' ) ).toHaveCount( 0 );
		}

		await page.locator( '.wc-block-mini-cart__button' ).click();
		await expect( drawer ).toBeVisible();
		await expect( drawer ).toHaveAttribute( 'aria-hidden', 'false' );
		// WooCommerce moves focus into the drawer once it has opened; Escape is handled from there.
		await expect(
			drawer.locator( '.wc-block-components-drawer__close' )
		).toBeFocused();
		await page.keyboard.press( 'Escape' );
		await expect( drawer ).toBeHidden();
	} );

	test( 'mini-cart is left out of the cart and checkout headers', async ( {
		page,
	} ) => {
		for ( const path of [ '/cart/', '/checkout/' ] ) {
			await page.goto( path );
			await expect( page.getByRole( 'banner' ) ).toHaveCount( 1 );
			await expect(
				page.locator( '.wp-block-woocommerce-mini-cart' )
			).toHaveCount( 0 );
		}
	} );

	test( 'announcement bar links to the shop', async ( { page } ) => {
		await page.goto( '/' );
		await expect(
			page
				.getByRole( 'banner' )
				.getByRole( 'link', { name: 'Shop the collection' } )
		).toHaveAttribute( 'href', /\/shop\/$/ );
	} );

	test( 'footer links to store pages', async ( { page } ) => {
		await page.goto( '/' );
		const footer = page.getByRole( 'contentinfo' );
		for ( const name of [ 'Shop', 'My account', 'Cart' ] ) {
			await expect(
				footer.getByRole( 'link', { name, exact: true } )
			).toBeVisible();
		}
	} );

	test( 'header search finds products', async ( { page, isMobile } ) => {
		await page.goto( '/' );
		if ( isMobile ) {
			await page.getByRole( 'button', { name: 'Open menu' } ).click();
		}
		const search = page
			.getByRole( 'banner' )
			.getByRole( 'searchbox', { name: 'Search products' } );
		await search.fill( 'hoodie' );
		await search.press( 'Enter' );
		await expect( page ).toHaveURL( /post_type=product/ );
		await expect( page.locator( 'main' ) ).toContainText(
			'Hoodie with Logo'
		);
	} );

	test( 'header, content and footer share the wide grid edges', async ( {
		page,
		isMobile,
	} ) => {
		test.skip( isMobile, 'Grid edges are checked on desktop.' );
		await page.goto( '/' );
		const edges = await page.evaluate( () =>
			[
				'.site-header > .alignwide',
				'main h1.alignwide',
				'main .wp-block-query.alignwide',
				'.site-footer > .alignwide',
			].map( ( selector ) => {
				const box = document
					.querySelector( selector )
					.getBoundingClientRect();
				return [ Math.round( box.left ), Math.round( box.right ) ];
			} )
		);
		for ( const edge of edges ) {
			expect( edge ).toEqual( edges[ 0 ] );
		}
	} );

	test( 'mobile menu opens as a dialog and closes with Escape', async ( {
		page,
		isMobile,
	} ) => {
		test.skip( ! isMobile, 'Overlay menu is used on mobile only.' );
		await page.goto( '/' );
		const open = page.getByRole( 'button', { name: 'Open menu' } );
		await open.click();
		const dialog = page.getByRole( 'dialog' );
		await expect( dialog ).toBeVisible();
		await expect(
			dialog.getByRole( 'link', { name: 'Shop', exact: true } )
		).toBeVisible();
		await page.keyboard.press( 'Escape' );
		await expect( dialog ).toBeHidden();
		await expect( open ).toBeFocused();
	} );
} );
