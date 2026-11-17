<?php
/**
 * ChordPro HTML renderer.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Frontend;

use ChordPress\Chords\Chord;
use ChordPress\Chords\ChordRepository;
use ChordPress\Settings\Settings;
use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/**
 * Renders a parsed song to semantic, escaped HTML.
 */
final class SongRenderer {

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Transposer.
	 *
	 * @var Transposer
	 */
	private Transposer $transposer;

	/**
	 * Chord repository.
	 *
	 * @var ChordRepository
	 */
	private ChordRepository $repository;

	/**
	 * HTML sanitizer.
	 *
	 * @var HtmlSanitizer
	 */
	private HtmlSanitizer $sanitizer;

	/**
	 * Current settings values.
	 *
	 * @var array<string, mixed>
	 */
	private array $values = array();

	/**
	 * Current shortcode options.
	 *
	 * @var array<string, mixed>
	 */
	private array $options = array();

	/**
	 * Current song ID.
	 *
	 * @var string
	 */
	private string $song_id = '';

	/**
	 * Formatted chord name cache.
	 *
	 * @var array<string, array<int|string, string>>
	 */
	private array $format_cache = array();

	/**
	 * Unique chords used in the song.
	 *
	 * @var array<string, bool>
	 */
	private array $used_chords = array();

	/**
	 * Key names for the current song.
	 *
	 * @var array<int, string>
	 */
	private array $key_names = array();

	/**
	 * Constructor.
	 *
	 * @param Settings        $settings   Settings service.
	 * @param Transposer      $transposer Transposer.
	 * @param ChordRepository $repository Chord repository.
	 * @param HtmlSanitizer   $sanitizer  HTML sanitizer.
	 */
	public function __construct(
		Settings $settings,
		Transposer $transposer,
		ChordRepository $repository,
		HtmlSanitizer $sanitizer
	) {
		$this->settings   = $settings;
		$this->transposer = $transposer;
		$this->repository = $repository;
		$this->sanitizer  = $sanitizer;
	}

