<?php
/**
 * Default navigation markup for the header and footer patterns.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

namespace StarterPack;

defined( 'ABSPATH' ) || exit;

/**
 * Builds navigation-link blocks that point at the store's real pages.
 *
 * Used only as the starting content of the header and footer patterns. Once
 * the merchant edits a menu in the Site Editor, WordPress stores it as a
 * wp_navigation post and this markup is no longer used.
 */
final class Navigation {

	private const MAX_CATEGORIES = 5;

	/**
	 * Shop link followed by top-level product categories.
	 */
	public static function header_links(): string {
		$links = self::page_links( array( 'shop' ) );

		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
				'number'     => self::MAX_CATEGORIES,
				'orderby'    => 'name',
			)
		);

		if ( is_array( $categories ) ) {
			foreach ( $categories as $category ) {
				$url = get_term_link( $category );
				if ( is_string( $url ) ) {
					$links .= self::link(
						array(
							'label' => $category->name,
							'type'  => 'product_cat',
							'id'    => $category->term_id,
							'url'   => $url,
							'kind'  => 'taxonomy',
						)
					);
				}
			}
		}

		return $links;
	}

	/**
	 * Customer-facing store pages.
	 */
	public static function footer_links(): string {
		return self::page_links( array( 'shop', 'myaccount', 'cart' ) );
	}

	/**
	 * Links to WooCommerce pages that exist and are published.
	 *
	 * @param string[] $pages WooCommerce page keys, e.g. "shop", "cart".
	 */
	private static function page_links( array $pages ): string {
		if ( ! function_exists( 'wc_get_page_id' ) ) {
			return '';
		}

		$links = '';
		foreach ( $pages as $page ) {
			$id = wc_get_page_id( $page );
			if ( $id > 0 && 'publish' === get_post_status( $id ) ) {
				$links .= self::link(
					array(
						'label' => get_the_title( $id ),
						'type'  => 'page',
						'id'    => $id,
						'url'   => (string) get_permalink( $id ),
						'kind'  => 'post-type',
					)
				);
			}
		}

		return $links;
	}

	/**
	 * Serialized core/navigation-link block.
	 *
	 * @param array<string, int|string> $attrs Block attributes.
	 */
	private static function link( array $attrs ): string {
		return serialize_block(
			array(
				'blockName'    => 'core/navigation-link',
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
	}
}
