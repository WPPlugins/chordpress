<?php
/**
 * Chord lookup and persistence.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Chords;

use InvalidArgumentException;
use WP_Error;
use WP_Post;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Request-scoped chord repository with batched lookup.
 */
class ChordRepository {

	public const POST_TYPE = 'guitar_chord';
	public const META_NAME = 'guitar_chord_name';
	public const META_DATA = '_chordpress_chord_data';

	/**
	 * Request-level cache, including negative lookups.
	 *
	 * @var array<string, Chord|null>
	 */
	private array $cache = array();

	/**
	 * Find several chords by name with one query for all cache misses.
	 *
	 * @param array<int, mixed> $names Chord names.
	 * @return array<string, Chord> Chords keyed by normalized lookup name.
	 */
	public function find_by_names( array $names ): array {
		$lookups = array();

		foreach ( $names as $name ) {
			if ( ! is_string( $name ) || '' === trim( $name ) ) {
				continue;
			}

			try {
				$lookups[] = Chord::normalize_name( $name );
			} catch ( InvalidArgumentException ) {
				continue;
			}
		}

		$lookups = array_values( array_unique( $lookups ) );

		if ( array() === $lookups ) {
			return array();
		}

		$missing = array();

		foreach ( $lookups as $lookup ) {
			if ( ! array_key_exists( $lookup, $this->cache ) ) {
				$missing[] = $lookup;
			}
		}

		if ( array() !== $missing ) {
			$found = $this->fetch( $missing );

			foreach ( $missing as $lookup ) {
				$this->cache[ $lookup ] = $found[ $lookup ] ?? null;
			}
		}

		$chords = array();

		foreach ( $lookups as $lookup ) {
			$chord = $this->cache[ $lookup ] ?? null;

			if ( $chord instanceof Chord ) {
				$chords[ $lookup ] = $chord;
			}
		}

		return $chords;
	}

	/**
	 * Find one chord by post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return Chord|null
	 */
	public function find_by_id( int $post_id ): ?Chord {
		if ( $post_id <= 0 ) {
			return null;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post || self::POST_TYPE !== $post->post_type ) {
			return null;
		}

		$json = get_post_meta( $post_id, self::META_DATA, true );

		if ( ! is_string( $json ) || '' === $json ) {
			return null;
		}

		try {
			$chord = Chord::from_json( $json );
		} catch ( InvalidArgumentException ) {
			return null;
		}

		$this->cache[ $chord->lookup_key() ] = $chord;

		return $chord;
	}

	/**
	 * Find one chord by name.
	 *
	 * @param string $name Chord name.
	 * @return Chord|null
	 */
	public function find_by_name( string $name ): ?Chord {
		try {
			$lookup = Chord::normalize_name( $name );
		} catch ( InvalidArgumentException ) {
			return null;
		}

		$chords = $this->find_by_names( array( $lookup ) );

		return $chords[ $lookup ] ?? null;
	}

	/**
	 * Create a chord post with structured data.
	 *
	 * @param Chord                $chord Chord value object.
	 * @param array<string, mixed> $args  Optional post arguments.
	 * @return int|WP_Error
	 * @throws \JsonException When the chord cannot be serialized.
	 */
	public function save( Chord $chord, array $args = array() ): int|WP_Error {
		$title = isset( $args['title'] ) && is_scalar( $args['title'] )
			? sanitize_text_field( (string) $args['title'] )
			: $chord->name;

		$status = isset( $args['post_status'] ) && is_string( $args['post_status'] )
			? $args['post_status']
			: 'publish';

		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => $status,
				'post_title'   => $title,
				'post_content' => '',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$post_id = (int) $post_id;

		update_post_meta( $post_id, self::META_NAME, $chord->lookup_key() );
		update_post_meta( $post_id, self::META_DATA, $chord->to_json() );

		$this->cache[ $chord->lookup_key() ] = $chord;

		return $post_id;
	}

	/**
	 * Update an existing chord post.
	 *
	 * @param int   $post_id Post ID.
	 * @param Chord $chord   Chord value object.
	 * @return bool
	 * @throws \JsonException When the chord cannot be serialized.
	 */
	public function update( int $post_id, Chord $chord ): bool {
		if ( $post_id <= 0 ) {
			return false;
		}

		update_post_meta( $post_id, self::META_NAME, $chord->lookup_key() );
		update_post_meta( $post_id, self::META_DATA, $chord->to_json() );

		$this->cache[ $chord->lookup_key() ] = $chord;

		return true;
	}

	/**
	 * Clear the request-level cache.
	 *
	 * @return void
	 */
	public function flush_cache(): void {
		$this->cache = array();
	}

	/**
	 * Fetch missing chord names in one query and preload their meta.
	 *
	 * @param array<int, string> $lookups Normalized chord names.
	 * @return array<string, Chord>
	 */
	protected function fetch( array $lookups ): array {
		if ( array() === $lookups ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'              => self::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
				'ignore_sticky_posts'    => true,
				'orderby'                => 'none',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Batch lookup of the indexed guitar_chord_name meta key.
				'meta_query'             => array(
					array(
						'key'     => self::META_NAME,
						'value'   => $lookups,
						'compare' => 'IN',
					),
				),
			)
		);

		$chords = array();

		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$lookup = get_post_meta( $post->ID, self::META_NAME, true );

			if ( ! is_string( $lookup ) || '' === $lookup ) {
				continue;
			}

			try {
				$lookup = Chord::normalize_name( $lookup );
			} catch ( InvalidArgumentException ) {
				continue;
			}

			$json = get_post_meta( $post->ID, self::META_DATA, true );

			if ( ! is_string( $json ) || '' === $json ) {
				continue;
			}

			try {
				$chords[ $lookup ] = Chord::from_json( $json );
			} catch ( InvalidArgumentException ) {
				continue;
			}
		}

		return $chords;
	}
}
