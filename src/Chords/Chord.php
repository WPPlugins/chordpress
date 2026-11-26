<?php
/**
 * Chord value object and input normalization.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Chords;

use InvalidArgumentException;
use JsonException;

defined( 'ABSPATH' ) || exit;

/**
 * Immutable, validated chord definition.
 */
final class Chord {

	public const MAX_NAME_LENGTH = 64;
	public const MIN_STRINGS     = 4;
	public const MAX_STRINGS     = 12;
	public const MIN_FRETS       = 1;
	public const MAX_FRETS       = 24;
	public const MIN_POSITION    = 1;
	public const MAX_POSITION    = 24;
	public const MAX_FRET        = 24;

	/**
	 * Barre definitions.
	 *
	 * @var array<int, array{fromString:int,toString:int,fret:int}>
	 */
	public readonly array $barres;

	/**
	 * Finger definitions.
	 *
	 * @var array<int, array{string:int,fret:int|string,text:string}>
	 */
	public readonly array $fingers;

	/**
	 * Number of frets to display.
	 *
	 * @var int
	 */
	public readonly int $frets;

	/**
	 * Starting fret position.
	 *
	 * @var int
	 */
	public readonly int $position;

	/**
	 * Number of strings.
	 *
	 * @var int
	 */
	public readonly int $strings;

	/**
	 * String tuning labels.
	 *
	 * @var array<int, string>
	 */
	public readonly array $tuning;

	/**
	 * Build a chord from normalized values.
	 *
	 * @param string                                $name     Chord name.
	 * @param array<int, array<string, int>>        $barres   Barre definitions.
	 * @param array<int, array<string, int|string>> $fingers Finger definitions.
	 * @param int                                   $frets    Number of frets.
	 * @param int                                   $position Starting fret position.
	 * @param int                                   $strings  Number of strings.
	 * @param array<int, string>                    $tuning   Tuning labels.
	 */
	private function __construct(
		public readonly string $name,
		array $barres,
		array $fingers,
		int $frets,
		int $position,
		int $strings,
		array $tuning
	) {
		$this->barres   = $barres;
		$this->fingers  = $fingers;
		$this->frets    = $frets;
		$this->position = $position;
		$this->strings  = $strings;
		$this->tuning   = $tuning;
	}

	/**
	 * Create a chord from legacy or structured input.
	 *
	 * @param array<string, mixed> $data Chord input.
	 * @return self
	 * @throws InvalidArgumentException When the chord data is invalid.
	 */
	public static function from_array( array $data ): self {
		$name = self::require_string(
			$data['name'] ?? $data['chord_name'] ?? '',
			'name',
			self::MAX_NAME_LENGTH
		);

		$strings = self::to_int(
			$data['strings'] ?? $data['chord_strings'] ?? 6,
			'strings',
			self::MIN_STRINGS,
			self::MAX_STRINGS
		);

		$frets = self::to_int(
			$data['frets'] ?? $data['chord_frets'] ?? 4,
			'frets',
			self::MIN_FRETS,
			self::MAX_FRETS
		);

		$position = self::to_int(
			$data['position'] ?? $data['chord_position'] ?? 1,
			'position',
			self::MIN_POSITION,
			self::MAX_POSITION
		);

		$barres  = self::parse_barres( $data['barres'] ?? $data['chord_barres'] ?? array(), $strings );
		$fingers = self::parse_fingers( $data['fingers'] ?? $data['chord_fingers'] ?? array(), $strings );
		$tuning  = self::parse_tuning( $data['tuning'] ?? $data['chord_tuning'] ?? 'E,A,D,G,B,E' );

		return new self( $name, $barres, $fingers, $frets, $position, $strings, $tuning );
	}

	/**
	 * Create a chord from stored JSON.
	 *
	 * @param string $json JSON data.
	 * @return self
	 * @throws InvalidArgumentException When the JSON or chord data is invalid.
	 */
	public static function from_json( string $json ): self {
		try {
			$data = json_decode( $json, true, 512, JSON_THROW_ON_ERROR );
		} catch ( JsonException $exception ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
			throw new InvalidArgumentException( 'Invalid chord JSON: ' . $exception->getMessage(), 0, $exception );
		}

		if ( ! is_array( $data ) ) {
			throw new InvalidArgumentException( 'Chord JSON must decode to an object.' );
		}

		return self::from_array( $data );
	}

