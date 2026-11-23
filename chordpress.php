<?php
/**
 * Plugin Name:       Lewe ChordPress
 * Plugin URI:        https://lewe.gitbook.io/lewe-chordpress/
 * Description:       Renders ChordPro formatted text and chord diagrams in WordPress sites.
 * Version:           5.0.2
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Author:            George Lewe
 * Author URI:        https://www.lewe.com
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       chordpress
 * Domain Path:       /languages
 *
 * @package ChordPress
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

define( 'CHORDPRESS_VERSION', '5.0.2' );
define( 'CHORDPRESS_FILE', __FILE__ );
define( 'CHORDPRESS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CHORDPRESS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Licensing is disabled for the free 5.0 release but can be enabled in wp-config.php.
 */
if ( ! defined( 'CHORDPRESS_LICENSE_REQUIRED' ) ) {
	define( 'CHORDPRESS_LICENSE_REQUIRED', false );
}

require_once __DIR__ . '/includes/autoload.php';

/**
 * Load the generated license configuration when the build provides it.
 */
if ( is_readable( __DIR__ . '/includes/license-config.php' ) ) {
	require_once __DIR__ . '/includes/license-config.php';
}

register_activation_hook( __FILE__, array( \ChordPress\Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \ChordPress\Plugin::class, 'deactivate' ) );

add_action( 'plugins_loaded', array( \ChordPress\Plugin::class, 'boot' ) );