	/**
	 * Render a parsed song.
	 *
	 * @param array<string, mixed> $song    Parsed song.
	 * @param array<string, mixed> $options Render options.
	 * @return string
	 */
	public function render( array $song, array $options ): string {
		$this->values       = $this->settings->all();
		$this->options      = $options;
		$this->song_id      = wp_unique_id( 'cpress-' );
		$this->format_cache = array();
		$this->used_chords  = array();

		$directives      = isset( $song['directives'] ) && is_array( $song['directives'] ) ? $song['directives'] : array();
		$sections        = isset( $song['sections'] ) && is_array( $song['sections'] ) ? $song['sections'] : array();
		$key             = isset( $directives['key'] ) && is_scalar( $directives['key'] ) ? (string) $directives['key'] : '';
		$this->key_names = $this->transposer->key_variants( $key );

		$body        = $this->render_sections( $sections );
		$chord_sheet = $this->render_chord_sheet();
		$float       = (string) ( $options['float'] ?? 'none' );
		$classes     = 'cpress cpress--float-' . ( in_array( $float, array( 'left', 'right' ), true ) ? $float : 'none' );

		$html  = '<div id="' . esc_attr( $this->song_id ) . '" class="' . esc_attr( $classes ) . '" data-cpress-song';
		$html .= ' data-cpress-transpose="' . esc_attr( (string) $this->initial_transpose() ) . '"';
		$html .= ' data-cpress-harmonica="' . esc_attr( (string) ( $options['harmonica'] ?? 'none' ) ) . '"';
		$html .= ' data-cpress-key="' . esc_attr( $key ) . '"';
		$html .= ' data-cpress-key-names="' . esc_attr( $this->json_attr( $this->key_names ) ) . '"';
		$html .= ' data-cpress-width="' . esc_attr( (string) $this->canvas_width() ) . '">';

		$html .= $this->render_header( $directives );
		$html .= $this->render_meta( $directives );

		if ( ! empty( $options['interactive'] ) ) {
			$html .= $this->render_interaction( $directives );
		}

		if ( ! empty( $options['show_chord_sheet'] ) && ! empty( $options['show_chord_sheet_on_top'] ) ) {
			$html .= $chord_sheet;
		}

		$html .= '<div class="cpress-body">' . $body . '</div>';

		if ( ! empty( $options['show_chord_sheet'] ) && empty( $options['show_chord_sheet_on_top'] ) ) {
			$html .= $chord_sheet;
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render the title and subtitle.
	 *
	 * @param array<string, mixed> $directives Directives.
	 * @return string
	 */
	private function render_header( array $directives ): string {
		$html = '';

		$title = isset( $directives['title'] ) && is_scalar( $directives['title'] ) ? trim( (string) $directives['title'] ) : '';

		if ( '' !== $title && empty( $this->options['hide_title'] ) ) {
			$tag   = $this->heading_tag( 'select_title_level', 'h1' );
			$html .= '<' . $tag . ' class="cpress-title">' . esc_html( $title ) . '</' . $tag . '>';
		}

		$subtitle = isset( $directives['subtitle'] ) && is_scalar( $directives['subtitle'] ) ? trim( (string) $directives['subtitle'] ) : '';

		if ( '' !== $subtitle && empty( $this->options['hide_subtitle'] ) ) {
			$tag   = $this->heading_tag( 'select_subtitle_level', 'h2' );
			$html .= '<' . $tag . ' class="cpress-subtitle">' . esc_html( $subtitle ) . '</' . $tag . '>';
		}

		return $html;
	}

	/**
	 * Render the metadata block.
	 *
	 * @param array<string, mixed> $directives Directives.
	 * @return string
	 */
	private function render_meta( array $directives ): string {
		$rows = array();

		$value = static function ( array $data, string $key ): string {
			return isset( $data[ $key ] ) && is_scalar( $data[ $key ] ) ? trim( (string) $data[ $key ] ) : '';
		};

		$composer = $value( $directives, 'composer' );

		if ( '' !== $composer && empty( $this->options['hide_composer'] ) ) {
			$rows[] = $this->meta_row( __( 'Composer', 'chordpress' ), esc_html( $composer ) );
		}

		$artist = $value( $directives, 'artist' );

		if ( '' !== $artist && empty( $this->options['hide_artist'] ) ) {
			$rows[] = $this->meta_row( __( 'Artist', 'chordpress' ), esc_html( $artist ) );
		}

		$year = $value( $directives, 'year' );

		if ( '' !== $year && empty( $this->options['hide_year'] ) ) {
			$rows[] = $this->meta_row( __( 'Year', 'chordpress' ), esc_html( $year ) );
		}

		$album = $value( $directives, 'album' );

		if ( '' !== $album && empty( $this->options['hide_album'] ) ) {
			$rows[] = $this->meta_row( __( 'Album', 'chordpress' ), esc_html( $album ) );
		}

		$key = $value( $directives, 'key' );

		if ( '' !== $key && empty( $this->options['hide_key'] ) ) {
			$rows[] = $this->meta_row( __( 'Key (original)', 'chordpress' ), esc_html( $key ) );

			if ( 12 === count( $this->key_names ) ) {
				$spans = '';

				foreach ( $this->key_names as $index => $name ) {
					$spans .= '<span class="cpress-key-variant" data-cpress-key-variant="' . esc_attr( (string) $index ) . '"' . ( $index === $this->initial_transpose() ? '' : ' hidden' ) . '>' . esc_html( $name ) . '</span>';
				}

				$rows[] = $this->meta_row( __( 'Key (transposed)', 'chordpress' ), $spans );
			}
		}

		$time = $value( $directives, 'time' );

		if ( '' !== $time ) {
			$rows[] = $this->meta_row( __( 'Time', 'chordpress' ), esc_html( $time ) );
		}

		$tempo = $value( $directives, 'tempo' );

		if ( '' !== $tempo ) {
			$rows[] = $this->meta_row( __( 'Tempo', 'chordpress' ), esc_html( $tempo ) );
		}

		$capo = $value( $directives, 'capo' );

		if ( '' !== $capo ) {
			$rows[] = $this->meta_row( __( 'Capo', 'chordpress' ), esc_html( $capo ) );
		}

		if ( array() === $rows ) {
			return '';
		}

		return '<div class="cpress_meta">' . implode( '', $rows ) . '</div>';
	}

	/**
	 * Render one metadata row.
	 *
	 * @param string $label Row label.
	 * @param string $value Escaped row value.
	 * @return string
	 */
	private function meta_row( string $label, string $value ): string {
		return '<div class="cpress_meta-row"><span class="cpress_meta-label">' . esc_html( $label ) . ':</span> ' . $value . '</div>';
	}

	/**
	 * Render the interaction menu.
	 *
	 * @param array<string, mixed> $directives Directives.
	 * @return string
	 */
	private function render_interaction( array $directives ): string {
		$harmonica    = (string) ( $this->options['harmonica'] ?? 'none' );
		$is_harmonica = 'none' !== $harmonica;

		if ( ! $is_harmonica && ! empty( $this->options['hide_fontsize'] ) && ! empty( $this->options['hide_transpose'] ) && ! empty( $this->options['hide_print'] ) ) {
			return '';
		}

		$html = '<div class="cpress-interaction' . ( ! empty( $this->options['fixed_interaction'] ) ? ' fixed' : '' ) . '">';

		if ( empty( $this->options['hide_fontsize'] ) ) {
			$html .= '<div class="cpress-control">';
			$html .= '<span class="cpress-control-label">' . esc_html__( 'Font Size', 'chordpress' ) . '</span>';
			$html .= '<div class="cpress-btn-group">';
			$html .= '<button type="button" class="cpress-btn" data-cpress-action="font" data-cpress-step="-5">-</button>';
			$html .= '<button type="button" class="cpress-btn" data-cpress-action="font" data-cpress-step="0" data-cpress-font-label>100%</button>';
			$html .= '<button type="button" class="cpress-btn" data-cpress-action="font" data-cpress-step="5">+</button>';
			$html .= '</div></div>';
		}

		if ( empty( $this->options['hide_transpose'] ) ) {
			if ( $is_harmonica ) {
				$html .= $this->render_chromatic_transpose_control( $harmonica );
			} else {
				$html .= '<div class="cpress-control">';
				$html .= $this->render_guitar_transpose_controls( $directives );
				$html .= '</div>';
			}
		}

		if ( $is_harmonica ) {
			$html .= $this->render_harmonica_select( $harmonica );
		}

		if ( empty( $this->options['hide_print'] ) ) {
			$html .= '<div class="cpress-control cpress-control--print">';
			$html .= '<button type="button" class="cpress-btn" data-cpress-action="print">' . esc_html__( 'Print', 'chordpress' ) . '</button>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render guitar transpose controls.
	 *
	 * @param array<string, mixed> $directives Directives.
	 * @return string
	 */
	private function render_guitar_transpose_controls( array $directives ): string {
		$html = '<span class="cpress-control-label">' . esc_html__( 'Transpose', 'chordpress' ) . '</span>';

		if ( ! empty( $this->options['numeric_transpose'] ) ) {
			$html .= '<div class="cpress-btn-group">';
			$html .= '<button type="button" class="cpress-btn" data-cpress-action="transpose" data-cpress-step="-1">-</button>';
			$html .= '<button type="button" class="cpress-btn" data-cpress-action="transpose" data-cpress-step="0" data-cpress-transpose-label>' . esc_html( $this->transpose_label( $directives ) ) . '</button>';
			$html .= '<button type="button" class="cpress-btn" data-cpress-action="transpose" data-cpress-step="1">+</button>';
			$html .= '</div>';

			return $html;
		}

		$html .= '<select class="cpress-select" data-cpress-action="transpose-select" aria-label="' . esc_attr__( 'Transpose', 'chordpress' ) . '">';

		for ( $index = 0; $index < 12; $index++ ) {
			$label = 12 === count( $this->key_names )
				? $index . ' (' . $this->key_names[ $index ] . ')'
				: (string) $index;

			$html .= '<option value="' . esc_attr( (string) $index ) . '"' . ( $index === $this->initial_transpose() ? ' selected' : '' ) . '>' . esc_html( $label ) . '</option>';
		}

		$html .= '</select>';

		return $html;
	}

	/**
	 * Render the harmonica instrument selector.
	 *
	 * @param string $harmonica Current harmonica type.
	 * @return string
	 */
	private function render_harmonica_select( string $harmonica ): string {
		$html  = '<div class="cpress-control">';
		$html .= '<span class="cpress-control-label">' . esc_html__( 'Harmonica', 'chordpress' ) . '</span>';
		$html .= '<select class="cpress-select" data-cpress-action="harmonica" aria-label="' . esc_attr__( 'Harmonica', 'chordpress' ) . '">';

		$labels = array(
			'diatonic'  => __( 'Diatonic', 'chordpress' ),
			'chromatic' => __( 'Chromatic', 'chordpress' ),
			'tremolo'   => __( 'Tremolo', 'chordpress' ),
		);

		foreach ( array( 'diatonic', 'chromatic', 'tremolo' ) as $type ) {
			$html .= '<option value="' . esc_attr( $type ) . '"' . ( $type === $harmonica ? ' selected' : '' ) . '>' . esc_html( $labels[ $type ] ) . '</option>';
		}

		$html .= '</select>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render the chromatic harmonica transpose controls.
	 *
	 * The control is always present so the client script can reveal it when
	 * the visitor switches the instrument to Chromatic.
	 *
	 * @param string $harmonica Current harmonica type.
	 * @return string
	 */
	private function render_chromatic_transpose_control( string $harmonica ): string {
		$html  = '<div class="cpress-control cpress-control--chromatic"' . ( 'chromatic' === $harmonica ? '' : ' hidden' ) . ' data-cpress-chromatic-controls>';
		$html .= '<span class="cpress-control-label">' . esc_html__( 'Transpose', 'chordpress' ) . '</span>';
		$html .= '<div class="cpress-btn-group">';
		$html .= '<button type="button" class="cpress-btn" data-cpress-action="chromatic-transpose" data-cpress-step="-1">-</button>';
		$html .= '<button type="button" class="cpress-btn" data-cpress-action="chromatic-transpose" data-cpress-step="0" data-cpress-chromatic-label>0</button>';
		$html .= '<button type="button" class="cpress-btn" data-cpress-action="chromatic-transpose" data-cpress-step="1">+</button>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render all sections.
	 *
	 * @param array<int, array<string, mixed>> $sections Sections.
	 * @return string
	 */
	private function render_sections( array $sections ): string {
		$html = '';

		foreach ( $sections as $section ) {
			$type  = isset( $section['type'] ) && is_scalar( $section['type'] ) ? (string) $section['type'] : 'plain';
			$lines = isset( $section['lines'] ) && is_array( $section['lines'] ) ? $section['lines'] : array();

			if ( 'monospace' === $type ) {
				$html .= $this->render_monospace( $lines );
				continue;
			}

			$content = '';

			foreach ( $lines as $line ) {
				if ( is_array( $line ) ) {
					$content .= $this->render_line( $line );
				}
			}

			if ( 'verse' === $type ) {
				$html .= '<div class="cpress_verse">' . $content . '</div>';
			} elseif ( 'chorus' === $type ) {
				$html .= '<div class="cpress_chorus">' . $content . '</div>';
			} else {
				$html .= $content;
			}
		}

		return $html;
	}

	/**
	 * Render a monospace section.
	 *
	 * @param array<int, array<string, mixed>> $lines Lines.
	 * @return string
	 */
	private function render_monospace( array $lines ): string {
		$text = array();

		foreach ( $lines as $line ) {
			if ( isset( $line['text'] ) && is_scalar( $line['text'] ) ) {
				$text[] = (string) $line['text'];
			}
		}

		$content = implode( "\n", $text );

		if ( '' === trim( $content ) ) {
			return '';
		}

		return '<div class="cpress_monospace"><pre>' . esc_html( $content ) . '</pre></div>';
	}

	/**
	 * Render one line.
	 *
	 * @param array<string, mixed> $line Line.
	 * @return string
	 */
	private function render_line( array $line ): string {
		$type = isset( $line['type'] ) && is_scalar( $line['type'] ) ? (string) $line['type'] : 'lyric';

		if ( 'blank' === $type ) {
			return '<div class="cpress_line cpress_line--empty"></div>';
		}

		if ( 'comment' === $type ) {
			if ( ! empty( $this->options['hide_comments'] ) ) {
				return '';
			}

			$text = isset( $line['text'] ) && is_scalar( $line['text'] ) ? (string) $line['text'] : '';

			return '<div class="cpress_line cpress_line--comment"><span class="cpress_comment">' . esc_html( $text ) . '</span></div>';
		}

		if ( 'error' === $type ) {
			$raw = isset( $line['raw'] ) && is_scalar( $line['raw'] ) ? (string) $line['raw'] : '';

			return '<div class="cpress_line cpress_line--error"><span class="cpress_error">' . esc_html__( 'Missing square bracket in line', 'chordpress' ) . ':</span> <code>' . esc_html( $raw ) . '</code></div>';
		}

		$segments = isset( $line['segments'] ) && is_array( $line['segments'] ) ? $line['segments'] : array();

		if ( array() === $segments ) {
			return '<div class="cpress_line cpress_line--empty"></div>';
		}

		if ( ! empty( $this->options['hide_chords'] ) ) {
			$lyrics = '';

			foreach ( $segments as $segment ) {
				if ( is_array( $segment ) && isset( $segment['lyrics'] ) && is_scalar( $segment['lyrics'] ) ) {
					$lyrics .= (string) $segment['lyrics'];
				}
			}

			return '<div class="cpress_line"><div class="cpress_line_section"><div class="lyric">' . $this->sanitizer->inline( $lyrics ) . '</div></div></div><div class="cpress_clear"></div>';
		}

		$html = '<div class="cpress_line">';

		foreach ( $segments as $segment ) {
			if ( ! is_array( $segment ) ) {
				continue;
			}

			$chord  = isset( $segment['chord'] ) && is_scalar( $segment['chord'] ) ? trim( (string) $segment['chord'] ) : '';
			$lyrics = isset( $segment['lyrics'] ) && is_scalar( $segment['lyrics'] ) ? (string) $segment['lyrics'] : '';

			$html .= '<div class="cpress_line_section">';

			if ( '' !== $chord ) {
				$html .= $this->render_chord( $chord );
			} else {
				$html .= '<div class="chord chord--empty" aria-hidden="true">&nbsp;</div>';
			}

			$html .= '<div class="lyric">' . $this->sanitizer->inline( $lyrics ) . '</div>';
			$html .= '</div>';
		}

		$html .= '</div><div class="cpress_clear"></div>';

		return $html;
	}

	/**
	 * Render chord variants for one chord token.
	 *
	 * @param string $chord Chord token.
	 * @return string
	 */
	private function render_chord( string $chord ): string {
		$harmonica = (string) ( $this->options['harmonica'] ?? 'none' );

		if ( 'none' !== $harmonica ) {
			return $this->render_harmonica_chord( $chord, $harmonica );
		}

		if ( $this->transposer->is_static_chord( $chord ) ) {
			return '<div class="chord"><span class="chordshort">' . esc_html( $chord ) . '&nbsp;</span></div>';
		}

		$this->used_chords[ $chord ] = true;
		$names                       = $this->guitar_names( $chord );
		$initial                     = $this->initial_transpose();

		if ( empty( $this->options['interactive'] ) ) {
			return '<div class="chord"><span class="chordshort">' . esc_html( $names[ $initial ] ?? $chord ) . '&nbsp;</span></div>';
		}

		$html = '<div class="chord">';

		for ( $index = 0; $index < 12; $index++ ) {
			$html .= $this->chord_variant( (string) $index, $names[ $index ] ?? $chord, $index === $initial, $index );
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render harmonica note variants.
	 *
	 * @param string $chord     Harmonica note.
	 * @param string $harmonica Source harmonica type.
	 * @return string
	 */
	private function render_harmonica_chord( string $chord, string $harmonica ): string {
		$names = $this->harmonica_names( $chord, $harmonica );

		if ( empty( $this->options['interactive'] ) ) {
			return '<div class="chord"><span class="chordshort">' . esc_html( $names[ $harmonica ] ?? $chord ) . '&nbsp;</span></div>';
		}

		$html  = '<div class="chord">';
		$html .= $this->chord_variant( 'diatonic', $names['diatonic'] ?? $chord, 'diatonic' === $harmonica, 'diatonic' );

		for ( $index = 0; $index < 12; $index++ ) {
			$html .= $this->chord_variant( 'chromatic-' . $index, $names[ 'chromatic-' . $index ] ?? $chord, 'chromatic' === $harmonica && 0 === $index, 'chromatic-' . $index );
		}

		$html .= $this->chord_variant( 'tremolo', $names['tremolo'] ?? $chord, 'tremolo' === $harmonica, 'tremolo' );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render one chord variant span.
	 *
	 * @param string     $variant Variant key.
	 * @param string     $text    Chord text.
	 * @param bool       $visible Whether visible initially.
	 * @param int|string $suffix  Class suffix.
	 * @return string
	 */
	private function chord_variant( string $variant, string $text, bool $visible, int|string $suffix ): string {
		$class = 'chordshort chord-variant chord-hidable-' . $this->song_id . ' key-' . $this->song_id . '-' . $suffix;

		return '<span class="' . esc_attr( $class ) . '" data-cpress-variant="' . esc_attr( $variant ) . '"' . ( $visible ? '' : ' hidden' ) . '>' . esc_html( $text ) . '&nbsp;</span>';
	}

	/**
	 * Return formatted guitar chord names for all semitones.
	 *
	 * @param string $chord Chord token.
	 * @return array<int, string>
	 */
	private function guitar_names( string $chord ): array {
		if ( isset( $this->format_cache[ $chord ] ) ) {
			$names = array();

			for ( $index = 0; $index < 12; $index++ ) {
				$names[ $index ] = (string) ( $this->format_cache[ $chord ][ $index ] ?? $chord );
			}

			return $names;
		}

		$names = array();

		for ( $index = 0; $index < 12; $index++ ) {
			$names[ $index ] = $this->transposer->format_chord(
				$chord,
				$index,
				! empty( $this->options['hb_notation_input'] ),
				! empty( $this->options['display_hb'] )
			);
		}

		$this->format_cache[ $chord ] = $names;

		return $names;
	}

	/**
	 * Return harmonica variants for one note.
	 *
	 * @param string $chord     Harmonica note.
	 * @param string $harmonica Source type.
	 * @return array<int|string, string>
	 */
	private function harmonica_names( string $chord, string $harmonica ): array {
		if ( isset( $this->format_cache[ $chord ] ) ) {
			return $this->format_cache[ $chord ];
		}

		$names = array();

		foreach ( array( 'diatonic', 'chromatic', 'tremolo' ) as $type ) {
			$names[ $type ] = $this->transposer->translate_harmonica_note( $chord, $harmonica, $type );
		}

		$chromatic = $names['chromatic'];

		for ( $index = 0; $index < 12; $index++ ) {
			$names[ 'chromatic-' . $index ] = $this->transposer->transpose_chromatic_note( $chromatic, $index );
		}

		$this->format_cache[ $chord ] = $names;

		return $names;
	}

	/**
	 * Render the chord sheet.
	 *
	 * @return string
	 */
	private function render_chord_sheet(): string {
		if ( empty( $this->options['show_chord_sheet'] ) || 'none' !== ( $this->options['harmonica'] ?? 'none' ) || array() === $this->used_chords ) {
			return '';
		}

		$items = array();
		$names = array();

		foreach ( array_keys( $this->used_chords ) as $chord ) {
			$variants = $this->guitar_names( $chord );
			$items[]  = $variants;
			$names    = array_merge( $names, $variants );
		}

		$found = $this->repository->find_by_names( array_values( array_unique( $names ) ) );

		if ( array() === $found ) {
			return '';
		}

		$shapes = array();

		foreach ( $found as $chord ) {
			$shapes[ $chord->lookup_key() ] = $chord->to_preview_array();
		}

		$initial       = $this->initial_transpose();
		$item_html     = '';
		$visible_items = 0;

		foreach ( $items as $variants ) {
			$has_shape = false;

			foreach ( $variants as $name ) {
				try {
					if ( isset( $shapes[ Chord::normalize_name( $name ) ] ) ) {
						$has_shape = true;
						break;
					}
				} catch ( InvalidArgumentException ) {
					continue;
				}
			}

			if ( ! $has_shape ) {
				continue;
			}

			$initial_name      = $variants[ $initial ] ?? $variants[0] ?? '';
			$initial_has_shape = false;

			try {
				$initial_has_shape = isset( $shapes[ Chord::normalize_name( $initial_name ) ] );
			} catch ( InvalidArgumentException ) {
				$initial_has_shape = false;
			}

			if ( $initial_has_shape ) {
				++$visible_items;
			}

			$item_html .= '<div class="cpress-chord-container" data-cpress-chord-sheet-item data-cpress-names="' . esc_attr( $this->json_attr( $variants ) ) . '"' . ( $initial_has_shape ? '' : ' hidden' ) . '>';
			$item_html .= '<div class="cpress-chord" data-cpress-diagram data-cpress-width="' . esc_attr( (string) $this->canvas_width() ) . '"></div>';
			$item_html .= '<noscript><span class="cpress-chord-name">' . esc_html( $initial_name ) . '</span></noscript>';
			$item_html .= '</div>';
		}

		if ( 0 === $visible_items && '' === $item_html ) {
			return '';
		}

		$html  = '<div class="cpress_chordsheet" data-cpress-chord-sheet data-cpress-chords="' . esc_attr( $this->json_attr( $shapes ) ) . '"' . ( 0 === $visible_items ? ' hidden' : '' ) . '>';
		$html .= '<p class="cpress-chordsheet-intro"><i>' . esc_html__( 'These known chords are used in this song.', 'chordpress' ) . '</i></p>';
		$html .= $item_html;
		$html .= '</div>';

		return $html;
	}

	/**
	 * Return the initial normalized transpose.
	 *
	 * @return int
	 */
	private function initial_transpose(): int {
		return ( ( (int) ( $this->options['transpose'] ?? 0 ) % 12 ) + 12 ) % 12;
	}

	/**
	 * Return the transpose button label.
	 *
	 * @param array<string, mixed> $directives Directives.
	 * @return string
	 */
	private function transpose_label( array $directives ): string {
		$key = isset( $directives['key'] ) && is_scalar( $directives['key'] ) ? (string) $directives['key'] : '';
		$key = 12 === count( $this->key_names ) ? $this->key_names[ $this->initial_transpose() ] : '';

		return '' !== $key ? $this->initial_transpose() . ' (' . $key . ')' : (string) $this->initial_transpose();
	}

	/**
	 * Return a sanitized heading tag.
	 *
	 * @param string $setting  Setting key.
	 * @param string $fallback Default tag.
	 * @return string
	 */
	private function heading_tag( string $setting, string $fallback ): string {
		$tag = isset( $this->values[ $setting ] ) && is_scalar( $this->values[ $setting ] ) ? (string) $this->values[ $setting ] : $fallback;

		return in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $tag : $fallback;
	}

	/**
	 * Return the diagram width.
	 *
	 * @return int
	 */
	private function canvas_width(): int {
		$width = (int) ( $this->values['number_chord_canvasWidth'] ?? 180 );

		return max( 50, $width );
	}

	/**
	 * JSON-encode a data attribute value.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	private function json_attr( mixed $value ): string {
		$json = wp_json_encode( $value, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES );

		return is_string( $json ) ? $json : '[]';
	}
}
