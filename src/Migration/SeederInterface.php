<?php
/**
 * Seed installer contract.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Migration;

defined( 'ABSPATH' ) || exit;

/**
 * Installs the bundled chord definitions.
 */
interface SeederInterface {

	/**
	 * Seed the chord post type.
	 *
	 * @return int Number of chords created.
	 */
	public function seed(): int;
}
