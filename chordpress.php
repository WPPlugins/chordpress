<?php

/**
 * ----------------------------------------------------------------------------
 * The plugin bootstrap file
 * ----------------------------------------------------------------------------
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.lewe.com
 * @since             1.0.0
 * @package           ChordPress
 *
 * @wordpress-plugin
 * Plugin Name:       Lewe ChordPress
 * Plugin URI:        https://lewe.gitbook.io/lewe-chordpress/
 * Description:       This plugin renders ChordPro formatted text and chord diagrams in WordPress sites.
 * Version:           4.0.1
 * Author:            George Lewe
 * Author URI:        https://www.lewe.com
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.txt
 * Text Domain:       chordpress
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort.
 */
if (!defined('WPINC')) die;

/**
 * Current plugin info.
 * Version number based on SemVer - https://semver.org
 */
define('CHORDPRESS_NAME', 'ChordPress');
define('CHORDPRESS_VERSION', '4.0.1');
define('CHORDPRESS_AUTHOR', 'George Lewe');
define('CHORDPRESS_AUTHOR_URI', 'https://www.lewe.com');
define('CHORDPRESS_DOC_URI', 'https://lewe.gitbook.io/lewe-chordpress/');
define('CHORDPRESS_SUPPORT_URI', 'https://georgelewe.atlassian.net/servicedesk/customer/portal/5');

/**
 * Plugin requires licensing.
 * This will enable/disable licensing features.
 */
define('CHORDPRESS_LICENSE_REQUIRED', false);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-chordpress-activator.php
 *
 * @since 1.0.0
 */
function activate_chordpress() {
  require_once plugin_dir_path(__FILE__) . 'includes/class-chordpress-activator.php';
  ChordPress_Activator::activate();
}

register_activation_hook(__FILE__, 'activate_chordpress');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-chordpress-deactivator.php
 *
 * @since 1.0.0
 */
function deactivate_chordpress() {
  require_once plugin_dir_path(__FILE__) . 'includes/class-chordpress-deactivator.php';
  ChordPress_Deactivator::deactivate();
}

register_deactivation_hook(__FILE__, 'deactivate_chordpress');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
$core_plugin_file = plugin_dir_path(__FILE__) . 'includes/class-chordpress-plugin.php';
if (file_exists($core_plugin_file)) {
  require $core_plugin_file;
} else {
  add_action('admin_notices', function() {
    echo '<div class="notice notice-error"><p>' . esc_html__('ChordPress Error: Core plugin file is missing.', 'chordpress') . '</p></div>';
  });
  return;
}

/**
 * The ChordPress rendering class.
 */
$renderer_file = plugin_dir_path(__FILE__) . 'includes/class-chordpress-renderer.php';
if (file_exists($renderer_file)) {
  require $renderer_file;
}

/**
 * The ChordPress chord rendering class.
 */
$chord_file = plugin_dir_path(__FILE__) . 'includes/class-chordpress-chord.php';
if (file_exists($chord_file)) {
  require $chord_file;
}

/**
 * The ChordPress chord meta box.
 */
$metabox_file = plugin_dir_path(__FILE__) . 'admin/partials/chordpress-admin-chord-meta-box.php';
if (file_exists($metabox_file)) {
  require $metabox_file;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks, then kicking
 * off the plugin from this point in the file does not affect the page life
 * cycle.
 *
 * @since    1.0.0
 */
function run_chordpress() {
  if (class_exists('ChordPress_Plugin')) {
    $plugin = new ChordPress_Plugin();
    $plugin->run();
  } else {
    add_action('admin_notices', function() {
      echo '<div class="notice notice-error"><p>' . esc_html__('ChordPress Error: Plugin class not found. Please check file integrity.', 'chordpress') . '</p></div>';
    });
  }
}

run_chordpress();
