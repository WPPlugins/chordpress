<?php
/**
 * Typed plugin settings and sanitization.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Single-option settings schema for ChordPress.
 */
final class Settings {

	public const OPTION = 'chordpress_settings';
	public const GROUP  = 'chordpress';

	/**
	 * Settings schema.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private const SCHEMA = array(
		'checkbox_fixed_interaction'            => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_album'                   => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_artist'                  => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_chords'                  => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_comments'                => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_composer'                => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_fontsize'                => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_key'                     => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_print'                   => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_subtitle'                => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_title'                   => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_transpose'               => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hide_year'                    => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_hb_notation'                  => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_show_chord_sheet'             => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_show_chord_sheet_on_top'      => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_show_numeric_transpose'       => array(
			'type'    => 'bool',
			'default' => false,
		),
		'select_title_level'                    => array(
			'type'    => 'select',
			'default' => 'h1',
			'options' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ),
		),
		'select_subtitle_level'                 => array(
			'type'    => 'select',
			'default' => 'h2',
			'options' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ),
		),
		'text_meta_style'                       => array(
			'type'    => 'css',
			'default' => '',
		),
		'text_chord_style'                      => array(
			'type'    => 'css',
			'default' => '',
		),
		'text_lyrics_style'                     => array(
			'type'    => 'css',
			'default' => '',
		),
		'text_comment_style'                    => array(
			'type'    => 'css',
			'default' => '',
		),
		'text_chorus_style'                     => array(
			'type'    => 'css',
			'default' => '',
		),
		'text_verse_style'                      => array(
			'type'    => 'css',
			'default' => '',
		),
		'text_line_style'                       => array(
			'type'    => 'css',
			'default' => '',
		),
		'text_chord_backgroundColor'            => array(
			'type'    => 'color',
			'default' => 'none',
		),
		'number_chord_barreChordRadius'         => array(
			'type'    => 'float',
			'default' => 0.25,
			'min'     => 0.0,
			'max'     => 1.0,
		),
		'text_chord_barreChordStrokeColor'      => array(
			'type'    => 'color',
			'default' => '',
		),
		'number_chord_barreChordStrokeWidth'    => array(
			'type'    => 'int',
			'default' => 0,
			'min'     => 0,
			'max'     => 100,
		),
		'number_chord_canvasWidth'              => array(
			'type'    => 'int',
			'default' => 180,
			'min'     => 50,
			'max'     => 2000,
		),
		'text_chord_color'                      => array(
			'type'    => 'color',
			'default' => '#000000',
		),
		'number_chord_emptyStringIndicatorSize' => array(
			'type'    => 'float',
			'default' => 0.6,
			'min'     => 0.0,
			'max'     => 2.0,
		),
		'checkbox_chord_fixedDiagramPosition'   => array(
			'type'    => 'bool',
			'default' => false,
		),
		'text_chord_fontFamily'                 => array(
			'type'    => 'text',
			'default' => 'Arial, "Helvetica Neue", Helvetica, sans-serif',
		),
		'text_chord_fretColor'                  => array(
			'type'    => 'color',
			'default' => '',
		),
		'text_chord_fretLabelColor'             => array(
			'type'    => 'color',
			'default' => '',
		),
		'select_chord_fretLabelPosition'        => array(
			'type'    => 'select',
			'default' => 'right',
			'options' => array( 'left', 'right' ),
		),
		'number_chord_fretLabelFontSize'        => array(
			'type'    => 'int',
			'default' => 38,
			'min'     => 1,
			'max'     => 500,
		),
		'number_chord_frets'                    => array(
			'type'    => 'int',
			'default' => 4,
			'min'     => 1,
			'max'     => 24,
		),
		'number_chord_fretSize'                 => array(
			'type'    => 'float',
			'default' => 1.5,
			'min'     => 0.1,
			'max'     => 10.0,
		),
		'number_chord_nutSize'                  => array(
			'type'    => 'float',
			'default' => 0.85,
			'min'     => 0.1,
			'max'     => 5.0,
		),
		'text_chord_nutColor'                   => array(
			'type'    => 'color',
			'default' => '',
		),
		'text_chord_nutStrokeColor'             => array(
			'type'    => 'color',
			'default' => '',
		),
		'number_chord_nutStrokeWidth'           => array(
			'type'    => 'int',
			'default' => 0,
			'min'     => 0,
			'max'     => 100,
		),
		'text_chord_nutTextColor'               => array(
			'type'    => 'color',
			'default' => '#FFFFFF',
		),
		'number_chord_nutTextSize'              => array(
			'type'    => 'int',
			'default' => 24,
			'min'     => 1,
			'max'     => 500,
		),
		'number_chord_position'                 => array(
			'type'    => 'int',
			'default' => 1,
			'min'     => 1,
			'max'     => 24,
		),
		'number_chord_sidePadding'              => array(
			'type'    => 'float',
			'default' => 0.2,
			'min'     => 0.0,
			'max'     => 1.0,
		),
		'text_chord_stringColor'                => array(
			'type'    => 'color',
			'default' => '',
		),
		'select_chord_strings'                  => array(
			'type'    => 'int',
			'default' => 6,
			'min'     => 4,
			'max'     => 12,
			'options' => array( 4, 6 ),
		),
		'number_chord_strokeWidth'              => array(
			'type'    => 'int',
			'default' => 2,
			'min'     => 0,
			'max'     => 100,
		),
		'select_chord_style'                    => array(
			'type'    => 'select',
			'default' => 'normal',
			'options' => array( 'normal', 'handdrawn' ),
		),
		'number_chord_titleBottomMargin'        => array(
			'type'    => 'int',
			'default' => 0,
			'min'     => 0,
			'max'     => 500,
		),
		'text_chord_titleColor'                 => array(
			'type'    => 'color',
			'default' => '',
		),
		'number_chord_titleFontSize'            => array(
			'type'    => 'int',
			'default' => 48,
			'min'     => 1,
			'max'     => 500,
		),
		'number_chord_topFretWidth'             => array(
			'type'    => 'int',
			'default' => 10,
			'min'     => 0,
			'max'     => 100,
		),
		'text_chord_tuning'                     => array(
			'type'    => 'text',
			'default' => 'E,A,D,G,B,E',
		),
		'text_chord_tuningsColor'               => array(
			'type'    => 'color',
			'default' => '',
		),
		'number_chord_tuningsFontSize'          => array(
			'type'    => 'int',
			'default' => 28,
			'min'     => 1,
			'max'     => 500,
		),
		'checkbox_uninstall_delete_options'     => array(
			'type'    => 'bool',
			'default' => false,
		),
		'checkbox_uninstall_delete_chordposts'  => array(
			'type'    => 'bool',
			'default' => false,
		),
	);

	/**
	 * Register the settings option.
	 *
	 * @return void
	 */
	public function register(): void {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Return the complete settings schema.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function schema(): array {
		return self::SCHEMA;
	}

	/**
	 * Return default values for every setting.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		$defaults = array();

		foreach ( self::SCHEMA as $key => $field ) {
			$defaults[ $key ] = $field['default'];
		}

		return $defaults;
	}

	/**
	 * Return all settings merged with their defaults.
	 *
	 * @return array<string, mixed>
	 */
	public function all(): array {
		$stored = get_option( self::OPTION, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return $this->sanitize( array_merge( self::defaults(), $stored ) );
	}

	/**
	 * Return one setting value.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public function get( string $key ): mixed {
		$settings = $this->all();

		return $settings[ $key ] ?? null;
	}

	/**
	 * Sanitize and persist the complete settings array.
	 *
	 * @param array<string, mixed> $values Settings values.
	 * @return bool
	 */
	public function update( array $values ): bool {
		return update_option( self::OPTION, $this->sanitize( $values ) );
	}

	/**
	 * Sanitize a settings array.
	 *
	 * @param array<string, mixed> $input Raw settings input.
	 * @return array<string, mixed>
	 */
	public function sanitize( array $input ): array {
		$clean = self::defaults();

		foreach ( self::SCHEMA as $key => $field ) {
			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}

			$clean[ $key ] = $this->sanitize_value( $input[ $key ], $field );
		}

		return $clean;
	}

	/**
	 * Sanitize one value against its schema field.
	 *
	 * @param mixed                $value Raw value.
	 * @param array<string, mixed> $field Schema field.
	 * @return mixed
	 */
	private function sanitize_value( mixed $value, array $field ): mixed {
		$default = $field['default'] ?? null;

		if ( ! is_scalar( $value ) ) {
			return $default;
		}

		switch ( $field['type'] ) {
			case 'bool':
				return in_array( $value, array( true, 1, '1', 'true', 'on', 'yes' ), true );

			case 'int':
				if ( ! is_numeric( $value ) ) {
					return $default;
				}

				$number = (int) $value;

				if ( isset( $field['options'] ) && ! in_array( $number, $field['options'], true ) ) {
					return $default;
				}

				return max( (int) ( $field['min'] ?? PHP_INT_MIN ), min( (int) ( $field['max'] ?? PHP_INT_MAX ), $number ) );

			case 'float':
				if ( ! is_numeric( $value ) ) {
					return $default;
				}

				$number = (float) $value;

				return max( (float) ( $field['min'] ?? -INF ), min( (float) ( $field['max'] ?? INF ), $number ) );

			case 'select':
				$value = (string) $value;

				return in_array( $value, $field['options'] ?? array(), true ) ? $value : $default;

			case 'color':
				return $this->sanitize_color( (string) $value, (string) $default );

			case 'css':
				return $this->sanitize_css( (string) $value );

			case 'text':
			default:
				return $this->sanitize_text( (string) $value );
		}
	}

	/**
	 * Sanitize plain text.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private function sanitize_text( string $value ): string {
		$value = preg_replace( '/<[^>]*>/', '', $value );
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', is_string( $value ) ? $value : '' );

		return trim( is_string( $value ) ? $value : '' );
	}

	/**
	 * Sanitize a CSS value against style-block breakouts and script execution.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	public function sanitize_css( string $value ): string {
		$value = trim( $value );
		$value = str_replace( array( '{', '}' ), '', $value );
		$value = preg_replace( '/<[^>]*>/', '', $value );
		$value = is_string( $value ) ? $value : '';
		$value = str_ireplace(
			array( '</style', '<script', 'javascript:', 'vbscript:', 'expression(', 'behavior:', '-moz-binding', '@import' ),
			'',
			$value
		);
		$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value );

		return trim( is_string( $value ) ? $value : '' );
	}

	/**
	 * Sanitize a CSS color value.
	 *
	 * @param string $value    Raw value.
	 * @param string $fallback Default value.
	 * @return string
	 */
	private function sanitize_color( string $value, string $fallback ): string {
		$value = trim( $value );

		if ( '' === $value ) {
			return '';
		}

		$lower = strtolower( $value );

		if ( 'none' === $lower || 'transparent' === $lower ) {
			return $lower;
		}

		if ( preg_match( '/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^[a-z]+$/i', $value ) ) {
			return $value;
		}

		if ( preg_match( '/^(rgb|rgba|hsl|hsla)\([0-9.,%\s\/+-]+\)$/i', $value ) ) {
			return $value;
		}

		return $fallback;
	}
}
