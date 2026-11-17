<?php
/**
 * Fresh-install chord seeder.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Migration;

use ChordPress\Chords\Chord;
use ChordPress\Chords\ChordPostType;
use ChordPress\Chords\ChordRepository;
use ChordPress\Chords\SeedData;

defined( 'ABSPATH' ) || exit;

/**
 * Installs the bundled chord definitions once.
 */
final class Seeder implements SeederInterface {

	/**
	 * Chord repository.
	 *
	 * @var ChordRepository
	 */
	private ChordRepository $chords;

	/**
	 * Constructor.
	 *
	 * @param ChordRepository $chords Chord repository.
	 */
	public function __construct( ChordRepository $chords ) {
		$this->chords = $chords;
	}

	/**
	 * Seed the bundled chords.
	 *
	 * @return int Number of chords created.
	 * @throws MigrationException When the seed data cannot be installed or verified.
	 */
	public function seed(): int {
		$rows = SeedData::all();

		if ( array() === $rows ) {
			throw new MigrationException( 'Seed data is empty.' );
		}

		$registered_post_type = false;

		if ( ! post_type_exists( ChordRepository::POST_TYPE ) ) {
			( new ChordPostType() )->register();
			$registered_post_type = true;
		}

		$names = array();

		foreach ( $rows as $row ) {
			$names[] = (string) $row['name'];
		}

		$this->chords->flush_cache();
		$existing = $this->chords->find_by_names( $names );
		$created  = 0;

		foreach ( $rows as $row ) {
			$chord = Chord::from_array( $row );

			if ( isset( $existing[ $chord->lookup_key() ] ) ) {
				continue;
			}

			$title  = isset( $row['title'] ) && is_scalar( $row['title'] ) ? (string) $row['title'] : $chord->name;
			$result = $this->chords->save( $chord, array( 'title' => $title ) );

			if ( is_wp_error( $result ) ) {
				$message = 'Could not seed chord ' . $chord->name . ': ' . $result->get_error_message();
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
				throw new MigrationException( $message );
			}

			$existing[ $chord->lookup_key() ] = $chord;
			++$created;
		}

		$this->chords->flush_cache();
		$verified = $this->chords->find_by_names( $names );

		if ( count( $verified ) !== count( $rows ) ) {
			throw new MigrationException( 'Seed verification failed.' );
		}

		if ( $registered_post_type ) {
			flush_rewrite_rules();
		}

		return $created;
	}
}
