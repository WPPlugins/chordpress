<?php
/**
 * Admin asset loading.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Assets;

use ChordPress\Admin\Admin;
use ChordPress\Chords\DiagramSettings;
use ChordPress\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues admin styles, scripts and localized preview settings.
 */
final class AdminAssets {

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings service.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Enqueue assets on ChordPress admin screens.
	 *
	 * @return void
	 */
	public function enqueue(): void {
		if ( ! $this->is_chordpress_screen() ) {
			return;
		}

		$is_settings = $this->is_settings_screen();

		if ( $is_settings ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

		wp_enqueue_style(
			'chordpress-admin',
			plugins_url( 'css/admin.css', CHORDPRESS_FILE ),
			array(),
			CHORDPRESS_VERSION
		);

		wp_add_inline_style( 'chordpress-admin', $this->font_face_css() );

		wp_enqueue_script(
			'chordpress-admin',
			plugins_url( 'js/admin.js', CHORDPRESS_FILE ),
			$is_settings ? array( 'wp-color-picker' ) : array(),
			CHORDPRESS_VERSION,
			true
		);

		wp_localize_script( 'chordpress-admin', 'chordpressAdmin', $this->localize_data() );
	}

	/**
	 * Return the @font-face rules for the bundled Poppins font.
	 *
	 * The font files ship with the plugin so the admin screens do not contact
	 * Google Fonts and keep working offline.
	 *
	 * @return string
	 */
	private function font_face_css(): string {
		$subsets = array(
			'latin-ext' => 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C4, U+2113, U+2C60-2C7F, U+A720-A7FF',
			'latin'     => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD',
		);
		$css     = '';

		foreach ( array( 400, 500, 600, 700 ) as $weight ) {
			foreach ( $subsets as $subset => $unicode_range ) {
				$src = add_query_arg(
					'ver',
					CHORDPRESS_VERSION,
					plugins_url( 'resources/fonts/poppins-' . $weight . '-' . $subset . '.woff2', CHORDPRESS_FILE )
				);

				$css .= sprintf(
					'@font-face{font-family:"Poppins";font-style:normal;font-weight:%d;font-display:swap;src:url("%s") format("woff2");unicode-range:%s}',
					$weight,
					esc_url_raw( $src ),
					$unicode_range
				);
			}
		}

		return $css;
	}

	/**
	 * Determine whether the current screen belongs to ChordPress.
	 *
	 * @return bool
	 */
	private function is_chordpress_screen(): bool {
		$screen = get_current_screen();

		if ( null === $screen ) {
			return false;
		}

		if ( 'toplevel_page_' . Admin::MENU_SLUG === $screen->id ) {
			return true;
		}

		return 'guitar_chord' === $screen->post_type;
	}

	/**
	 * Determine whether the current screen is the ChordPress settings page.
	 *
	 * @return bool
	 */
	private function is_settings_screen(): bool {
		$screen = get_current_screen();

		return null !== $screen && 'toplevel_page_' . Admin::MENU_SLUG === $screen->id;
	}

	/**
	 * Return the data passed to the admin script.
	 *
	 * @return array<string, mixed>
	 */
	private function localize_data(): array {
		return array(
			'settings'    => $this->svguitar_settings(),
			'fieldMap'    => self::field_map(),
			'canvasWidth' => $this->canvas_width(),
			'i18n'        => array(
				'copied'       => __( 'Shortcode copied.', 'chordpress' ),
				'copyFailed'   => __( 'Could not copy the shortcode. Select it and copy it manually.', 'chordpress' ),
				'previewError' => __( 'The chord preview could not be rendered. Check the chord values.', 'chordpress' ),
			),
		);
	}

	/**
	 * Map SVGuitar settings to their ChordPress settings keys.
	 *
	 * @return array<string, string>
	 */
	public static function field_map(): array {
		return DiagramSettings::field_map();
	}

	/**
	 * Build the SVGuitar configuration from settings.
	 *
	 * @param array<string, mixed>|null $settings Settings values, or null to read them.
	 * @return array<string, mixed>
	 */
	public function svguitar_settings( ?array $settings = null ): array {
		return DiagramSettings::from_settings( $settings ?? $this->settings->all() );
	}

	/**
	 * Return the configured diagram width.
	 *
	 * @return int
	 */
	public function canvas_width(): int {
		return DiagramSettings::canvas_width( $this->settings->all() );
	}
}
