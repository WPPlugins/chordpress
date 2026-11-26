<?php
/**
 * Shared notation data loader and transposition helpers.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Support;

use JsonException;
use RuntimeException;

defined( 'ABSPATH' ) || exit;

/**
 * Loads the single notation source used by PHP and bundled JavaScript.
 */
final class Notation {

	/**
	 * Decoded notation data.
	 *
	 * @var array<string, mixed>
	 */
	private array $data;

	/**
	 * Load the notation source.
	 *
	 * @param string|null $file Optional notation file path.
	 * @throws RuntimeException When the notation source cannot be read or decoded.
	 */
	public function __construct( ?string $file = null ) {
		$path = $file ?? dirname( __DIR__, 2 ) . '/resources/notation.json';

		if ( ! is_readable( $path ) ) {
			throw new RuntimeException( 'Notation source file is missing.' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading a local bundled data file.
		$json = file_get_contents( $path );

		if ( false === $json ) {
			throw new RuntimeException( 'Notation source file could not be read.' );
		}

		try {
			$decoded = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
		} catch ( JsonException $exception ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
			throw new RuntimeException( 'Notation source is not valid JSON.', 0, $exception );
		}

		if ( ! is_array( $decoded ) ) {
			throw new RuntimeException( 'Notation source must decode to an object.' );
		}

		$this->data = $decoded;
	}

	/**
	 * Return the supported key names.
	 *
	 * @return array<int, string>
	 */
	public function keys(): array {
		return $this->string_list( 'keys' );
	}

	/**
	 * Return the key transposition table.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function transpose_values(): array {
		return $this->map_of_string_lists( 'transposeValues' );
	}

	/**
	 * Return the chromatic harmonica transposition table.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function chromatic_transpose(): array {
		return $this->map_of_string_lists( 'chromaticTranspose' );
	}

	/**
	 * Return the harmonica note translation table.
	 *
	 * @return array<int, array{tremolo:string,diatonic:string,chromatic:string,note:string}>
	 */
	public function harmonica_notes(): array {
		$value = $this->data['harmonicaNotes'] ?? array();

		if ( ! is_array( $value ) ) {
			return array();
		}

		$notes = array();

		foreach ( $value as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$notes[] = array(
				'tremolo'   => (string) ( $item['tremolo'] ?? '' ),
				'diatonic'  => (string) ( $item['diatonic'] ?? '' ),
				'chromatic' => (string) ( $item['chromatic'] ?? '' ),
				'note'      => (string) ( $item['note'] ?? '' ),
			);
		}

		return $notes;
	}

	/**
	 * Return the supported harmonica types.
	 *
	 * @return array<int, string>
	 */
	public function harmonica_types(): array {
		return $this->string_list( 'harmonicaTypes' );
	}

	/**
	 * Return all transposed names for a key.
	 *
	 * @param string $key Base key.
	 * @return array<int, string>
	 */
	public function key_variants( string $key ): array {
		$values = $this->transpose_values()[ $key ] ?? null;

		return is_array( $values ) ? array_values( $values ) : array();
	}

	/**
	 * Normalize semitone steps to 0-11.
	 *
	 * @param int $steps Steps.
	 * @return int
	 */
	public static function normalize_steps( int $steps ): int {
		return ( ( $steps % 12 ) + 12 ) % 12;
	}

	/**
	 * Transpose a key by semitones.
	 *
	 * @param string $key   Base key.
	 * @param int    $steps Semitone steps.
	 * @return string|null
	 */
	public function transpose( string $key, int $steps ): ?string {
		$steps  = self::normalize_steps( $steps );
		$values = $this->transpose_values()[ $key ] ?? null;

		if ( ! is_array( $values ) ) {
			return null;
		}

		return $values[ $steps ] ?? null;
	}

	/**
	 * Transpose a chromatic harmonica note.
	 *
	 * @param string $note  Note.
	 * @param int    $steps Semitone steps.
	 * @return string
	 */
	public function transpose_chromatic( string $note, int $steps ): string {
		if ( $steps < 0 || $steps > 11 ) {
			return $note;
		}

		$values = $this->chromatic_transpose()[ $note ] ?? null;

		if ( ! is_array( $values ) ) {
			return $note;
		}

		return $values[ $steps ] ?? $note;
	}

	/**
	 * Translate a harmonica note between tremolo, diatonic and chromatic notation.
	 *
	 * @param string $note Source note.
	 * @param string $from Source harmonica type.
	 * @param string $to   Target harmonica type.
	 * @return string
	 */
	public function translate_harmonica_note( string $note, string $from, string $to ): string {
		$from = strtolower( $from );
		$to   = strtolower( $to );
		$note = strtolower( $note );

		if ( $from === $to ) {
			return $note;
		}

		$types = $this->harmonica_types();

		if ( ! in_array( $from, $types, true ) || ! in_array( $to, $types, true ) ) {
			return '-';
		}

		foreach ( $this->harmonica_notes() as $item ) {
			$source = match ( $from ) {
				'tremolo'   => $item['tremolo'],
				'diatonic'  => $item['diatonic'],
				'chromatic' => $item['chromatic'],
				default     => null,
			};

			if ( $source !== $note ) {
				continue;
			}

			return match ( $to ) {
				'tremolo'   => $item['tremolo'],
				'diatonic'  => $item['diatonic'],
				'chromatic' => $item['chromatic'],
				default     => '-',
			};
		}

		return '-';
	}

	/**
	 * Read a list of strings from the notation data.
	 *
	 * @param string $key Data key.
	 * @return array<int, string>
	 */
	private function string_list( string $key ): array {
		$value = $this->data[ $key ] ?? array();

		if ( ! is_array( $value ) ) {
			return array();
		}

		$result = array();

		foreach ( $value as $item ) {
			if ( is_scalar( $item ) ) {
				$result[] = (string) $item;
			}
		}

		return $result;
	}

	/**
	 * Read a map of string lists from the notation data.
	 *
	 * @param string $key Data key.
	 * @return array<string, array<int, string>>
	 */
	private function map_of_string_lists( string $key ): array {
		$value = $this->data[ $key ] ?? array();

		if ( ! is_array( $value ) ) {
			return array();
		}

		$result = array();

		foreach ( $value as $map_key => $items ) {
			if ( ! is_array( $items ) ) {
				continue;
			}

			$list = array();

			foreach ( $items as $item ) {
				if ( is_scalar( $item ) ) {
					$list[] = (string) $item;
				}
			}

			$result[ (string) $map_key ] = $list;
		}

		return $result;
	}
}
