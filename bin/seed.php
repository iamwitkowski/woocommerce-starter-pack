<?php
/**
 * Idempotent seed for the local/CI test store. Development only — never shipped in the theme zip.
 *
 * Run: npx wp-env run cli wp eval-file wp-content/themes/starter-pack/bin/seed.php
 *
 * No strict_types declaration: `wp eval-file` evaluates this file and rejects it.
 *
 * @package StarterPack
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce must be active.' );
}

WP_CLI::runcommand( 'theme activate starter-pack' );
WP_CLI::runcommand( 'rewrite structure /%postname%/ --hard', array( 'exit_error' => false ) );

// Site identity used in screenshots and header/footer tests.
update_option( 'blogname', 'Starter Pack' );
update_option( 'blogdescription', 'Fast WooCommerce block theme' );

// Store settings: PLN, Polish VAT, prices entered and displayed with tax.
$starter_pack_options = array(
	'woocommerce_coming_soon'                => 'no',
	'woocommerce_default_country'            => 'PL',
	'woocommerce_currency'                   => 'PLN',
	'woocommerce_currency_pos'               => 'right_space',
	'woocommerce_price_decimal_sep'          => ',',
	'woocommerce_price_thousand_sep'         => ' ',
	'woocommerce_calc_taxes'                 => 'yes',
	'woocommerce_prices_include_tax'         => 'yes',
	'woocommerce_tax_display_shop'           => 'incl',
	'woocommerce_tax_display_cart'           => 'incl',
	'woocommerce_enable_coupons'             => 'yes',
	'woocommerce_ship_to_countries'          => 'specific',
	'woocommerce_specific_ship_to_countries' => array( 'PL' ),
);
foreach ( $starter_pack_options as $starter_pack_key => $starter_pack_value ) {
	update_option( $starter_pack_key, $starter_pack_value );
}

WP_CLI::runcommand( 'wc tool run install_pages --user=1', array( 'exit_error' => false ) );

// Sample catalogue. The WXR import does not fill the product lookup tables, so they are regenerated below;
// without that, variable products are synced as out of stock.
$starter_pack_product_count = (int) wp_count_posts( 'product' )->publish;
if ( 0 === $starter_pack_product_count ) {
	WP_CLI::runcommand( 'import ' . escapeshellarg( WC_ABSPATH . 'sample-data/sample_products.xml' ) . ' --authors=skip --quiet' );
	WP_CLI::runcommand( 'wc tool run regenerate_product_lookup_tables --user=1' );
	WP_CLI::runcommand( 'wc tool run regenerate_product_attributes_lookup_table --user=1' );
	WP_CLI::runcommand( 'action-scheduler run --quiet', array( 'exit_error' => false ) );
}

// Fixture: one out-of-stock variation (Hoodie: Blue + Logo "Yes").
foreach ( wc_get_products(
	array(
		'type'  => 'variable',
		'limit' => -1,
	)
) as $starter_pack_product ) {
	foreach ( $starter_pack_product->get_children() as $starter_pack_child_id ) {
		$starter_pack_variation = wc_get_product( $starter_pack_child_id );
		if ( ! $starter_pack_variation instanceof WC_Product_Variation ) {
			continue;
		}
		$starter_pack_attributes = $starter_pack_variation->get_attributes();
		$starter_pack_is_oos     = 'woo-hoodie' === $starter_pack_product->get_sku()
			&& 'blue' === ( $starter_pack_attributes['pa_color'] ?? '' )
			&& 'Yes' === ( $starter_pack_attributes['logo'] ?? '' );
		$starter_pack_variation->set_stock_status( $starter_pack_is_oos ? 'outofstock' : 'instock' );
		$starter_pack_variation->save();
	}
	WC_Product_Variable::sync( $starter_pack_product->get_id() );
	wc_delete_product_transients( $starter_pack_product->get_id() );
}

// Tax: 23% VAT for Poland.
if ( ! WC_Tax::find_rates( array( 'country' => 'PL' ) ) ) {
	WC_Tax::_insert_tax_rate(
		array(
			'tax_rate_country'  => 'PL',
			'tax_rate'          => '23.0000',
			'tax_rate_name'     => 'VAT',
			'tax_rate_priority' => 1,
			'tax_rate_compound' => 0,
			'tax_rate_shipping' => 1,
			'tax_rate_order'    => 0,
			'tax_rate_class'    => '',
		)
	);
}

// Offline payment methods only — no external services in tests.
foreach ( array( 'bacs', 'cod' ) as $starter_pack_gateway ) {
	$starter_pack_settings            = (array) get_option( "woocommerce_{$starter_pack_gateway}_settings", array() );
	$starter_pack_settings['enabled'] = 'yes';
	update_option( "woocommerce_{$starter_pack_gateway}_settings", $starter_pack_settings );
}

// Shipping: courier 15 PLN, free shipping from 200 PLN.
if ( ! WC_Shipping_Zones::get_zones() ) {
	$starter_pack_zone = new WC_Shipping_Zone();
	$starter_pack_zone->set_zone_name( 'Polska' );
	$starter_pack_zone->add_location( 'PL', 'country' );
	$starter_pack_zone->save();

	$starter_pack_flat = $starter_pack_zone->add_shipping_method( 'flat_rate' );
	update_option(
		"woocommerce_flat_rate_{$starter_pack_flat}_settings",
		array(
			'title'      => 'Kurier',
			'tax_status' => 'taxable',
			'cost'       => '15',
		)
	);

	$starter_pack_free = $starter_pack_zone->add_shipping_method( 'free_shipping' );
	update_option(
		"woocommerce_free_shipping_{$starter_pack_free}_settings",
		array(
			'title'      => 'Darmowa dostawa',
			'requires'   => 'min_amount',
			'min_amount' => '200',
		)
	);
}

// Coupon: 10% off.
if ( ! wc_get_coupon_id_by_code( 'TEST10' ) ) {
	$starter_pack_coupon = new WC_Coupon();
	$starter_pack_coupon->set_code( 'TEST10' );
	$starter_pack_coupon->set_discount_type( 'percent' );
	$starter_pack_coupon->set_amount( 10 );
	$starter_pack_coupon->save();
}

// Design-system fixture page: every globally styled element, used by contrast/axe tests.
$starter_pack_fixture = <<<'HTML'
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Heading 1</h1><!-- /wp:heading -->
<!-- wp:heading --><h2 class="wp-block-heading">Heading 2 — zażółć gęślą jaźń</h2><!-- /wp:heading -->
<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Heading 3</h3><!-- /wp:heading -->
<!-- wp:heading {"level":4} --><h4 class="wp-block-heading">Heading 4</h4><!-- /wp:heading -->
<!-- wp:heading {"level":5} --><h5 class="wp-block-heading">Heading 5</h5><!-- /wp:heading -->
<!-- wp:heading {"level":6} --><h6 class="wp-block-heading">Heading 6</h6><!-- /wp:heading -->
<!-- wp:paragraph --><p>Paragraph with <a href="/shop/">a link to the shop</a> and Polish characters: zażółć gęślą jaźń.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/shop/">Primary button</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>A quote.</p><!-- /wp:paragraph --><cite>Citation</cite></blockquote><!-- /wp:quote -->
<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>List item</li><!-- /wp:list-item --></ul><!-- /wp:list -->
<!-- wp:table --><figure class="wp-block-table"><table><thead><tr><th>Name</th><th>Price</th></tr></thead><tbody><tr><td>Hoodie</td><td>45 zł</td></tr></tbody></table><figcaption class="wp-element-caption">Table caption</figcaption></figure><!-- /wp:table -->
<!-- wp:code --><pre class="wp-block-code"><code>const code = true;</code></pre><!-- /wp:code -->
<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->
<!-- wp:html --><form onsubmit="return false"><p><label for="sp-email">E-mail</label><br><input id="sp-email" type="email" placeholder="you@example.com"></p><p><label for="sp-country">Country</label><br><select id="sp-country"><option>Polska</option></select></p><p><input id="sp-terms" type="checkbox"> <label for="sp-terms">I accept the terms</label></p><p><label for="sp-note">Note</label><br><textarea id="sp-note"></textarea></p></form><!-- /wp:html -->
<!-- wp:group {"className":"is-style-section-surface","layout":{"type":"constrained"}} --><div class="wp-block-group is-style-section-surface"><!-- wp:heading --><h2 class="wp-block-heading">Surface section</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Text with <a href="/shop/">a link</a>.</p><!-- /wp:paragraph --></div><!-- /wp:group -->
<!-- wp:group {"className":"is-style-section-inverted","layout":{"type":"constrained"}} --><div class="wp-block-group is-style-section-inverted"><!-- wp:heading --><h2 class="wp-block-heading">Inverted section</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Text with <a href="/shop/">a link</a>.</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/shop/">Inverted button</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->
HTML;

$starter_pack_fixture_page = get_page_by_path( 'design-system' );
// WP-CLI runs without a user, so kses would strip the form controls from trusted fixture markup.
kses_remove_filters();
wp_insert_post(
	array(
		'ID'           => $starter_pack_fixture_page ? $starter_pack_fixture_page->ID : 0,
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => 'design-system',
		'post_title'   => 'Design system',
		'post_content' => $starter_pack_fixture,
	)
);

// Blog fixtures: posts with featured images (taken from the product catalogue), a category and tags.
$starter_pack_guides = term_exists( 'Guides', 'category' );
if ( ! $starter_pack_guides ) {
	$starter_pack_guides = wp_insert_term( 'Guides', 'category', array( 'description' => 'How-to articles for customers.' ) );
}
$starter_pack_images = get_posts(
	array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'posts_per_page' => 3,
		'post__not_in'   => array( (int) get_option( 'woocommerce_placeholder_image' ) ),
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'fields'         => 'ids',
	)
);
$starter_pack_posts  = array(
	'how-to-choose-a-hoodie' => 'How to choose a hoodie',
	'caring-for-cotton'      => 'Caring for cotton clothes',
	'gift-ideas'             => 'Gift ideas — zażółć gęślą jaźń',
);
foreach ( array_keys( $starter_pack_posts ) as $starter_pack_index => $starter_pack_slug ) {
	$starter_pack_existing = get_page_by_path( $starter_pack_slug, OBJECT, 'post' );
	$starter_pack_post_id  = $starter_pack_existing ? $starter_pack_existing->ID : wp_insert_post(
		array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_name'     => $starter_pack_slug,
			'post_title'    => $starter_pack_posts[ $starter_pack_slug ],
			'post_excerpt'  => 'A short summary used on archive cards.',
			'post_content'  => '<!-- wp:paragraph --><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p><!-- /wp:paragraph --><!-- wp:heading --><h2 class="wp-block-heading">Section heading</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Sed posuere consectetur est at lobortis. <a href="/shop/">Visit the shop</a>.</p><!-- /wp:paragraph -->',
			'post_category' => array( (int) $starter_pack_guides['term_id'] ),
			'tags_input'    => array( 'cotton', 'care' ),
			'post_date'     => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( $starter_pack_index + 1 ) . ' days' ) ),
		)
	);
	if ( isset( $starter_pack_images[ $starter_pack_index ] ) ) {
		set_post_thumbnail( $starter_pack_post_id, $starter_pack_images[ $starter_pack_index ] );
	}
}

WP_CLI::success( 'Test store seeded.' );
