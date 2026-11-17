<?php
/**
 * Public asset loading.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Assets;

use ChordPress\Chords\DiagramSettings;
use ChordPress\Settings\Settings;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues public styles, scripts and site-specific CSS.
 */
final class PublicAssets {

	public const HANDLE = 'chordpress-public';

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Whether assets were already enqueued.
	 *
	 * @var bool
	 */
	private bool $enqueued = false;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings service.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register the asset hook.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}

	/**
	 * Enqueue assets when the queried content contains a ChordPress shortcode.
	 *
	 * @return void
	 */
	public function maybe_enqueue(): void {
		if ( is_admin() ) {
			return;
		}

		$post = get_post();

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( has_shortcode( $post->post_content, 'chordpress' ) || has_shortcode( $post->post_content, 'chordpress-chord' ) ) {
			$this->enqueue();
		}
	}

	/**
	 * Enqueue public assets and localized data.
	 *
	 * @return void
	 */
	public function enqueue(): void {
		if ( $this->enqueued ) {
			return;
		}

		$this->enqueued = true;

		if ( ! wp_style_is( self::HANDLE, 'registered' ) ) {
			wp_register_style(
				self::HANDLE,
				plugins_url( 'css/public.css', CHORDPRESS_FILE ),
				array(),
				CHORDPRESS_VERSION
			);
		}

		if ( ! wp_script_is( self::HANDLE, 'registered' ) ) {
			wp_register_script(
				self::HANDLE,
				plugins_url( 'js/public.js', CHORDPRESS_FILE ),
				array(),
				CHORDPRESS_VERSION,
				true
			);
		}

		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
		wp_localize_script( self::HANDLE, 'chordpressPublic', $this->localize_data() );

		$css = $this->site_css();

		if ( '' !== $css ) {
			wp_add_inline_style( self::HANDLE, $css );
		}
	}

	/**
	 * Return the data passed to the public script.
	 *
	 * @return array<string, mixed>
	 */
	private function localize_data(): array {
		return array(
			'settings' => DiagramSettings::from_settings( $this->settings->all() ),
			'i18n'     => array(
				'chordError' => __( 'The chord diagram could not be rendered.', 'chordpress' ),
			),
		);
	}

	/**
	 * Build the site-specific CSS from the sanitized settings.
	 *
	 * @return string
	 */
	private function site_css(): string {
		$settings = $this->settings->all();
		$rules    = array();

		$add = static function ( string $selector, mixed $value ) use ( &$rules ): void {
			$value = is_string( $value ) ? trim( $value ) : '';

			if ( '' !== $value ) {
				$rules[] = $selector . '{' . $value . '}';
			}
		};

		$add( '.cpress .cpress_meta', (string) ( $settings['text_meta_style'] ?? '' ) );
		$add( '.cpress .cpress_line', (string) ( $settings['text_line_style'] ?? '' ) );
		$add( '.cpress .cpress_line_section .chord .chordshort', (string) ( $settings['text_chord_style'] ?? '' ) );
		$add( '.cpress .cpress_line_section .lyric', (string) ( $settings['text_lyrics_style'] ?? '' ) );
		$add( '.cpress .cpress_chorus', (string) ( $settings['text_chorus_style'] ?? '' ) );
		$add( '.cpress .cpress_verse', (string) ( $settings['text_verse_style'] ?? '' ) );
		$add( '.cpress .cpress_comment', (string) ( $settings['text_comment_style'] ?? '' ) );

		return implode( "\n", $rules );
	}
}
