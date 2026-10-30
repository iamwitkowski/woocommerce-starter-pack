import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { applyVariation } from './wp-cli.mjs';

// Fixtures come from bin/seed.php (WooCommerce sample data, PLN, VAT 23%, courier 15 zł, free shipping from 200 zł).

async function expectAccessible( page ) {
	const axe = await new AxeBuilder( { page } )
		.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa' ] )
		.analyze();
	// Compact report: rule id and offending selectors.
	expect(
		axe.violations.map(
			( { id, nodes } ) =>
				`${ id }: ${ nodes
					.map( ( node ) => node.target.join( ' ' ) )
					.join( ', ' ) }`
		)
	).toEqual( [] );
}

// Cart and Checkout render loading skeletons first; audit the hydrated UI.
async function waitForBlocksLoaded( page ) {
	await expect(
		page.locator( '.wc-block-components-skeleton__element' )
	).toHaveCount( 0, { timeout: 20_000 } );
}

async function expectNoOverflow( page ) {
	expect(
		await page.evaluate(
			() =>
				document.documentElement.scrollWidth >
				document.documentElement.clientWidth
		)
	).toBe( false );
}

async function storeCart( page ) {
	const response = await page.request.get( '/wp-json/wc/store/v1/cart' );
	return response.json();
}

async function addToCart( page, slug, quantity = 1 ) {
	await page.goto( `/product/${ slug }/` );
	if ( quantity > 1 ) {
		await page
			.getByRole( 'spinbutton', { name: 'Product quantity' } )
			.fill( String( quantity ) );
	}
	await page
		.locator( '.wp-block-woocommerce-add-to-cart-with-options' )
		.getByRole( 'button', { name: /^Add to cart/ } )
		.click();
	await expect
		.poll( async () => ( await storeCart( page ) ).items_count )
		.toBeGreaterThan( 0 );
}

test.describe( 'listing', () => {
	for ( const [ name, path, heading ] of [
		[ 'shop', '/shop/', 'Shop' ],
		[ 'category', '/product-category/hoodies/', 'Hoodies' ],
		[ 'product search', '/?s=hoodie&post_type=product', /hoodie/i ],
	] ) {
		test( `${ name }: single h1, product cards, a11y, no overflow`, async ( {
			page,
		} ) => {
			const response = await page.goto( path );
			expect( response?.status() ).toBe( 200 );
			await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
			await expect( page.locator( 'h1' ) ).toHaveText( heading );
			await expect(
				page.locator( '.product-grid .product-cards > li' ).first()
			).toBeVisible();
			await expectAccessible( page );
			await expectNoOverflow( page );
		} );
	}

	test( 'shop shows 12 products per page with pagination', async ( {
		page,
	} ) => {
		await page.goto( '/shop/' );
		await expect(
			page.locator( '.product-grid .product-cards > li' )
		).toHaveCount( 12 );
		await expect(
			page.locator( '.wp-block-query-pagination' )
		).toBeVisible();
	} );

	test( 'cards in a row align their buttons', async ( { page } ) => {
		await page.goto( '/shop/' );
		const tops = await page
			.locator( '.product-grid .product-card__button' )
			.evaluateAll( ( buttons ) =>
				buttons.map( ( button ) => [
					Math.round( button.getBoundingClientRect().top ),
					Math.round(
						button.closest( 'li' ).getBoundingClientRect().top
					),
				] )
			);
		const rows = new Map();
		for ( const [ buttonTop, rowTop ] of tops ) {
			rows.set( rowTop, [ ...( rows.get( rowTop ) ?? [] ), buttonTop ] );
		}
		for ( const buttons of rows.values() ) {
			expect( new Set( buttons ).size ).toBe( 1 );
		}
	} );

	test( 'sorting by price puts the cheapest product first', async ( {
		page,
	} ) => {
		await page.goto( '/shop/' );
		await page
			.getByRole( 'combobox', { name: /sort/i } )
			.selectOption( 'price' );
		await expect( page ).toHaveURL( /orderby=price/ );
		await expect(
			page.locator( '.product-card__title' ).first()
		).toHaveText( 'Single' );
	} );

	test( 'category filter narrows the results', async ( {
		page,
		isMobile,
	} ) => {
		await page.goto( '/shop/' );
		if ( isMobile ) {
			await page
				.getByRole( 'button', { name: /filter products/i } )
				.click();
		}
		await page.getByRole( 'checkbox', { name: /^Hoodies/ } ).check();
		if ( isMobile ) {
			await page
				.getByRole( 'button', { name: /apply|show/i } )
				.first()
				.click();
		}
		await expect( page ).toHaveURL( /hoodies/ );
		const titles = page.locator( '.product-grid .product-card__title' );
		await expect( titles.first() ).toBeVisible();
		for ( const title of await titles.allTextContents() ) {
			expect( title ).toMatch( /Hoodie/ );
		}
	} );

	test( 'product search without results offers a new search', async ( {
		page,
	} ) => {
		await page.goto( '/?s=zzzqqq&post_type=product' );
		await expect( page.locator( 'main' ) ).toContainText(
			'No products found'
		);
		await expect(
			page
				.locator( 'main' )
				.getByRole( 'searchbox', { name: 'Search products' } )
		).toBeVisible();
	} );
} );