	/**
	 * Normalize a chord name for indexed lookup.
	 *
	 * @param string $name Chord name.
	 * @return string
	 * @throws InvalidArgumentException When the name is empty.
	 */
	public static function normalize_name( string $name ): string {
		$name = self::clean_string( $name );
		$name = preg_replace( '/\s+/', ' ', $name );
		$name = is_string( $name ) ? trim( $name ) : '';

		if ( '' === $name ) {
			throw new InvalidArgumentException( 'Chord name is empty.' );
		}

		return strtoupper( $name );
	}

	/**
	 * Return the normalized lookup key.
	 *
	 * @return string
	 */
	public function lookup_key(): string {
		return self::normalize_name( $this->name );
	}

	/**
	 * Convert the chord to a structured array.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return array(
			'name'     => $this->name,
			'barres'   => $this->barres,
			'fingers'  => $this->fingers,
			'frets'    => $this->frets,
			'position' => $this->position,
			'strings'  => $this->strings,
			'tuning'   => $this->tuning,
		);
	}

	/**
	 * Convert the chord to the SVGuitar preview shape.
	 *
	 * @return array<string, mixed>
	 */
	public function to_preview_array(): array {
		$fingers = array();

		foreach ( $this->fingers as $finger ) {
			$preview_finger = array( $finger['string'], $finger['fret'] );

			if ( '' !== $finger['text'] ) {
				$preview_finger[] = $finger['text'];
			}

			$fingers[] = $preview_finger;
		}

		return array(
			'title'    => $this->name,
			'fingers'  => $fingers,
			'barres'   => array_values( $this->barres ),
			'position' => $this->position,
			'tuning'   => $this->tuning,
			'strings'  => $this->strings,
			'frets'    => $this->frets,
		);
	}

	/**
	 * Serialize the chord to JSON.
	 *
	 * @return string
	 * @throws JsonException When encoding fails.
	 */
	public function to_json(): string {
		$json = wp_json_encode(
			$this->to_array(),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
		);

		if ( ! is_string( $json ) ) {
			throw new JsonException( 'Could not encode chord JSON.' );
		}

		return $json;
	}

