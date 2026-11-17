<?php
/**
 * Frontend shortcodes.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Frontend;

use ChordPress\Assets\PublicAssets;
use ChordPress\Chords\Chord;
use ChordPress\Chords\ChordRepository;
use ChordPress\Chords\DiagramSettings;
use ChordPress\Settings\Settings;
use ChordPress\Support\Notation;
use InvalidArgumentException;
use RuntimeException;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the public shortcodes.
 */
final class Frontend {

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Chord repository.
	 *
	 * @var ChordRepository
	 */
	private ChordRepository $repository;

	/**
	 * Public assets.
	 *
	 * @var PublicAssets
	 */
	private PublicAssets $assets;

	/**
	 * Parser instance.
	 *
	 * @var ChordProParser|null
	 */
	private ?ChordProParser $parser = null;

	/**
	 * Renderer instance.
	 *
	 * @var SongRenderer|null
	 */
	private ?SongRenderer $renderer = null;

	/**
	 * Constructor.
	 *
	 * @param Settings        $settings   Settings service.
	 * @param ChordRepository $repository Chord repository.
	 * @param PublicAssets    $assets     Public assets.
	 */
	public function __construct( Settings $settings, ChordRepository $repository, PublicAssets $assets ) {
		$this->settings   = $settings;
		$this->repository = $repository;
		$this->assets     = $assets;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		$this->assets->register();
	}

	/**
	 * Register shortcodes.
	 *
	 * @return void
	 */
	public function register_shortcodes(): void {
		add_shortcode( 'chordpress', array( $this, 'render_song' ) );
		add_shortcode( 'chordpress-chord', array( $this, 'render_chord' ) );
	}

