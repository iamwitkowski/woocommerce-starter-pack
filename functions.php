<?php
/**
 * Theme bootstrap. Keep this file limited to loading and registering the theme.
 *
 * @package StarterPack
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/autoload.php';

( new StarterPack\Theme() )->register();