	/**
	 * Parse barre input.
	 *
	 * @param mixed $value   Barre input.
	 * @param int   $strings Number of strings.
	 * @return array<int, array{fromString:int,toString:int,fret:int}>
	 * @throws InvalidArgumentException When a barre is invalid.
	 */
	private static function parse_barres( mixed $value, int $strings ): array {
		if ( is_string( $value ) ) {
			$value = trim( $value );

			if ( '' === $value ) {
				return array();
			}

			if ( str_starts_with( $value, '[' ) || str_starts_with( $value, '{' ) ) {
				$decoded = json_decode( $value, true );

				if ( is_array( $decoded ) ) {
					return self::parse_barres( $decoded, $strings );
				}
			}

			if ( preg_match( '/[;\r\n]/', $value ) ) {
				$barres   = array();
				$segments = preg_split( '/[;\r\n]+/', $value );

				if ( ! is_array( $segments ) ) {
					return array();
				}

				foreach ( $segments as $segment ) {
					$segment = trim( $segment );

					if ( '' === $segment ) {
						continue;
					}

					$barres = array_merge( $barres, self::parse_barres( $segment, $strings ) );
				}

				return $barres;
			}

			if ( preg_match( '/fromString\s*[:=]\s*(\d+)/i', $value, $from_match )
				&& preg_match( '/toString\s*[:=]\s*(\d+)/i', $value, $to_match )
				&& preg_match( '/fret\s*[:=]\s*(\d+)/i', $value, $fret_match )
			) {
				return array(
					self::normalize_barre(
						(int) $from_match[1],
						(int) $to_match[1],
						(int) $fret_match[1],
						$strings
					),
				);
			}

			$parts = array_map( 'trim', explode( ',', $value ) );

			if ( count( $parts ) < 3 ) {
				throw new InvalidArgumentException( 'Barre input must contain fromString, toString and fret values.' );
			}

			return array(
				self::normalize_barre(
					self::to_int( $parts[0], 'barre fromString', 1, $strings ),
					self::to_int( $parts[1], 'barre toString', 1, $strings ),
					self::to_int( $parts[2], 'barre fret', 0, self::MAX_FRET ),
					$strings
				),
			);
		}

		if ( ! is_array( $value ) ) {
			throw new InvalidArgumentException( 'Barre input must be a string or an array.' );
		}

		if ( self::is_single_barre( $value ) ) {
			$value = array( $value );
		}

		$barres = array();

		foreach ( $value as $item ) {
			if ( is_string( $item ) ) {
				$barres = array_merge( $barres, self::parse_barres( $item, $strings ) );
				continue;
			}

			if ( ! is_array( $item ) ) {
				throw new InvalidArgumentException( 'Each barre must be a string or an array.' );
			}

			if ( array_is_list( $item ) ) {
				if ( count( $item ) < 3 ) {
					throw new InvalidArgumentException( 'Each barre array must contain fromString, toString and fret.' );
				}

				$barres[] = self::normalize_barre(
					self::to_int( $item[0], 'barre fromString', 1, $strings ),
					self::to_int( $item[1], 'barre toString', 1, $strings ),
					self::to_int( $item[2], 'barre fret', 0, self::MAX_FRET ),
					$strings
				);
				continue;
			}

			$barres[] = self::normalize_barre(
				self::to_int( $item['fromString'] ?? $item['from'] ?? null, 'barre fromString', 1, $strings ),
				self::to_int( $item['toString'] ?? $item['to'] ?? null, 'barre toString', 1, $strings ),
				self::to_int( $item['fret'] ?? null, 'barre fret', 0, self::MAX_FRET ),
				$strings
			);
		}

		return $barres;
	}

	/**
	 * Normalize one barre.
	 *
	 * @param int $from_string Starting string.
	 * @param int $to_string   Ending string.
	 * @param int $fret        Fret number.
	 * @param int $strings     Number of strings.
	 * @return array{fromString:int,toString:int,fret:int}
	 * @throws InvalidArgumentException When the barre is invalid.
	 */
	private static function normalize_barre( int $from_string, int $to_string, int $fret, int $strings ): array {
		if ( $from_string < 1 || $from_string > $strings || $to_string < 1 || $to_string > $strings ) {
			throw new InvalidArgumentException( 'Barre strings must be within the chord string range.' );
		}

		if ( $fret < 0 || $fret > self::MAX_FRET ) {
			throw new InvalidArgumentException( 'Barre fret is outside the allowed range.' );
		}

		return array(
			'fromString' => $from_string,
			'toString'   => $to_string,
			'fret'       => $fret,
		);
	}

	/**
	 * Determine whether an array is a single barre.
	 *
	 * @param array<mixed> $value Barre input.
	 * @return bool
	 */
	private static function is_single_barre( array $value ): bool {
		if ( array_is_list( $value ) ) {
			return count( $value ) >= 3 && ! is_array( $value[0] );
		}

		return isset( $value['fromString'] ) || isset( $value['from'] );
	}

