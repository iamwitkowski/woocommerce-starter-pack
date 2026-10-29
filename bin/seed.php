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

WP_CLI::success( 'Test store seeded.' );
