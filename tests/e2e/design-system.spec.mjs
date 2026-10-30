import { readFileSync } from 'node:fs';
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { wpCli } from './wp-cli.mjs';

const FIXTURE = '/design-system/';

// Theme style variations, by slug (presets) or title (full variations), with the file defining their palette.
const VARIATIONS = [
	{ name: 'default', palette: 'theme.json' },
	{ name: 'colors-ocean', palette: 'styles/colors/ocean.json' },
	{ name: 'colors-midnight', palette: 'styles/colors/midnight.json' },
	{ name: 'typography-editorial', palette: 'theme.json' },
	{ name: 'Editorial', palette: 'styles/editorial.json' },
];

const PALETTE = [
	'background',
	'surface',
	'text',
	'muted',
	'primary',
	'on-primary',
	'secondary',
	'accent',
];

// [foreground, background, minimum WCAG ratio]. These pairs are the palette contract documented in docs.
const CONTRAST_PAIRS = [
	[ 'text', 'background', 4.5 ],
	[ 'text', 'surface', 4.5 ],
	[ 'muted', 'background', 4.5 ],
	[ 'muted', 'surface', 4.5 ],
	[ 'primary', 'background', 4.5 ],
	[ 'on-primary', 'primary', 4.5 ],
	[ 'on-primary', 'secondary', 4.5 ],
	[ 'accent', 'background', 3 ],
];

function expectedPalette( file ) {
	const json = JSON.parse( readFileSync( file, 'utf8' ) );
	return Object.fromEntries(
		json.settings.color.palette.map( ( { slug, color } ) => [
			slug,
			color
				.slice( 1 )
				.match( /../g )
				.map( ( hex ) => parseInt( hex, 16 ) ),
		] )
	);
}

function applyVariation( name ) {
	wpCli(
		`wp --user=1 eval-file wp-content/themes/starter-pack/tests/e2e/bin/apply-style-variation.php ${ name }`
	);
}

function luminance( [ r, g, b ] ) {
	const [ R, G, B ] = [ r, g, b ].map( ( value ) => {
		const c = value / 255;
		return c <= 0.04045 ? c / 12.92 : ( ( c + 0.055 ) / 1.055 ) ** 2.4;
	} );
	return 0.2126 * R + 0.7152 * G + 0.0722 * B;
}

function contrast( a, b ) {
	const [ hi, lo ] = [ luminance( a ), luminance( b ) ].sort(
		( x, y ) => y - x
	);
	return ( hi + 0.05 ) / ( lo + 0.05 );
}

// Resolves palette presets to [r, g, b] through the browser, so the test reads what is actually rendered.
async function readPalette( page ) {
	return page.evaluate( ( slugs ) => {
		const probe = document.createElement( 'span' );
		document.body.append( probe );
		const result = {};
		for ( const slug of slugs ) {
			probe.style.color = `var(--wp--preset--color--${ slug })`;
			result[ slug ] = window
				.getComputedStyle( probe )
				.color.match( /\d+(\.\d+)?/g )
				.slice( 0, 3 )
				.map( Number );
		}
		probe.remove();
		return result;
	}, PALETTE );
}

test.describe( 'design system', () => {
	test.describe.configure( { mode: 'serial' } );

	test.afterAll( () => applyVariation( 'default' ) );

	for ( const variation of VARIATIONS ) {
		test.describe( `variation: ${ variation.name }`, () => {
			test.beforeAll( () => applyVariation( variation.name ) );

			test( 'palette defines every token and meets the contrast contract', async ( {
				page,
			} ) => {
				await page.goto( FIXTURE );
				const palette = await readPalette( page );
				// Proves the variation was actually applied, not just that the default palette passes.
				expect( palette ).toEqual(
					expectedPalette( variation.palette )
				);

				for ( const slug of PALETTE ) {
					expect( palette[ slug ], `token ${ slug }` ).toHaveLength(
						3
					);
				}
				for ( const [ fg, bg, min ] of CONTRAST_PAIRS ) {
					const ratio = contrast( palette[ fg ], palette[ bg ] );
					expect(
						ratio,
						`${ fg } on ${ bg } = ${ ratio.toFixed( 2 ) }`
					).toBeGreaterThanOrEqual( min );
				}
			} );

			test( 'fixture page has no WCAG 2.2 AA violations (axe)', async ( {
				page,
			} ) => {
				await page.goto( FIXTURE );
				const results = await new AxeBuilder( { page } )
					.withTags( [ 'wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa' ] )
					.analyze();
				expect(
					results.violations.map(
						( v ) =>
							`${ v.id }: ${ v.nodes.map( ( n ) => n.target ).join( ', ' ) }`
					)
				).toEqual( [] );
			} );

			test( 'palette reaches core and WooCommerce buttons', async ( {
				page,
			} ) => {
				await page.goto( FIXTURE );
				const primary = `rgb(${ ( await readPalette( page ) ).primary.join( ', ' ) })`;
				await expect(
					page.locator( '.wp-block-button__link', {
						hasText: 'Primary button',
					} )
				).toHaveCSS( 'background-color', primary );

				await page.goto( '/shop/' );
				await expect(
					page
						.locator(
							'.wc-block-components-product-button__button'
						)
						.first()
				).toHaveCSS( 'background-color', primary );
			} );

			test( 'no horizontal overflow on the fixture page', async ( {
				page,
			} ) => {
				await page.goto( FIXTURE );
				const overflow = await page.evaluate(
					() =>
						document.documentElement.scrollWidth - window.innerWidth
				);
				expect( overflow ).toBeLessThanOrEqual( 0 );
			} );
		} );
	}

	test( 'default typography downloads no web fonts', async ( { page } ) => {
		applyVariation( 'default' );
		const fonts = [];
		page.on( 'request', ( request ) => {
			if ( request.resourceType() === 'font' ) {
				fonts.push( request.url() );
			}
		} );
		await page.goto( FIXTURE, { waitUntil: 'networkidle' } );
		expect( fonts ).toEqual( [] );
	} );

	test( 'editorial typography uses only self-hosted fonts, incl. Polish glyphs', async ( {
		page,
	} ) => {
		applyVariation( 'typography-editorial' );
		const fonts = [];
		page.on( 'request', ( request ) => {
			if ( request.resourceType() === 'font' ) {
				fonts.push( new URL( request.url() ).pathname );
			}
		} );
		await page.goto( FIXTURE, { waitUntil: 'networkidle' } );

		const headingFont = await page
			.locator( 'h1' )
			.first()
			.evaluate( ( el ) => window.getComputedStyle( el ).fontFamily );
		expect( headingFont ).toMatch( /^Fraunces/ );
		expect( fonts.length ).toBeGreaterThan( 0 );
		expect( fonts.some( ( path ) => path.includes( 'latin-ext' ) ) ).toBe(
			true
		);
		for ( const path of fonts ) {
			expect( path ).toMatch(
				/^\/wp-content\/themes\/starter-pack\/assets\/fonts\//
			);
		}
	} );
} );
