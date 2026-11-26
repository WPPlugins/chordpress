<?php
/**
 * Seed chord data loader.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Chords;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

/**
 * Loads the versioned seed chord definitions.
 */
final class SeedData {

	/**
	 * Cached seed rows.
	 *
	 * @var array<int, array<string, mixed>>|null
	 */
	private static ?array $cache = null;

	/**
	 * Return all seed chord definitions.
	 *
	 * @return array<int, array<string, mixed>>
	 * @throws RuntimeException When the seed data file is missing or invalid.
	 */
	public static function all(): array {
		if ( is_array( self::$cache ) ) {
			return self::$cache;
		}

		$file = dirname( __DIR__, 2 ) . '/resources/seed-chords.php';

		if ( ! is_readable( $file ) ) {
			throw new RuntimeException( 'Seed chord data file is missing.' );
		}

		$data = require $file;

		if ( ! is_array( $data ) ) {
			throw new RuntimeException( 'Seed chord data file must return an array.' );
		}

		self::$cache = $data;

		return self::$cache;
	}
}