	/**
	 * Render a ChordPro song.
	 *
	 * @param array<string, mixed>|string $atts    Shortcode attributes.
	 * @param string|null                 $content Shortcode content.
	 * @param string                      $tag     Shortcode tag.
	 * @return string
	 */
	public function render_song( array|string $atts, ?string $content = null, string $tag = '' ): string {
		$atts    = array_change_key_case( (array) $atts, CASE_LOWER );
		$args    = shortcode_atts(
			array(
				'float'       => 'none',
				'format'      => 'yes',
				'hbnotation'  => 'no',
				'interactive' => 'no',
				'transpose'   => '0',
				'harmonica'   => 'none',
			),
			$atts,
			$tag
		);
		$content = do_shortcode( (string) $content );

		if ( ! self::is_truthy( (string) $args['format'] ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core content filter is required for format=no parity.
			return '[chordpress]' . apply_filters( 'the_content', $content ) . '[/chordpress]';
		}

		if ( '' === trim( $content ) ) {
			return '';
		}

		$settings = $this->settings->all();
		$options  = $this->build_options( $atts, $args, $settings );
		$song     = $this->parser()->parse( $content );

		$this->assets->enqueue();

		try {
			$renderer = $this->renderer();
		} catch ( RuntimeException ) {
			return $this->warning( __( 'ChordPress notation data is unavailable.', 'chordpress' ) );
		}

		return $renderer->render( $song, $options );
	}

	/**
	 * Render a single chord diagram.
	 *
	 * @param array<string, mixed>|string $atts    Shortcode attributes.
	 * @param string|null                 $content Shortcode content.
	 * @param string                      $tag     Shortcode tag.
	 * @return string
	 */
	public function render_chord( array|string $atts, ?string $content = null, string $tag = '' ): string {
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		$args = shortcode_atts(
			array(
				'chord'    => '',
				'barre'    => '',
				'fingers'  => '',
				'frets'    => '',
				'strings'  => '',
				'position' => '',
				'title'    => '',
				'tuning'   => '',
			),
			$atts,
			$tag
		);

		$this->assets->enqueue();

		$chord_id = absint( $args['chord'] );

		if ( '' !== trim( (string) $args['chord'] ) ) {
			$chord = $this->repository->find_by_id( $chord_id );

			if ( ! $chord instanceof Chord ) {
				return $this->warning(
					sprintf(
						/* translators: %s: chord post ID. */
						__( 'There is no chord record with the ID %s. Please check your input or create a proper post for this chord first.', 'chordpress' ),
						(string) $chord_id
					)
				);
			}
		} else {
			try {
				$data = array(
					'name'    => '' !== trim( (string) $args['title'] ) ? sanitize_text_field( (string) $args['title'] ) : __( 'No Title', 'chordpress' ),
					'barres'  => sanitize_text_field( (string) $args['barre'] ),
					'fingers' => str_replace( array( '[', ']' ), array( '(', ')' ), sanitize_text_field( (string) $args['fingers'] ) ),
					'tuning'  => sanitize_text_field( (string) $args['tuning'] ),
				);

				foreach ( array( 'frets', 'strings', 'position' ) as $numeric ) {
					if ( '' !== (string) $args[ $numeric ] ) {
						$data[ $numeric ] = absint( $args[ $numeric ] );
					}
				}

				$chord = Chord::from_array( $data );
			} catch ( InvalidArgumentException ) {
				return $this->warning( __( 'The chord attributes are invalid. Please check the fingers, barres and tuning values.', 'chordpress' ) );
			}
		}

		$settings = $this->settings->all();
		$width    = DiagramSettings::canvas_width( $settings );
		$shape    = $chord->to_preview_array();

		return '<div class="cpress-chord" data-cpress-chord-diagram data-cpress-width="' . esc_attr( (string) $width ) . '" data-cpress-shape="' . esc_attr( $this->json_attr( $shape ) ) . '"><noscript><span class="cpress-chord-name">' . esc_html( $chord->name ) . '</span></noscript></div>';
	}

	/**
	 * Build render options from shortcode attributes and settings.
	 *
	 * @param array<string, mixed> $atts     Lowercased attributes.
	 * @param array<string, mixed> $args     Parsed default arguments.
	 * @param array<string, mixed> $settings Settings values.
	 * @return array<string, mixed>
	 */
	private function build_options( array $atts, array $args, array $settings ): array {
		$float = isset( $atts['float'] ) ? sanitize_key( (string) $atts['float'] ) : 'none';

		if ( 'no' === $float || ! in_array( $float, array( 'left', 'right', 'none' ), true ) ) {
			$float = 'none';
		}

		$harmonica = isset( $atts['harmonica'] ) ? sanitize_key( (string) $atts['harmonica'] ) : 'none';

		if ( ! in_array( $harmonica, array( 'diatonic', 'chromatic', 'tremolo' ), true ) ) {
			$harmonica = 'none';
		}

		return array(
			'float'                   => $float,
			'format'                  => 'yes',
			'interactive'             => self::is_truthy( (string) $args['interactive'] ),
			'transpose'               => (int) $args['transpose'],
			'hb_notation_input'       => self::is_truthy( (string) $args['hbnotation'] ),
			'display_hb'              => (bool) ( $settings['checkbox_hb_notation'] ?? false ),
			'hide_album'              => $this->option_bool( $atts, 'hidealbum', $settings, 'checkbox_hide_album' ),
			'hide_artist'             => $this->option_bool( $atts, 'hideartist', $settings, 'checkbox_hide_artist' ),
			'hide_chords'             => $this->option_bool( $atts, 'hidechords', $settings, 'checkbox_hide_chords' ),
			'hide_comments'           => $this->option_bool( $atts, 'hidecomments', $settings, 'checkbox_hide_comments' ),
			'hide_composer'           => $this->option_bool( $atts, 'hidecomposer', $settings, 'checkbox_hide_composer' ),
			'hide_fontsize'           => $this->option_bool( $atts, 'hidefontsize', $settings, 'checkbox_hide_fontsize' ),
			'hide_key'                => $this->option_bool( $atts, 'hidekey', $settings, 'checkbox_hide_key' ),
			'hide_print'              => $this->option_bool( $atts, 'hideprint', $settings, 'checkbox_hide_print' ),
			'hide_subtitle'           => $this->option_bool( $atts, 'hidesubtitle', $settings, 'checkbox_hide_subtitle' ),
			'hide_title'              => $this->option_bool( $atts, 'hidetitle', $settings, 'checkbox_hide_title' ),
			'hide_transpose'          => $this->option_bool( $atts, 'hidetranspose', $settings, 'checkbox_hide_transpose' ),
			'hide_year'               => $this->option_bool( $atts, 'hideyear', $settings, 'checkbox_hide_year' ),
			'numeric_transpose'       => $this->option_bool( $atts, 'numerictranspose', $settings, 'checkbox_show_numeric_transpose' ),
			'fixed_interaction'       => $this->option_bool( $atts, 'fixedinteraction', $settings, 'checkbox_fixed_interaction' ),
			'show_chord_sheet'        => $this->option_bool( $atts, 'showchordsheet', $settings, 'checkbox_show_chord_sheet' ),
			'show_chord_sheet_on_top' => $this->option_bool( $atts, 'showchordsheetontop', $settings, 'checkbox_show_chord_sheet_on_top' ),
			'harmonica'               => $harmonica,
		);
	}

	/**
	 * Resolve a shortcode boolean attribute or a global setting.
	 *
	 * @param array<string, mixed> $atts    Lowercased attributes.
	 * @param string               $attr    Attribute name.
	 * @param array<string, mixed> $settings Settings values.
	 * @param string               $setting Setting key.
	 * @return bool
	 */
	private function option_bool( array $atts, string $attr, array $settings, string $setting ): bool {
		if ( array_key_exists( $attr, $atts ) ) {
			return self::is_truthy( (string) $atts[ $attr ] );
		}

		return (bool) ( $settings[ $setting ] ?? false );
	}

	/**
	 * Return the parser.
	 *
	 * @return ChordProParser
	 */
	private function parser(): ChordProParser {
		if ( ! $this->parser instanceof ChordProParser ) {
			$this->parser = new ChordProParser();
		}

		return $this->parser;
	}

	/**
	 * Return the renderer.
	 *
	 * @return SongRenderer
	 */
	private function renderer(): SongRenderer {
		if ( ! $this->renderer instanceof SongRenderer ) {
			$this->renderer = new SongRenderer(
				$this->settings,
				new Transposer( new Notation() ),
				$this->repository,
				new HtmlSanitizer()
			);
		}

		return $this->renderer;
	}

	/**
	 * Return a warning callout.
	 *
	 * @param string $message Warning message.
	 * @return string
	 */
	private function warning( string $message ): string {
		return '<div class="cpress cpress-callout cpress-callout-warning" role="alert"><strong>' . esc_html__( 'ChordPress Warning', 'chordpress' ) . '</strong><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * JSON-encode a data attribute value.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private function json_attr( mixed $value ): string {
		$json = wp_json_encode( $value, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES );

		return is_string( $json ) ? $json : '{}';
	}

	/**
	 * Whether a shortcode boolean value is true.
	 *
	 * @param string $value Value.
	 * @return bool
	 */
	private static function is_truthy( string $value ): bool {
		return in_array( strtolower( trim( $value ) ), array( 'yes', '1', 'true', 'on' ), true );
	}
}
