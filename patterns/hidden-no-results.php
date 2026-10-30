<?php
/**
 * Title: No results
 * Slug: starter-pack/hidden-no-results
 * Inserter: no
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:paragraph -->
<p><?php echo esc_html__( 'Nothing found. Try a different search.', 'starter-pack' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:search {"label":"<?php echo esc_attr__( 'Search', 'starter-pack' ); ?>","buttonText":"<?php echo esc_attr__( 'Search', 'starter-pack' ); ?>"} /-->
