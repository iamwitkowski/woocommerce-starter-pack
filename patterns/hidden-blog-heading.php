<?php
/**
 * Title: Blog heading
 * Slug: starter-pack/hidden-blog-heading
 * Inserter: no
 * Description: Page heading for the posts index. Uses the title of the "Posts page" when one is set.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

$starter_pack_posts_page = (int) get_option( 'page_for_posts' );
$starter_pack_title      = $starter_pack_posts_page > 0 ? get_the_title( $starter_pack_posts_page ) : __( 'Blog', 'starter-pack' );
?>
<!-- wp:heading {"level":1,"align":"wide"} -->
<h1 class="wp-block-heading alignwide"><?php echo esc_html( $starter_pack_title ); ?></h1>
<!-- /wp:heading -->
