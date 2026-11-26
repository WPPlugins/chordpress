<?php
/**
 * Chord transposition and harmonica notation.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Frontend;

use ChordPress\Support\Notation;

defined( 'ABSPATH' ) || exit;

/**
 * Formats chords and transposes keys and harmonica notes.
 */
final class Transposer {

	/**
	 * Allowed chord placeholders.
	 *
	 * @var array<int, string>
	 */
	private const STATIC_PREFIXES = array( '~', '|', '/', '.', '-', '+', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );

	/**
	 * Notation source.
	 *
	 * @var Notation
	 */
	private Notation $notation;

	/**
	 * Constructor.
	 *
	 * @param Notation $notation Notation source.
	 */
	public function __construct( Notation $notation ) {
		$this->notation = $notation;
	}

	/**
	 * Format and transpose one chord.
	 *
	 * @param string $chord      Chord text.
	 * @param int    $transpose  Semitone steps.
	 * @param bool   $input_hb   Whether input uses H/B notation.
	 * @param bool   $display_hb Whether output should use H/B notation.
	 * @return string
	 */
	public function format_chord( string $chord, int $transpose, bool $input_hb = false, bool $display_hb = false ): string {
		$chord = trim( $chord );

		if ( '' === $chord ) {
			return '';
		}

		if ( $this->is_static_chord( $chord ) ) {
			return $chord;
		}

		if ( $this->is_named_sequence_chord( $chord ) ) {
			return $chord;
		}

		$note = substr( $chord, 0, 1 );
		$rem  = substr( $chord, 1 );
		$root = $this->note_to_number( $note, $input_hb );

		if ( null === $root ) {
			return $chord;
		}

		$use_flats = false;

		if ( '' !== $rem && '#' === $rem[0] ) {
			++$root;
			$rem = substr( $rem, 1 );
		} elseif ( '' !== $rem && 'b' === strtolower( $rem[0] ) ) {
			$use_flats = true;
			--$root;
			$rem = substr( $rem, 1 );
		}

		$root = Notation::normalize_steps( $root + $transpose );
		$note = $this->number_to_note( $root, $use_flats, $display_hb );

		return $note . $this->format_remainder( $rem, $transpose, $input_hb, $display_hb );
	}

	/**
	 * Transpose the bass note in a chord remainder.
	 *
	 * @param string $remainder Chord remainder.
	 * @param int    $transpose Semitone steps.
	 * @param bool   $input_hb  Whether input uses H/B notation.
	 * @param bool   $display_hb Whether output should use H/B notation.
	 * @return string
	 */
	public function format_remainder( string $remainder, int $transpose, bool $input_hb = false, bool $display_hb = false ): string {
		$slash = strpos( $remainder, '/' );

		if ( false === $slash ) {
			return $remainder;
		}

		$chord = substr( $remainder, 0, $slash );
		$bass  = substr( $remainder, $slash + 1 );

		if ( '' === $bass ) {
			return $chord . '/?';
		}

		$note = substr( $bass, 0, 1 );
		$rem  = substr( $bass, 1 );
		$root = $this->note_to_number( $note, $input_hb );

		if ( null === $root ) {
			return $remainder;
		}

		$use_flats = false;

		if ( '' !== $rem && '#' === $rem[0] ) {
			++$root;
			$rem = substr( $rem, 1 );
		} elseif ( '' !== $rem && 'b' === strtolower( $rem[0] ) ) {
			$use_flats = true;
			--$root;
			$rem = substr( $rem, 1 );
		}

		$root = Notation::normalize_steps( $root + $transpose );

		return $chord . '/' . $this->number_to_note( $root, $use_flats, $display_hb ) . $rem;
	}

	/**
	 * Convert a note name to a semitone number.
	 *
	 * @param string $note     Note name.
	 * @param bool   $input_hb Whether input uses H/B notation.
	 * @return int|null
	 */
	public function note_to_number( string $note, bool $input_hb = false ): ?int {
		$note = strtoupper( $note );

		$regular = array(
			'A' => 0,
			'B' => 2,
			'C' => 3,
			'D' => 5,
			'E' => 7,
			'F' => 8,
			'G' => 10,
		);

		$hb = array(
			'A' => 0,
			'B' => 1,
			'H' => 2,
			'C' => 3,
			'D' => 5,
			'E' => 7,
			'F' => 8,
			'G' => 10,
		);

		$map = $input_hb ? $hb : $regular;

		return $map[ $note ] ?? null;
	}

	/**
	 * Convert a semitone number to a note name.
	 *
	 * @param int  $number     Semitone number.
	 * @param bool $use_flats  Whether to prefer flats.
	 * @param bool $display_hb Whether to use H/B notation.
	 * @return string
	 */
	public function number_to_note( int $number, bool $use_flats = false, bool $display_hb = false ): string {
		$number = Notation::normalize_steps( $number );

		if ( $display_hb ) {
			$hb_notes = array( 'A', 'B', 'H', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#' );

			return $hb_notes[ $number ];
		}

		$sharp = array( 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#' );
		$flat  = array( 'A', 'Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab' );

		return $use_flats ? $flat[ $number ] : $sharp[ $number ];
	}

	/**
	 * Whether a chord token is a static placeholder.
	 *
	 * @param string $chord Chord token.
	 * @return bool
	 */
	public function is_static_chord( string $chord ): bool {
		return '' !== $chord && in_array( $chord[0], self::STATIC_PREFIXES, true );
	}

	/**
	 * Return transposed key names for all twelve semitones.
	 *
	 * @param string $key Original key.
	 * @return array<int, string>
	 */
	public function key_variants( string $key ): array {
		$values = $this->notation->transpose_values()[ $key ] ?? array();

		return array_values( $values );
	}

	/**
	 * Transpose a chromatic harmonica note.
	 *
	 * @param string $note  Note.
	 * @param int    $steps Steps.
	 * @return string
	 */
	public function transpose_chromatic_note( string $note, int $steps ): string {
		return $this->notation->transpose_chromatic( $note, Notation::normalize_steps( $steps ) );
	}

	/**
	 * Translate a harmonica note between types.
	 *
	 * @param string $note Note.
	 * @param string $from Source type.
	 * @param string $to   Target type.
	 * @return string
	 */
	public function translate_harmonica_note( string $note, string $from, string $to ): string {
		return $this->notation->translate_harmonica_note( $note, $from, $to );
	}

	/**
	 * Return the supported harmonica types.
	 *
	 * @return array<int, string>
	 */
	public function harmonica_types(): array {
		return $this->notation->harmonica_types();
	}

	/**
	 * Whether a chord uses a named sequence that is not transposed.
	 *
	 * @param string $chord Chord token.
	 * @return bool
	 */
	private function is_named_sequence_chord( string $chord ): bool {
		$italian   = array( 'Do', 'do', 'Re', 're', 'Mi', 'mi', 'Fa', 'fa', 'Sol', 'sol', 'La', 'la', 'Si', 'si' );
		$nashville = array( '1', '2', '3', '4', '5', '6', '7' );
		$roman     = array( 'I', 'I7', 'II', 'III', 'iii', 'IV', 'V', 'V7', 'VI', 'vi', 'VI7', 'vi7', 'VII' );

		return in_array( $chord, $italian, true ) || in_array( $chord, $nashville, true ) || in_array( $chord, $roman, true );
	}
}
