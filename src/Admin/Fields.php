<?php
/**
 * Settings field metadata and rendering.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Admin;

use ChordPress\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Maps the typed settings schema to admin labels, tabs and controls.
 */
final class Fields {

	/**
	 * Return the settings tabs.
	 *
	 * @return array<string, string> Tab slug to label.
	 */
	public static function tabs(): array {
		return array(
			'tab_output'     => __( 'Output Options', 'chordpress' ),
			'tab_formatting' => __( 'Formatting Options', 'chordpress' ),
			'tab_chord'      => __( 'Chord Diagram Options', 'chordpress' ),
			'tab_plugin'     => __( 'Plugin Options', 'chordpress' ),
			'tab_info'       => __( 'Plugin Info', 'chordpress' ),
		);
	}

	/**
	 * Return all fields merged with their schema definition.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function all(): array {
		$schema   = Settings::schema();
		$metadata = self::metadata();
		$fields   = array();

		foreach ( $schema as $key => $field ) {
			$meta = $metadata[ $key ] ?? array(
				'tab'         => 'tab_plugin',
				'label'       => ucwords( str_replace( '_', ' ', $key ) ),
				'description' => '',
			);

			$fields[ $key ] = array_merge( $meta, $field );
		}

		return $fields;
	}

	/**
	 * Return the fields that belong to one tab.
	 *
	 * @param string $tab Tab slug.
	 * @return array<string, array<string, mixed>>
	 */
	public static function for_tab( string $tab ): array {
		return array_filter(
			self::all(),
			static fn ( array $field ): bool => ( $field['tab'] ?? '' ) === $tab
		);
	}

	/**
	 * Return the UI metadata keys.
	 *
	 * @return array<int, string>
	 */
	public static function metadata_keys(): array {
		return array_keys( self::metadata() );
	}

