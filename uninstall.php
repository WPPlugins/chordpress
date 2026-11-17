<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package ChordPress
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/includes/autoload.php';

( new \ChordPress\Migration\Uninstaller(
	new \ChordPress\Migration\WordPressStore(),
	new \ChordPress\Settings\Settings()
) )->uninstall();
