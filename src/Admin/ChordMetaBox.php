<?php
/**
 * Guitar chord meta box.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Admin;

use ChordPress\Chords\Chord;
use ChordPress\Chords\ChordRepository;
use InvalidArgumentException;
use JsonException;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and saves validated chord data.
 */
final class ChordMetaBox {

	public const NONCE_ACTION = 'chordpress_chord_meta_box';
	public const NONCE_NAME   = 'chordpress_chord_nonce';
	public const FIELD        = 'chordpress_chord';
	public const ERROR_PREFIX = 'chordpress_chord_error_';

	/**
	 * Register the meta box.
	 *
	 * @return void
	 */
	public function register(): void {
		add_meta_box(
			'chordpress_chord_details',
			__( 'Guitar Chord Details', 'chordpress' ),
			array( $this, 'render' ),
			ChordRepository::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public function render( WP_Post $post ): void {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		$chord = $this->chord_for_post( $post->ID );

		$name     = $chord instanceof Chord ? $chord->name : $post->post_title;
		$barres   = $chord instanceof Chord ? self::barres_to_string( $chord->barres ) : '';
		$fingers  = $chord instanceof Chord ? self::fingers_to_string( $chord->fingers ) : '';
		$frets    = $chord instanceof Chord ? $chord->frets : 4;
		$position = $chord instanceof Chord ? $chord->position : 1;
		$strings  = $chord instanceof Chord ? $chord->strings : 6;
		$tuning   = $chord instanceof Chord ? implode( ',', $chord->tuning ) : 'E,A,D,G,B,E';

		$preview = $chord instanceof Chord
			? self::preview_chord( $chord )
			: array(
				'title'    => '' !== $name ? $name : __( 'Chord', 'chordpress' ),
				'fingers'  => array(),
				'barres'   => array(),
				'position' => 1,
				'tuning'   => array( 'E', 'A', 'D', 'G', 'B', 'E' ),
				'strings'  => 6,
				'frets'    => 4,
			);

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<table class="form-table chordpress-chord-table" role="presentation"><tbody>';

		$this->render_text_row(
			'name',
			__( 'Name', 'chordpress' ),
			(string) $name,
			__( 'Short chord name as used in ChordPro, for example Em.', 'chordpress' )
		);

		echo '<tr><th scope="row"><label for="chordpress-chord-position">' . esc_html__( 'Base Fret', 'chordpress' ) . '</label></th><td>';
		echo '<select id="chordpress-chord-position" name="' . esc_attr( self::FIELD ) . '[position]">';

		for ( $fret = 1; $fret <= 24; $fret++ ) {
			echo '<option value="' . esc_attr( (string) $fret ) . '"';
			selected( $position, $fret );
			echo '>' . esc_html( sprintf( /* translators: %d: fret number. */ __( '%d. fret', 'chordpress' ), $fret ) ) . '</option>';
		}

		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Fret at which the diagram starts.', 'chordpress' ) . '</p>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="chordpress-chord-frets">' . esc_html__( 'Number of Frets', 'chordpress' ) . '</label></th><td>';
		echo '<input type="number" id="chordpress-chord-frets" name="' . esc_attr( self::FIELD ) . '[frets]" value="' . esc_attr( (string) $frets ) . '" min="1" max="24" step="1">';
		echo '<p class="description">' . esc_html__( 'Number of frets shown in the chart.', 'chordpress' ) . '</p>';
		echo '</td></tr>';

		echo '<tr><th scope="row"><label for="chordpress-chord-strings">' . esc_html__( 'Number of Strings', 'chordpress' ) . '</label></th><td>';
		echo '<select id="chordpress-chord-strings" name="' . esc_attr( self::FIELD ) . '[strings]">';

		for ( $count = 4; $count <= 12; $count++ ) {
			echo '<option value="' . esc_attr( (string) $count ) . '"';
			selected( $strings, $count );
			echo '>' . esc_html( (string) $count ) . '</option>';
		}

		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Number of strings on the instrument.', 'chordpress' ) . '</p>';
		echo '</td></tr>';

		$this->render_text_row(
			'tuning',
			__( 'Tuning', 'chordpress' ),
			$tuning,
			__( 'Tuning labels separated by commas, for example E,A,D,G,B,E.', 'chordpress' )
		);

		$this->render_text_row(
			'fingers',
			__( 'Fingers', 'chordpress' ),
			$fingers,
			__( 'Triplets in the form (string,fret,\'text\'), separated by commas. Use \'x\' for a muted string.', 'chordpress' )
		);

		$this->render_text_row(
			'barres',
			__( 'Barres', 'chordpress' ),
			$barres,
			__( 'Barres in the form fromString,toString,fret. Separate multiple barres with a semicolon.', 'chordpress' )
		);

		echo '<tr><th scope="row">' . esc_html__( 'Preview', 'chordpress' ) . '</th><td>';
		echo '<div class="chordpress-preview" data-chordpress-preview="chord" data-chord="' . esc_attr( (string) wp_json_encode( $preview ) ) . '" aria-live="polite"></div>';
		echo '<p class="description">' . esc_html__( 'The preview updates as you edit the chord and uses the global chord diagram settings.', 'chordpress' ) . '</p>';
		echo '</td></tr>';

		$shortcode = '[chordpress-chord chord="' . $post->ID . '"]';
		echo '<tr><th scope="row">' . esc_html__( 'Shortcode', 'chordpress' ) . '</th><td>';
		echo '<div class="chordpress-copy-row">';
		echo '<input type="text" readonly id="chordpress-chord-shortcode" value="' . esc_attr( $shortcode ) . '" class="regular-text code">';
		echo '<button type="button" class="button button-secondary" data-chordpress-copy="chordpress-chord-shortcode">' . esc_html__( 'Copy shortcode', 'chordpress' ) . '</button>';
		echo '<span class="chordpress-copy-status" aria-live="polite"></span>';
		echo '</div>';
		echo '<p class="description">' . esc_html__( 'Use this shortcode to display this chord in a post or page.', 'chordpress' ) . '</p>';
		echo '</td></tr>';

		echo '</tbody></table>';
	}

	/**
	 * Save validated chord data.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function save( int $post_id, WP_Post $post ): void {
		if ( ChordRepository::POST_TYPE !== $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( (string) $_POST[ self::NONCE_NAME ] ) );

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		$raw = array();

		if ( isset( $_POST[ self::FIELD ] ) && is_array( $_POST[ self::FIELD ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every field is sanitized individually below.
			$raw = wp_unslash( $_POST[ self::FIELD ] );
		}

		$data = array();

		foreach ( array( 'name', 'barres', 'fingers', 'frets', 'position', 'strings', 'tuning' ) as $key ) {
			$value        = isset( $raw[ $key ] ) && is_scalar( $raw[ $key ] ) ? (string) $raw[ $key ] : '';
			$data[ $key ] = sanitize_text_field( $value );
		}

		try {
			$chord = Chord::from_array( $data );
			update_post_meta( $post_id, ChordRepository::META_NAME, $chord->lookup_key() );
			update_post_meta( $post_id, ChordRepository::META_DATA, $chord->to_json() );
			delete_transient( self::error_key( $post_id ) );
		} catch ( InvalidArgumentException | JsonException $exception ) {
			set_transient( self::error_key( $post_id ), $exception->getMessage(), MINUTE_IN_SECONDS );
		}
	}

	/**
	 * Return the transient key for a chord validation error.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function error_key( int $post_id ): string {
		return self::ERROR_PREFIX . $post_id;
	}

	/**
	 * Convert barres to the editable input format.
	 *
	 * @param array<int, array{fromString:int,toString:int,fret:int}> $barres Barres.
	 * @return string
	 */
	public static function barres_to_string( array $barres ): string {
		$parts = array();

		foreach ( $barres as $barre ) {
			$parts[] = $barre['fromString'] . ',' . $barre['toString'] . ',' . $barre['fret'];
		}

		return implode( ';', $parts );
	}

	/**
	 * Convert fingers to the editable input format.
	 *
	 * @param array<int, array{string:int,fret:int|string,text:string}> $fingers Fingers.
	 * @return string
	 */
	public static function fingers_to_string( array $fingers ): string {
		$parts = array();

		foreach ( $fingers as $finger ) {
			$fret       = $finger['fret'];
			$fret_value = is_int( $fret ) ? (string) $fret : "'" . $fret . "'";
			$text       = str_replace( "'", '', (string) $finger['text'] );

			$parts[] = '(' . $finger['string'] . ',' . $fret_value . ( '' !== $text ? ",'" . $text . "'" : '' ) . ')';
		}

		return implode( ',', $parts );
	}

	/**
	 * Convert a chord to the SVGuitar preview shape.
	 *
	 * @param Chord $chord Chord.
	 * @return array<string, mixed>
	 */
	public static function preview_chord( Chord $chord ): array {
		return $chord->to_preview_array();
	}

	/**
	 * Read and validate a chord post's structured data.
	 *
	 * @param int $post_id Post ID.
	 * @return Chord|null
	 */
	private function chord_for_post( int $post_id ): ?Chord {
		$json = get_post_meta( $post_id, ChordRepository::META_DATA, true );

		if ( ! is_string( $json ) || '' === $json ) {
			return null;
		}

		try {
			return Chord::from_json( $json );
		} catch ( InvalidArgumentException ) {
			return null;
		}
	}

	/**
	 * Render a text input row.
	 *
	 * @param string $key         Field key.
	 * @param string $label       Field label.
	 * @param string $value       Current value.
	 * @param string $description Field description.
	 * @return void
	 */
	private function render_text_row( string $key, string $label, string $value, string $description ): void {
		$id = 'chordpress-chord-' . $key;

		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
		echo '<input type="text" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( self::FIELD ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '">';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
		echo '</td></tr>';
	}
}