	/**
	 * Render one settings field row.
	 *
	 * @param string               $key   Setting key.
	 * @param array<string, mixed> $field Field metadata and schema.
	 * @param mixed                $value Current value.
	 * @return void
	 */
	public static function render( string $key, array $field, mixed $value ): void {
		$id    = 'chordpress_settings_' . $key;
		$name  = Settings::OPTION . '[' . $key . ']';
		$type  = (string) ( $field['type'] ?? 'text' );
		$label = (string) ( $field['label'] ?? $key );

		echo '<div class="mb-3 row chordpress-field chordpress-field--' . esc_attr( $type ) . '">';
		echo '<label class="col-12 col-sm-4 col-form-label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		echo '<div class="col-12 col-sm-8">';

		switch ( $type ) {
			case 'bool':
				echo '<div class="form-check">';
				echo '<input class="form-check-input" type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"';
				checked( (bool) $value, true );
				echo '> ';
				echo '<label class="form-check-label" for="' . esc_attr( $id ) . '">' . esc_html__( 'Yes', 'chordpress' ) . '</label>';
				echo '</div>';
				break;

			case 'select':
				self::render_select( $id, $name, $field, $value );
				break;

			case 'int':
			case 'float':
				self::render_number( $id, $name, $field, $value, $type );
				break;

			case 'color':
				echo '<input type="text" class="form-control chordpress-input-color" maxlength="25" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" placeholder="' . esc_attr__( '#000000 or none', 'chordpress' ) . '">';
				break;

			case 'css':
				echo '<input type="text" class="form-control code" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '">';
				break;

			case 'text':
			default:
				echo '<input type="text" class="form-control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '">';
				break;
		}

		if ( '' !== (string) ( $field['description'] ?? '' ) ) {
			echo '<div class="form-text">' . esc_html( (string) $field['description'] ) . '</div>';
		}

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render a select control.
	 *
	 * @param string               $id    Element ID.
	 * @param string               $name  Field name.
	 * @param array<string, mixed> $field Field metadata.
	 * @param mixed                $value Current value.
	 * @return void
	 */
	private static function render_select( string $id, string $name, array $field, mixed $value ): void {
		$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
		$key     = (string) ( $field['label'] ?? '' );

		echo '<select class="form-select" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';

		foreach ( $options as $option ) {
			$option_value = (string) $option;

			echo '<option value="' . esc_attr( $option_value ) . '"';
			selected( (string) $value, $option_value );
			echo '>' . esc_html( self::option_label( $key, $option_value ) ) . '</option>';
		}

		echo '</select>';
	}

	/**
	 * Render a number control.
	 *
	 * @param string               $id    Element ID.
	 * @param string               $name  Field name.
	 * @param array<string, mixed> $field Field metadata.
	 * @param mixed                $value Current value.
	 * @param string               $type  Field type.
	 * @return void
	 */
	private static function render_number( string $id, string $name, array $field, mixed $value, string $type ): void {
		$step = 'float' === $type ? '0.01' : '1';
		$min  = isset( $field['min'] ) && is_numeric( $field['min'] ) ? (string) $field['min'] : '';
		$max  = isset( $field['max'] ) && is_numeric( $field['max'] ) ? (string) $field['max'] : '';

		echo '<input type="number" class="form-control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '" step="' . esc_attr( $step ) . '"';

		if ( '' !== $min ) {
			echo ' min="' . esc_attr( $min ) . '"';
		}

		if ( '' !== $max ) {
			echo ' max="' . esc_attr( $max ) . '"';
		}

		echo '>';
	}

	/**
	 * Return a human-readable option label.
	 *
	 * @param string $label  Field label.
	 * @param string $option Option value.
	 * @return string
	 */
	private static function option_label( string $label, string $option ): string {
		if ( in_array( $option, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
			return sprintf(
				/* translators: %s: heading level number. */
				__( 'Header %s', 'chordpress' ),
				substr( $option, 1 )
			);
		}

		if ( 'left' === $option ) {
			return __( 'Left', 'chordpress' );
		}

		if ( 'right' === $option ) {
			return __( 'Right', 'chordpress' );
		}

		if ( 'normal' === $option ) {
			return __( 'Normal', 'chordpress' );
		}

		if ( 'handdrawn' === $option ) {
			return __( 'Hand-drawn', 'chordpress' );
		}

		return $option;
	}

	/**
	 * Return the admin metadata for every setting.
	 *
	 * @return array<string, array<string, string>>
	 */
	private static function metadata(): array {
		return array(
			'checkbox_fixed_interaction'            => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Fixed Interaction', 'chordpress' ),
				'description' => __( 'Keep the interaction menu fixed at the bottom of the page. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_album'                   => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Album', 'chordpress' ),
				'description' => __( 'Do not display the album directive.', 'chordpress' ),
			),
			'checkbox_hide_artist'                  => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Artist', 'chordpress' ),
				'description' => __( 'Do not display the artist directive.', 'chordpress' ),
			),
			'checkbox_hide_chords'                  => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Chords', 'chordpress' ),
				'description' => __( 'Print lyrics without chords. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_comments'                => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Comments', 'chordpress' ),
				'description' => __( 'Do not display comment directives. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_composer'                => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Composer', 'chordpress' ),
				'description' => __( 'Do not display the composer directive. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_fontsize'                => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Font Size', 'chordpress' ),
				'description' => __( 'Hide the font size control in interactive mode. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_key'                     => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Key', 'chordpress' ),
				'description' => __( 'Do not display the key directive.', 'chordpress' ),
			),
			'checkbox_hide_print'                   => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Print', 'chordpress' ),
				'description' => __( 'Hide the print button in interactive mode. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_subtitle'                => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Subtitle', 'chordpress' ),
				'description' => __( 'Do not display the subtitle directive. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_title'                   => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Title', 'chordpress' ),
				'description' => __( 'Do not display the title directive. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_transpose'               => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Transpose', 'chordpress' ),
				'description' => __( 'Hide the transpose control in interactive mode. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hide_year'                    => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Hide Year', 'chordpress' ),
				'description' => __( 'Do not display the year directive. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_hb_notation'                  => array(
				'tab'         => 'tab_output',
				'label'       => __( 'H/B Notation', 'chordpress' ),
				'description' => __( 'Display chords in H/B notation, where H replaces B and B replaces Bb.', 'chordpress' ),
			),
			'checkbox_show_chord_sheet'             => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Show Chord Sheet', 'chordpress' ),
				'description' => __( 'Show a chord sheet with diagrams for every chord used in the song. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_show_chord_sheet_on_top'      => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Show Chord Sheet on top', 'chordpress' ),
				'description' => __( 'Display the chord sheet before the song instead of after it. This can be overridden per shortcode.', 'chordpress' ),
			),
			'checkbox_show_numeric_transpose'       => array(
				'tab'         => 'tab_output',
				'label'       => __( 'Numeric Transpose', 'chordpress' ),
				'description' => __( 'Replace the transpose drop-down with a numeric plus/minus input. This can be overridden per shortcode.', 'chordpress' ),
			),
			'select_title_level'                    => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Title Level', 'chordpress' ),
				'description' => __( 'Select the heading level for the title directive.', 'chordpress' ),
			),
			'select_subtitle_level'                 => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Subtitle Level', 'chordpress' ),
				'description' => __( 'Select the heading level for the subtitle directive.', 'chordpress' ),
			),
			'text_meta_style'                       => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Meta Information Style', 'chordpress' ),
				'description' => __( 'CSS for the meta header block, for example: font-style: italic;', 'chordpress' ),
			),
			'text_chord_style'                      => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Chord Style', 'chordpress' ),
				'description' => __( 'CSS for chords in the song text, for example a background color or badge.', 'chordpress' ),
			),
			'text_lyrics_style'                     => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Lyrics Style', 'chordpress' ),
				'description' => __( 'CSS for lyrics. Leave empty to use the page default.', 'chordpress' ),
			),
			'text_comment_style'                    => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Comment Style', 'chordpress' ),
				'description' => __( 'CSS for comment directives.', 'chordpress' ),
			),
			'text_chorus_style'                     => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Chorus Style', 'chordpress' ),
				'description' => __( 'CSS for chorus sections.', 'chordpress' ),
			),
			'text_verse_style'                      => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Verse Style', 'chordpress' ),
				'description' => __( 'CSS for verse sections.', 'chordpress' ),
			),
			'text_line_style'                       => array(
				'tab'         => 'tab_formatting',
				'label'       => __( 'Line Style', 'chordpress' ),
				'description' => __( 'CSS for song lines. Leave empty to use the default margin.', 'chordpress' ),
			),
			'text_chord_backgroundColor'            => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Background Color', 'chordpress' ),
				'description' => __( 'Background color of the chord diagram. Use none or leave empty for transparent.', 'chordpress' ),
			),
			'number_chord_barreChordRadius'         => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Barre Border Radius', 'chordpress' ),
				'description' => __( 'Border radius of a barre relative to the nut size.', 'chordpress' ),
			),
			'text_chord_barreChordStrokeColor'      => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Barre Bar Stroke Color', 'chordpress' ),
				'description' => __( 'Stroke color of a barre. Leave empty to use the nut color.', 'chordpress' ),
			),
			'number_chord_barreChordStrokeWidth'    => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Barre Bar Stroke Width', 'chordpress' ),
				'description' => __( 'Stroke width of a barre in pixels.', 'chordpress' ),
			),
			'number_chord_canvasWidth'              => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Canvas Width', 'chordpress' ),
				'description' => __( 'Width of chord diagrams in pixels. The height is adjusted automatically.', 'chordpress' ),
			),
			'text_chord_color'                      => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Global Color', 'chordpress' ),
				'description' => __( 'Global color of the chord chart. More specific colors override this value.', 'chordpress' ),
			),
			'number_chord_emptyStringIndicatorSize' => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Empty String Indicator Size', 'chordpress' ),
				'description' => __( 'Size of the X and O indicators relative to the string spacing.', 'chordpress' ),
			),
			'checkbox_chord_fixedDiagramPosition'   => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Fixed Diagram Position', 'chordpress' ),
				'description' => __( 'Keep the distance between the diagram and the top of the SVG constant, whether a title is shown or not.', 'chordpress' ),
			),
			'text_chord_fontFamily'                 => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Font Family', 'chordpress' ),
				'description' => __( 'Font family used for letters and numbers in chord diagrams.', 'chordpress' ),
			),
			'text_chord_fretColor'                  => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Fret Color', 'chordpress' ),
				'description' => __( 'Fret color. Leave empty to use the global color.', 'chordpress' ),
			),
			'text_chord_fretLabelColor'             => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Fret Label Color', 'chordpress' ),
				'description' => __( 'Fret label color. Leave empty to use the global color.', 'chordpress' ),
			),
			'select_chord_fretLabelPosition'        => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Fret Label Position', 'chordpress' ),
				'description' => __( 'Position of the fret label.', 'chordpress' ),
			),
			'number_chord_fretLabelFontSize'        => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Fret Label Font Size', 'chordpress' ),
				'description' => __( 'Font size of the fret label.', 'chordpress' ),
			),
			'number_chord_frets'                    => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Frets', 'chordpress' ),
				'description' => __( 'Number of frets to show in the chart.', 'chordpress' ),
			),
			'number_chord_fretSize'                 => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Fret Height', 'chordpress' ),
				'description' => __( 'Height of a fret relative to the string spacing.', 'chordpress' ),
			),
			'number_chord_nutSize'                  => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Nut Size', 'chordpress' ),
				'description' => __( 'Size of a nut relative to the string spacing.', 'chordpress' ),
			),
			'text_chord_nutColor'                   => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Nut Color', 'chordpress' ),
				'description' => __( 'Color of a finger or nut. Leave empty to use the global color.', 'chordpress' ),
			),
			'text_chord_nutStrokeColor'             => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Nut Stroke Color', 'chordpress' ),
				'description' => __( 'Stroke color of a nut. Leave empty to use the nut color.', 'chordpress' ),
			),
			'number_chord_nutStrokeWidth'           => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Nut Stroke Width', 'chordpress' ),
				'description' => __( 'Stroke width of a nut.', 'chordpress' ),
			),
			'text_chord_nutTextColor'               => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Nut Text Color', 'chordpress' ),
				'description' => __( 'Text color inside a nut.', 'chordpress' ),
			),
			'number_chord_nutTextSize'              => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Nut Text Size', 'chordpress' ),
				'description' => __( 'Font size of the nut label.', 'chordpress' ),
			),
			'number_chord_position'                 => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Position', 'chordpress' ),
				'description' => __( 'Default starting fret position. The first fret is 1.', 'chordpress' ),
			),
			'number_chord_sidePadding'              => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Side Padding', 'chordpress' ),
				'description' => __( 'Minimum side padding relative to the whole width.', 'chordpress' ),
			),
			'text_chord_stringColor'                => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'String Color', 'chordpress' ),
				'description' => __( 'String color. Leave empty to use the global color.', 'chordpress' ),
			),
			'select_chord_strings'                  => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Strings', 'chordpress' ),
				'description' => __( 'Number of strings.', 'chordpress' ),
			),
			'number_chord_strokeWidth'              => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Stroke Width', 'chordpress' ),
				'description' => __( 'Global stroke width of the chord diagram.', 'chordpress' ),
			),
			'select_chord_style'                    => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Style', 'chordpress' ),
				'description' => __( 'Select the normal or hand-drawn chord diagram style.', 'chordpress' ),
			),
			'number_chord_titleBottomMargin'        => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Title Bottom Margin', 'chordpress' ),
				'description' => __( 'Space between the title and the chord chart.', 'chordpress' ),
			),
			'text_chord_titleColor'                 => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Title Color', 'chordpress' ),
				'description' => __( 'Title color. Leave empty to use the global color.', 'chordpress' ),
			),
			'number_chord_titleFontSize'            => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Title Font Size', 'chordpress' ),
				'description' => __( 'Initial font size of the title. The title is scaled down if it does not fit.', 'chordpress' ),
			),
			'number_chord_topFretWidth'             => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Top Fret Width', 'chordpress' ),
				'description' => __( 'Width of the nut when the position is 1.', 'chordpress' ),
			),
			'text_chord_tuning'                     => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Tuning', 'chordpress' ),
				'description' => __( 'Tuning labels under the strings, separated by commas.', 'chordpress' ),
			),
			'text_chord_tuningsColor'               => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Tunings Color', 'chordpress' ),
				'description' => __( 'Tuning label color. Leave empty to use the global color.', 'chordpress' ),
			),
			'number_chord_tuningsFontSize'          => array(
				'tab'         => 'tab_chord',
				'label'       => __( 'Tunings Font Size', 'chordpress' ),
				'description' => __( 'Font size of the tuning labels.', 'chordpress' ),
			),
			'checkbox_uninstall_delete_options'     => array(
				'tab'         => 'tab_plugin',
				'label'       => __( 'Remove options on delete', 'chordpress' ),
				'description' => __( 'Remove plugin options, transients and user meta when the plugin is deleted. Deactivation does not remove data.', 'chordpress' ),
			),
			'checkbox_uninstall_delete_chordposts'  => array(
				'tab'         => 'tab_plugin',
				'label'       => __( 'Remove guitar chord posts on delete', 'chordpress' ),
				'description' => __( 'Remove all guitar chord posts when the plugin is deleted. Deactivation does not remove data.', 'chordpress' ),
			),
		);
	}
}
