<?php
/**
 * SVGuitar configuration from settings.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Chords;

defined( 'ABSPATH' ) || exit;

/**
 * Maps ChordPress settings to SVGuitar configuration.
 */
final class DiagramSettings {

	/**
	 * Return the SVGuitar configuration for settings values.
	 *
	 * @param array<string, mixed> $values Settings values.
	 * @return array<string, mixed>
	 */
	public static function from_settings( array $values ): array {
		$config = array(
			'style'                    => (string) ( $values['select_chord_style'] ?? 'normal' ),
			'strings'                  => (int) ( $values['select_chord_strings'] ?? 6 ),
			'frets'                    => (int) ( $values['number_chord_frets'] ?? 4 ),
			'position'                 => (int) ( $values['number_chord_position'] ?? 1 ),
			'fretLabelPosition'        => (string) ( $values['select_chord_fretLabelPosition'] ?? 'right' ),
			'fretLabelFontSize'        => (int) ( $values['number_chord_fretLabelFontSize'] ?? 38 ),
			'tuningsFontSize'          => (int) ( $values['number_chord_tuningsFontSize'] ?? 28 ),
			'fingerSize'               => (float) ( $values['number_chord_nutSize'] ?? 0.85 ),
			'fingerTextSize'           => (int) ( $values['number_chord_nutTextSize'] ?? 24 ),
			'barreChordRadius'         => (float) ( $values['number_chord_barreChordRadius'] ?? 0.25 ),
			'barreChordStrokeWidth'    => (int) ( $values['number_chord_barreChordStrokeWidth'] ?? 0 ),
			'fretSize'                 => (float) ( $values['number_chord_fretSize'] ?? 1.5 ),
			'sidePadding'              => (float) ( $values['number_chord_sidePadding'] ?? 0.2 ),
			'fontFamily'               => (string) ( $values['text_chord_fontFamily'] ?? 'Arial, "Helvetica Neue", Helvetica, sans-serif' ),
			'titleFontSize'            => (int) ( $values['number_chord_titleFontSize'] ?? 48 ),
			'titleBottomMargin'        => (int) ( $values['number_chord_titleBottomMargin'] ?? 0 ),
			'emptyStringIndicatorSize' => (float) ( $values['number_chord_emptyStringIndicatorSize'] ?? 0.6 ),
			'strokeWidth'              => (int) ( $values['number_chord_strokeWidth'] ?? 2 ),
			'nutWidth'                 => (int) ( $values['number_chord_topFretWidth'] ?? 10 ),
			'fixedDiagramPosition'     => (bool) ( $values['checkbox_chord_fixedDiagramPosition'] ?? false ),
		);

		foreach ( self::field_map() as $config_key => $setting_key ) {
			if ( in_array( $config_key, array( 'tuning', 'fixedDiagramPosition' ), true ) ) {
				continue;
			}

			if ( ! array_key_exists( $config_key, $config ) ) {
				$value = $values[ $setting_key ] ?? '';

				if ( is_string( $value ) && '' !== $value ) {
					$config[ $config_key ] = $value;
				}
			}
		}

		$tuning           = (string) ( $values['text_chord_tuning'] ?? 'E,A,D,G,B,E' );
		$config['tuning'] = array_values(
			array_filter(
				array_map( 'trim', explode( ',', $tuning ) ),
				static fn ( string $note ): bool => '' !== $note
			)
		);

		if ( array() === $config['tuning'] ) {
			$config['tuning'] = array( 'E', 'A', 'D', 'G', 'B', 'E' );
		}

		if ( '' === (string) $config['fontFamily'] ) {
			$config['fontFamily'] = 'Arial, "Helvetica Neue", Helvetica, sans-serif';
		}

		return $config;
	}

	/**
	 * Return the configured diagram width.
	 *
	 * @param array<string, mixed> $values Settings values.
	 * @return int
	 */
	public static function canvas_width( array $values ): int {
		$width = (int) ( $values['number_chord_canvasWidth'] ?? 180 );

		return max( 50, $width );
	}

	/**
	 * Map SVGuitar settings to their ChordPress settings keys.
	 *
	 * @return array<string, string>
	 */
	public static function field_map(): array {
		return array(
			'backgroundColor'          => 'text_chord_backgroundColor',
			'barreChordRadius'         => 'number_chord_barreChordRadius',
			'barreChordStrokeColor'    => 'text_chord_barreChordStrokeColor',
			'barreChordStrokeWidth'    => 'number_chord_barreChordStrokeWidth',
			'color'                    => 'text_chord_color',
			'emptyStringIndicatorSize' => 'number_chord_emptyStringIndicatorSize',
			'fixedDiagramPosition'     => 'checkbox_chord_fixedDiagramPosition',
			'fontFamily'               => 'text_chord_fontFamily',
			'fretColor'                => 'text_chord_fretColor',
			'fretLabelColor'           => 'text_chord_fretLabelColor',
			'fretLabelPosition'        => 'select_chord_fretLabelPosition',
			'fretLabelFontSize'        => 'number_chord_fretLabelFontSize',
			'frets'                    => 'number_chord_frets',
			'fretSize'                 => 'number_chord_fretSize',
			'fingerSize'               => 'number_chord_nutSize',
			'fingerColor'              => 'text_chord_nutColor',
			'fingerStrokeColor'        => 'text_chord_nutStrokeColor',
			'fingerStrokeWidth'        => 'number_chord_nutStrokeWidth',
			'fingerTextColor'          => 'text_chord_nutTextColor',
			'fingerTextSize'           => 'number_chord_nutTextSize',
			'position'                 => 'number_chord_position',
			'sidePadding'              => 'number_chord_sidePadding',
			'stringColor'              => 'text_chord_stringColor',
			'strings'                  => 'select_chord_strings',
			'strokeWidth'              => 'number_chord_strokeWidth',
			'style'                    => 'select_chord_style',
			'titleBottomMargin'        => 'number_chord_titleBottomMargin',
			'titleColor'               => 'text_chord_titleColor',
			'titleFontSize'            => 'number_chord_titleFontSize',
			'nutWidth'                 => 'number_chord_topFretWidth',
			'tuning'                   => 'text_chord_tuning',
			'tuningsColor'             => 'text_chord_tuningsColor',
			'tuningsFontSize'          => 'number_chord_tuningsFontSize',
		);
	}
}
