/**
 * Mini-Cart focus return.
 *
 * WooCommerce 11.2 restores focus to the cart icon when the drawer was opened from it, but when the
 * drawer opens automatically after "Add to cart", closing it leaves focus on <body>. This keeps the
 * last focused element outside the drawer and returns focus there (or to the cart icon) on close.
 */
( function () {
	const drawer = document.querySelector( '.wc-block-mini-cart__drawer' );
	if ( ! drawer ) {
		return;
	}

	let lastOutside = null;

	document.addEventListener( 'focusin', ( event ) => {
		if ( ! drawer.contains( event.target ) ) {
			lastOutside = event.target;
		}
	} );

	new window.MutationObserver( () => {
		if ( drawer.getAttribute( 'aria-hidden' ) !== 'true' ) {
			return;
		}
		const active = drawer.ownerDocument.activeElement;
		if (
			active &&
			active !== drawer.ownerDocument.body &&
			! drawer.contains( active )
		) {
			return;
		}
		const target =
			lastOutside && lastOutside.isConnected
				? lastOutside
				: drawer.ownerDocument.querySelector(
						'.wc-block-mini-cart__button'
					);
		target?.focus();
	} ).observe( drawer, {
		attributes: true,
		attributeFilter: [ 'aria-hidden' ],
	} );
} )();
