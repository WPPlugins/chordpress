<?php
/**
 * Plugin bootstrap and lifecycle hooks.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress;

use ChordPress\Admin\Admin;
use ChordPress\Assets\PublicAssets;
use ChordPress\Chords\ChordPostType;
use ChordPress\Chords\ChordRepository;
use ChordPress\Frontend\Frontend;
use ChordPress\License\License;
use ChordPress\Migration\Migrator;
use ChordPress\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin entry point.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Chord post type registrar.
	 *
	 * @var ChordPostType|null
	 */
	private ?ChordPostType $chord_post_type = null;

	/**
	 * Admin integration.
	 *
	 * @var Admin|null
	 */
	private ?Admin $admin = null;

	/**
	 * Frontend integration.
	 *
	 * @var Frontend|null
	 */
	private ?Frontend $frontend = null;

	/**
	 * Register the plugin hooks.
	 *
	 * @return void
	 */
	public static function boot(): void {
		if ( self::$instance instanceof self ) {
			return;
		}

		self::$instance = new self();
		self::$instance->register_hooks();
	}

	/**
	 * Register runtime hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		$settings              = new Settings();
		$repository            = new ChordRepository();
		$this->chord_post_type = new ChordPostType();
		$this->admin           = new Admin( $settings, new License() );
		$this->frontend        = new Frontend( $settings, $repository, new PublicAssets( $settings ) );

		add_action( 'init', array( $this->chord_post_type, 'register' ) );
		add_action( 'admin_init', array( $this->chord_post_type, 'register_admin_columns' ) );

		$this->admin->register();
		$this->frontend->register();

		Migrator::register();
		Migrator::maybe_migrate();
	}

	/**
	 * Run installation routines.
	 *
	 * @return void
	 */
	public static function activate(): void {
		Migrator::maybe_migrate();
	}

	/**
	 * Run cleanup routines.
	 *
	 * @return void
	 */
	public static function deactivate(): void {}
}