test.describe( 'product page', () => {
	test( 'variable product: h1, structured data, gallery, a11y', async ( {
		page,
	} ) => {
		await page.goto( '/product/hoodie/' );
		await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
		await expect( page.locator( 'h1' ) ).toHaveText( 'Hoodie' );
		await expect(
			page.locator( '.wc-block-product-gallery-large-image' )
		).toBeVisible();

		const types = await page
			.locator( 'script[type="application/ld+json"]' )
			.evaluateAll( ( scripts ) =>
				scripts.flatMap( ( script ) => {
					const data = JSON.parse( script.textContent );
					const graph = data[ '@graph' ] ?? [ data ];
					return graph.map( ( node ) => node[ '@type' ] );
				} )
			);
		expect( types.filter( ( type ) => type === 'Product' ) ).toHaveLength(
			1
		);
		expect(
			types.filter( ( type ) => type === 'BreadcrumbList' )
		).toHaveLength( 1 );

		await expectAccessible( page );
		await expectNoOverflow( page );
	} );

	test( 'simple product adds to the cart', async ( { page } ) => {
		await addToCart( page, 'beanie', 2 );
		const cart = await storeCart( page );
		expect( cart.items ).toHaveLength( 1 );
		expect( cart.items[ 0 ].name ).toBe( 'Beanie' );
		expect( cart.items[ 0 ].quantity ).toBe( 2 );
	} );

	test( 'variable product requires a complete, in-stock variation', async ( {
		page,
	} ) => {
		await page.goto( '/product/hoodie/' );
		const addButton = page
			.locator( '.wp-block-woocommerce-add-to-cart-with-options' )
			.getByRole( 'button', { name: /^Add to cart/ } );

		// Incomplete selection: nothing is added.
		await page.getByRole( 'radio', { name: 'Blue' } ).click();
		await addButton.click();
		expect( ( await storeCart( page ) ).items_count ).toBe( 0 );

		// Blue + Logo "Yes" is the seeded out-of-stock variation.
		await page.getByRole( 'radio', { name: 'Yes' } ).click();
		await expect( page.locator( 'main' ) ).toContainText( /out of stock/i );

		// Blue + "No" is in stock and can be added.
		await page.getByRole( 'radio', { name: 'No' } ).click();
		await addButton.click();
		await expect
			.poll( async () => ( await storeCart( page ) ).items_count )
			.toBe( 1 );
		const [ item ] = ( await storeCart( page ) ).items;
		expect( item.name ).toBe( 'Hoodie' );
		expect( item.variation.map( ( { value } ) => value ).sort() ).toEqual( [
			'Blue',
			'No',
		] );
	} );

	test( 'details accordion shows reviews; related products are listed', async ( {
		page,
	} ) => {
		await page.goto( '/product/hoodie/' );
		await page.getByRole( 'button', { name: 'Reviews' } ).click();
		await expect( page.locator( '.product-details' ) ).toContainText(
			'Great fit and very warm.'
		);
		await expect(
			page.locator( '.related-products .product-cards > li' ).first()
		).toBeVisible();
	} );
} );

