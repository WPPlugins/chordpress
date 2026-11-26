<?php
/**
 * Admin integration.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Admin;

use ChordPress\Assets\AdminAssets;
use ChordPress\License\License;
use ChordPress\License\LicensePage;
use ChordPress\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the admin menu, settings, assets, meta box and links.
 */
final class Admin {

	public const MENU_SLUG  = 'chordpress';
	public const CAPABILITY = 'manage_options';

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * License service.
	 *
	 * @var License
	 */
	private License $license;

	/**
	 * Admin asset loader.
	 *
	 * @var AdminAssets
	 */
	private AdminAssets $assets;

	/**
	 * Settings page renderer.
	 *
	 * @var SettingsPage
	 */
	private SettingsPage $settings_page;

	/**
	 * Chord meta box.
	 *
	 * @var ChordMetaBox
	 */
	private ChordMetaBox $meta_box;

	/**
	 * License page handler.
	 *
	 * @var LicensePage
	 */
	private LicensePage $license_page;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings service.
	 * @param License  $license  License service.
	 */
	public function __construct( Settings $settings, License $license ) {
		$this->settings      = $settings;
		$this->license       = $license;
		$this->assets        = new AdminAssets( $settings );
		$this->license_page  = new LicensePage( $license );
		$this->settings_page = new SettingsPage( $settings, $license, $this->license_page );
		$this->meta_box      = new ChordMetaBox();
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this->settings, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this->assets, 'enqueue' ) );
		add_action( 'add_meta_boxes', array( $this->meta_box, 'register' ) );
		add_action( 'save_post_guitar_chord', array( $this->meta_box, 'save' ), 10, 2 );
		add_action( 'admin_post_chordpress_license', array( $this->license_page, 'handle' ) );
		add_action( 'admin_notices', array( $this, 'render_notices' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( CHORDPRESS_FILE ), array( $this, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'meta_links' ), 10, 2 );
		add_filter( 'mce_external_plugins', array( $this, 'register_tinymce_plugin' ) );
		add_filter( 'mce_buttons', array( $this, 'register_tinymce_button' ) );
	}

	/**
	 * Register the admin menu.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'ChordPress', 'chordpress' ),
			__( 'ChordPress', 'chordpress' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this->settings_page, 'render' ),
			plugins_url( 'resources/images/icon-20x20.png', CHORDPRESS_FILE ),
			5
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'chordpress' ),
			__( 'Settings', 'chordpress' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this->settings_page, 'render' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Guitar Chords', 'chordpress' ),
			__( 'Guitar Chords', 'chordpress' ),
			self::CAPABILITY,
			'edit.php?post_type=guitar_chord'
		);
	}

	/**
	 * Render admin notices.
	 *
	 * @return void
	 */
	public function render_notices(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( $this->license->is_required() ) {
			$error = $this->license->config_error();

			if ( is_string( $error ) && '' !== $error ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
			}
		}

		if ( null === $screen || 'guitar_chord' !== $screen->post_type ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only post ID for a validation notice.
		$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;

		if ( $post_id <= 0 ) {
			return;
		}

		$error = get_transient( ChordMetaBox::error_key( $post_id ) );

		if ( ! is_string( $error ) || '' === $error ) {
			return;
		}

		delete_transient( ChordMetaBox::error_key( $post_id ) );
		echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array<int|string, string> $actions Existing links.
	 * @return array<int|string, string>
	 */
	public function action_links( array $actions ): array {
		$settings = '<a href="' . esc_url( admin_url( 'admin.php?page=' . self::MENU_SLUG ) ) . '">' . esc_html__( 'Settings', 'chordpress' ) . '</a>';
		$docs     = '<a href="' . esc_url( 'https://book.lewe.com/chordpress/' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'chordpress' ) . '</a>';

		return array_merge(
			array(
				'settings'      => $settings,
				'documentation' => $docs,
			),
			$actions
		);
	}

	/**
	 * Add plugin row meta links.
	 *
	 * @param array<int, string> $links Existing links.
	 * @param string             $file  Plugin file.
	 * @return array<int, string>
	 */
	public function meta_links( array $links, string $file ): array {
		if ( plugin_basename( CHORDPRESS_FILE ) !== $file ) {
			return $links;
		}

		$links[] = '<a href="' . esc_url( 'https://desk.lewe.com' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'chordpress' ) . '</a>';

		if ( ! $this->license->is_required() ) {
			$links[] = '<a href="' . esc_url( 'https://www.paypal.me/GeorgeLewe' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Donate', 'chordpress' ) . '</a>';
		}

		$links[] = '<a href="' . esc_url( 'https://wordpress.org/support/plugin/chordpress/reviews/' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Rate and Review!', 'chordpress' ) . '</a>';

		return $links;
	}

	/**
	 * Register the TinyMCE plugin.
	 *
	 * @param array<string, string> $plugins External plugins.
	 * @return array<string, string>
	 */
	public function register_tinymce_plugin( array $plugins ): array {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $plugins;
		}

		$plugins['chordpress'] = plugins_url( 'js/tinymce.js', CHORDPRESS_FILE ) . '?ver=' . rawurlencode( CHORDPRESS_VERSION );

		return $plugins;
	}

	/**
	 * Register the TinyMCE toolbar button.
	 *
	 * @param array<int, string> $buttons Toolbar buttons.
	 * @return array<int, string>
	 */
	public function register_tinymce_button( array $buttons ): array {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $buttons;
		}

		$buttons[] = 'chordpress';

		return $buttons;
	}
}
