import { test, expect } from '@playwright/test';

// wp-env default administrator.
const ADMIN = { user: 'admin', password: 'password' };

test.describe( 'block editor parity', () => {
	test.skip(
		( { isMobile } ) => isMobile,
		'Editor is checked on desktop only.'
	);

	test( 'editor canvas loads theme presets and base.css', async ( {
		page,
	} ) => {
		await page.goto( '/wp-login.php' );
		await page.fill( '#user_login', ADMIN.user );
		await page.fill( '#user_pass', ADMIN.password );
		await page.click( '#wp-submit' );
		await page.waitForURL( /wp-admin/ );

		await page.goto( '/wp-admin/post-new.php?post_type=page' );
		const canvas = page.frameLocator( 'iframe[name="editor-canvas"]' );
		await expect( canvas.locator( 'body' ) ).toBeAttached( {
			timeout: 30_000,
		} );

		const result = await canvas.locator( 'body' ).evaluate( ( body ) => {
			const css = [ ...body.ownerDocument.styleSheets ]
				.map( ( sheet ) => {
					try {
						return [ ...sheet.cssRules ]
							.map( ( rule ) => rule.cssText )
							.join( '\n' );
					} catch {
						return '';
					}
				} )
				.join( '\n' );
			return {
				primary: body.ownerDocument.defaultView
					.getComputedStyle( body )
					.getPropertyValue( '--wp--preset--color--primary' )
					.trim(),
				hasBaseCss: css.includes(
					'accent-color: var(--wp--preset--color--primary)'
				),
			};
		} );

		expect( result.primary ).not.toBe( '' );
		expect( result.hasBaseCss ).toBe( true );
	} );
} );
