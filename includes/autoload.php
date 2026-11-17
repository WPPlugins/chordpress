<?php
/**
 * PSR-4 autoloader for the ChordPress namespace.
 *
 * @package ChordPress
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

/**
 * Resolve a ChordPress class name to its source file.
 *
 * @param string $class_name Fully qualified class name.
 * @return string|null Absolute path or null when the class is not part of ChordPress.
 */
function chordpress_class_path( string $class_name ): ?string {
	$prefix = 'ChordPress\\';

	if ( ! str_starts_with( $class_name, $prefix ) ) {
		return null;
	}

	$relative = substr( $class_name, strlen( $prefix ) );
	$file     = __DIR__ . '/../src/' . str_replace( '\\', '/', $relative ) . '.php';

	return is_readable( $file ) ? $file : null;
}

/**
 * Load a ChordPress class from the src directory.
 *
 * @param string $class_name Fully qualified class name.
 * @return void
 */
function chordpress_autoload( string $class_name ): void {
	$file = chordpress_class_path( $class_name );

	if ( null !== $file ) {
		require $file;
	}
}

spl_autoload_register( 'chordpress_autoload' );