test.describe( 'cart', () => {
	test( 'empty cart', async ( { page } ) => {
		await page.goto( '/cart/' );
		await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
		await expect(
			page.locator( '.wp-block-woocommerce-empty-cart-block' )
		).toBeVisible();
	} );

	test( 'quantity, coupon, shipping and removal stay in sync with the store', async ( {
		page,
	} ) => {
		await addToCart( page, 'beanie' );
		await page.goto( '/cart/' );
		const main = page.locator( 'main' );
		await expect( main.getByText( 'Beanie' ).first() ).toBeVisible();

		await page
			.getByRole( 'button', { name: /increase quantity of beanie/i } )
			.click();
		await expect
			.poll( async () => ( await storeCart( page ) ).items[ 0 ].quantity )
			.toBe( 2 );

		// Courier is the only rate below 200 zł.
		const rates = ( await storeCart( page ) ).shipping_rates[ 0 ]
			.shipping_rates;
		expect( rates.map( ( rate ) => rate.method_id ) ).toEqual( [
			'flat_rate',
		] );

		await page.getByRole( 'button', { name: /add coupons/i } ).click();
		await page.getByLabel( /enter code/i ).fill( 'TEST10' );
		await page.getByRole( 'button', { name: /apply/i } ).click();
		await expect
			.poll( async () =>
				( await storeCart( page ) ).coupons.map( ( c ) => c.code )
			)
			.toEqual( [ 'test10' ] );
		await expect( main ).toContainText( /test10/i );

		await waitForBlocksLoaded( page );
		await expectAccessible( page );
		await expectNoOverflow( page );

		await page
			.getByRole( 'button', { name: /remove beanie from cart/i } )
			.click();
		await expect(
			page.locator( '.wp-block-woocommerce-empty-cart-block' )
		).toBeVisible();
		expect( ( await storeCart( page ) ).items_count ).toBe( 0 );
	} );

	test( 'free shipping is offered from 200 zł', async ( { page } ) => {
		await addToCart( page, 'sunglasses', 3 );
		const rates = ( await storeCart( page ) ).shipping_rates[ 0 ]
			.shipping_rates;
		expect( rates.map( ( rate ) => rate.method_id ) ).toContain(
			'free_shipping'
		);
	} );
} );

test.describe( 'checkout', () => {
	test( 'guest places an order with bank transfer', async ( { page } ) => {
		await addToCart( page, 'beanie' );
		await page.goto( '/checkout/' );

		// Distraction-free header: back to cart, no mini-cart.
		await expect(
			page.getByRole( 'link', { name: /back to cart/i } )
		).toBeVisible();
		await expect(
			page.locator( '.wp-block-woocommerce-mini-cart' )
		).toHaveCount( 0 );

		await page.getByLabel( 'Email address' ).fill( 'buyer@example.com' );
		await page.getByLabel( 'First name' ).first().fill( 'Jan' );
		await page.getByLabel( 'Last name' ).first().fill( 'Kowalski' );
		await page
			.getByLabel( 'Address', { exact: true } )
			.first()
			.fill( 'Prosta 1' );
		await page.getByLabel( 'Postal code' ).first().fill( '00-001' );
		await page.getByLabel( 'City' ).first().fill( 'Warszawa' );

		await waitForBlocksLoaded( page );
		await expectAccessible( page );
		await expectNoOverflow( page );

		await page.getByRole( 'button', { name: 'Place Order' } ).click();
		await page.waitForURL( /order-received/, { timeout: 30_000 } );
		await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
		await expect( page.locator( 'main' ) ).toContainText( 'Beanie' );
		await expectAccessible( page );
	} );
} );

test.describe( 'my account', () => {
	test( 'login form, then dashboard navigation', async ( { page } ) => {
		await page.goto( '/my-account/' );
		await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
		await expectAccessible( page );

		await page.getByLabel( /username or email/i ).fill( 'admin' );
		await page.locator( '#password' ).fill( 'password' );
		await page.getByRole( 'button', { name: 'Log in' } ).click();

		const nav = page.locator( '.woocommerce-MyAccount-navigation' );
		await expect(
			nav.getByRole( 'link', { name: 'Addresses' } )
		).toBeVisible();
		await nav.getByRole( 'link', { name: 'Addresses' } ).click();
		await expect( page.locator( 'h1' ) ).toHaveText( 'Addresses' );
		await expectAccessible( page );
		await expectNoOverflow( page );
	} );
} );

test.describe( 'palette reaches WooCommerce forms', () => {
	test.describe.configure( { mode: 'serial' } );
	test.afterAll( () => applyVariation( 'default' ) );

	test( 'checkout fields use the Midnight background', async ( { page } ) => {
		applyVariation( 'colors-midnight' );
		await addToCart( page, 'beanie' );
		await page.goto( '/checkout/' );
		const email = page.getByLabel( 'Email address' );
		await expect( email ).toBeVisible();
		const [ field, body ] = await Promise.all( [
			email.evaluate(
				( el ) => window.getComputedStyle( el ).backgroundColor
			),
			page.evaluate(
				() => window.getComputedStyle( document.body ).backgroundColor
			),
		] );
		expect( field ).toBe( body );
	} );
} );
