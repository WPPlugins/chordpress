<?php
/**
 * Canonical legacy option names for ChordPress 4.x.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Migration;

use ChordPress\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Maps every legacy option to its settings key.
 */
final class LegacyOptions {

	/**
	 * Legacy option prefix.
	 */
	public const PREFIX = 'chordpress_';

	/**
	 * Options duplicated in the 4.0.2 option and uninstall lists.
	 */
	public const DUPLICATE_OPTIONS = array(
		'chordpress_text_chord_fretLabelColor',
	);

	/**
	 * Legacy options that are intentionally not migrated.
	 *
	 * These names are still deleted from existing installations so that jTab
	 * remnants do not survive the upgrade.
	 */
	public const REMOVED_OPTIONS = array(
		'chordpress_checkbox_show_jtab_sheet',
	);

	/**
	 * Return legacy option name to settings key mappings.
	 *
	 * @return array<string, string>
	 */
	public static function map(): array {
		$map = array();

		foreach ( Settings::schema() as $key => $field ) {
			$map[ self::PREFIX . $key ] = $key;
		}

		return $map;
	}

	/**
	 * Return all legacy option names, including options that are only removed.
	 *
	 * @return array<int, string>
	 */
	public static function names(): array {
		return array_values( array_unique( array_merge( array_keys( self::map() ), self::REMOVED_OPTIONS ) ) );
	}
}
