<?php
/**
 * Test helper: applies a theme style variation as the user's global styles (as the Site Editor does),
 * or resets them with "default".
 *
 * Usage: wp --user=1 eval-file wp-content/themes/starter-pack/tests/e2e/bin/apply-style-variation.php <slug|title|default>
 * --user is required: without it the global styles post cannot get its wp_theme term and the front end never sees it.
 *
 * @package StarterPack
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

$starter_pack_name = $args[0] ?? 'default'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- $args is provided by `wp eval-file`.
$starter_pack_data = array(
	'version'                     => 3,
	'isGlobalStylesUserThemeJSON' => true,
);

if ( 'default' !== $starter_pack_name ) {
	$starter_pack_match = null;
	foreach ( WP_Theme_JSON_Resolver::get_style_variations() as $starter_pack_variation ) {
		if ( ( $starter_pack_variation['slug'] ?? $starter_pack_variation['title'] ) === $starter_pack_name ) {
			$starter_pack_match = $starter_pack_variation;
			break;
		}
	}
	if ( null === $starter_pack_match ) {
		WP_CLI::error( "Unknown style variation: {$starter_pack_name}" );
	}
	foreach ( array( 'settings', 'styles' ) as $starter_pack_key ) {
		if ( isset( $starter_pack_match[ $starter_pack_key ] ) ) {
			$starter_pack_data[ $starter_pack_key ] = $starter_pack_match[ $starter_pack_key ];
		}
	}
}

// wp_update_post() unslashes its input; without wp_slash() escaped quotes in font stacks break the JSON.
wp_update_post(
	array(
		'ID'           => WP_Theme_JSON_Resolver::get_user_global_styles_post_id(),
		'post_content' => wp_slash( wp_json_encode( $starter_pack_data ) ),
	)
);

WP_CLI::success( "Applied style variation: {$starter_pack_name}" );