	/**
	 * Parse finger input.
	 *
	 * @param mixed $value   Finger input.
	 * @param int   $strings Number of strings.
	 * @return array<int, array{string:int,fret:int|string,text:string}>
	 * @throws InvalidArgumentException When a finger is invalid.
	 */
	private static function parse_fingers( mixed $value, int $strings ): array {
		if ( is_string( $value ) ) {
			$value = trim( $value );

			if ( '' === $value ) {
				return array();
			}

			if ( str_starts_with( $value, '[' ) || str_starts_with( $value, '{' ) ) {
				$decoded = json_decode( $value, true );

				if ( is_array( $decoded ) ) {
					return self::parse_fingers( $decoded, $strings );
				}
			}

			$pattern = "~\(\s*(\d+)\s*,\s*(?:'([^']*)'|([^,)]+))(?:\s*,\s*'([^']*)')?\s*\)~";
			$matches = array();

			if ( ! preg_match_all( $pattern, $value, $matches, PREG_SET_ORDER ) ) {
				throw new InvalidArgumentException( 'Finger input does not contain valid chord tuples.' );
			}

			$leftover = preg_replace( $pattern, '', $value );
			$leftover = is_string( $leftover ) ? preg_replace( '/[\s,]+/', '', $leftover ) : '';

			if ( '' !== $leftover ) {
				throw new InvalidArgumentException( 'Finger input contains unparsed data.' );
			}

			$fingers = array();

			foreach ( $matches as $match ) {
				$raw_fret = '' !== ( $match[2] ?? '' ) ? $match[2] : ( $match[3] ?? '' );
				$fret     = 'x' === strtolower( trim( $raw_fret ) )
					? 'x'
					: self::to_int( $raw_fret, 'finger fret', 0, self::MAX_FRET );

				$fingers[] = self::normalize_finger(
					self::to_int( $match[1], 'finger string', 1, $strings ),
					$fret,
					$match[4] ?? '',
					$strings
				);
			}

			return $fingers;
		}

		if ( ! is_array( $value ) ) {
			throw new InvalidArgumentException( 'Finger input must be a string or an array.' );
		}

		if ( self::is_single_finger( $value ) ) {
			$value = array( $value );
		}

		$fingers = array();

		foreach ( $value as $item ) {
			if ( is_string( $item ) ) {
				$fingers = array_merge( $fingers, self::parse_fingers( $item, $strings ) );
				continue;
			}

			if ( ! is_array( $item ) ) {
				throw new InvalidArgumentException( 'Each finger must be a string or an array.' );
			}

			if ( array_is_list( $item ) ) {
				if ( count( $item ) < 2 ) {
					throw new InvalidArgumentException( 'Each finger array must contain a string and a fret.' );
				}

				$fingers[] = self::normalize_finger(
					self::to_int( $item[0], 'finger string', 1, $strings ),
					self::normalize_fret( $item[1] ),
					$item[2] ?? '',
					$strings
				);
				continue;
			}

			$fingers[] = self::normalize_finger(
				self::to_int( $item['string'] ?? null, 'finger string', 1, $strings ),
				self::normalize_fret( $item['fret'] ?? null ),
				$item['text'] ?? '',
				$strings
			);
		}

		return $fingers;
	}

	/**
	 * Normalize a finger fret.
	 *
	 * @param mixed $value Fret value.
	 * @return int|string
	 * @throws InvalidArgumentException When the fret is invalid.
	 */
	private static function normalize_fret( mixed $value ): int|string {
		if ( is_string( $value ) && 'x' === strtolower( trim( $value ) ) ) {
			return 'x';
		}

		return self::to_int( $value, 'finger fret', 0, self::MAX_FRET );
	}

	/**
	 * Normalize one finger.
	 *
	 * @param int        $string_number String number.
	 * @param int|string $fret          Fret number or x.
	 * @param mixed      $text          Finger text.
	 * @param int        $strings       Number of strings.
	 * @return array{string:int,fret:int|string,text:string}
	 * @throws InvalidArgumentException When the finger is invalid.
	 */
	private static function normalize_finger( int $string_number, int|string $fret, mixed $text, int $strings ): array {
		if ( $string_number < 1 || $string_number > $strings ) {
			throw new InvalidArgumentException( 'Finger string must be within the chord string range.' );
		}

		$text = is_scalar( $text ) ? self::clean_string( (string) $text ) : '';

		if ( strlen( $text ) > 16 ) {
			throw new InvalidArgumentException( 'Finger text is too long.' );
		}

		return array(
			'string' => $string_number,
			'fret'   => $fret,
			'text'   => $text,
		);
	}

	/**
	 * Determine whether an array is a single finger.
	 *
	 * @param array<mixed> $value Finger input.
	 * @return bool
	 */
	private static function is_single_finger( array $value ): bool {
		if ( array_is_list( $value ) ) {
			return count( $value ) >= 2 && ! is_array( $value[0] );
		}

		return isset( $value['string'] );
	}

	/**
	 * Parse tuning input.
	 *
	 * @param mixed $value Tuning input.
	 * @return array<int, string>
	 * @throws InvalidArgumentException When the tuning is invalid.
	 */
	private static function parse_tuning( mixed $value ): array {
		$default = array( 'E', 'A', 'D', 'G', 'B', 'E' );

		if ( null === $value || '' === $value || array() === $value ) {
			return $default;
		}

		if ( is_string( $value ) ) {
			$value = trim( $value );

			if ( str_starts_with( $value, '[' ) && str_ends_with( $value, ']' ) ) {
				$value = substr( $value, 1, -1 );
			}

			$value = explode( ',', $value );
		}

		if ( ! is_array( $value ) ) {
			throw new InvalidArgumentException( 'Tuning must be a string or an array.' );
		}

		$tuning = array();

		foreach ( $value as $item ) {
			if ( ! is_scalar( $item ) ) {
				throw new InvalidArgumentException( 'Each tuning value must be a string.' );
			}

			$item = trim( self::clean_string( (string) $item ) );

			if ( strlen( $item ) >= 2 ) {
				$first = substr( $item, 0, 1 );
				$last  = substr( $item, -1 );

				if ( ( "'" === $first && "'" === $last ) || ( '"' === $first && '"' === $last ) ) {
					$item = substr( $item, 1, -1 );
				}
			}

			if ( '' === $item ) {
				continue;
			}

			if ( strlen( $item ) > 8 ) {
				throw new InvalidArgumentException( 'A tuning label is too long.' );
			}

			$tuning[] = $item;
		}

		if ( array() === $tuning ) {
			return $default;
		}

		if ( count( $tuning ) > 12 ) {
			throw new InvalidArgumentException( 'A chord cannot have more than 12 tuning labels.' );
		}

		return $tuning;
	}

	/**
	 * Require a non-empty scalar string.
	 *
	 * @param mixed  $value   Input value.
	 * @param string $field   Field name.
	 * @param int    $maximum Maximum length.
	 * @return string
	 * @throws InvalidArgumentException When the value is invalid.
	 */
	private static function require_string( mixed $value, string $field, int $maximum ): string {
		if ( ! is_scalar( $value ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
			throw new InvalidArgumentException( sprintf( 'Chord %s must be a string.', $field ) );
		}

		$value = trim( self::clean_string( (string) $value ) );

		if ( '' === $value ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
			throw new InvalidArgumentException( sprintf( 'Chord %s is required.', $field ) );
		}

		if ( strlen( $value ) > $maximum ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
			throw new InvalidArgumentException( sprintf( 'Chord %s is too long.', $field ) );
		}

		return $value;
	}

	/**
	 * Convert a scalar to an integer within a range.
	 *
	 * @param mixed  $value   Input value.
	 * @param string $field   Field name.
	 * @param int    $minimum Minimum value.
	 * @param int    $maximum Maximum value.
	 * @return int
	 * @throws InvalidArgumentException When the value is invalid.
	 */
	private static function to_int( mixed $value, string $field, int $minimum, int $maximum ): int {
		if ( ! is_numeric( $value ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
			throw new InvalidArgumentException( sprintf( 'Chord %s must be numeric.', $field ) );
		}

		$number = (int) $value;

		if ( $number < $minimum || $number > $maximum ) {
			$message = sprintf( 'Chord %s must be between %d and %d.', $field, $minimum, $maximum );
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
			throw new InvalidArgumentException( $message );
		}

		return $number;
	}

	/**
	 * Remove control characters.
	 *
	 * @param string $value Input value.
	 * @return string
	 */
	private static function clean_string( string $value ): string {
		$clean = preg_replace( '/<[^>]*>/', '', $value );
		$clean = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', is_string( $clean ) ? $clean : $value );

		return is_string( $clean ) ? $clean : '';
	}
}
