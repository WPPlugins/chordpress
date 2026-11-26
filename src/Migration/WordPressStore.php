<?php
/**
 * WordPress-backed migration store.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Migration;

use ChordPress\Chords\ChordRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Reads and writes migration state through WordPress APIs.
 */
final class WordPressStore implements Store {

	/**
	 * Post statuses included in chord queries.
	 *
	 * @var array<int, string>
	 */
	private const POST_STATUSES = array( 'publish', 'future', 'draft', 'pending', 'private', 'trash' );

	/**
	 * Read an option.
	 *
	 * @param string $name     Option name.
	 * @param mixed  $fallback Default value.
	 * @return mixed
	 */
	public function get_option( string $name, mixed $fallback = null ): mixed {
		return get_option( $name, $fallback );
	}

	/**
	 * Write an option.
	 *
	 * @param string $name  Option name.
	 * @param mixed  $value Option value.
	 * @return bool
	 */
	public function update_option( string $name, mixed $value ): bool {
		return (bool) update_option( $name, $value );
	}

	/**
	 * Delete an option.
	 *
	 * @param string $name Option name.
	 * @return bool
	 */
	public function delete_option( string $name ): bool {
		return (bool) delete_option( $name );
	}

	/**
	 * Return all chord post IDs.
	 *
	 * @return array<int, int>
	 */
	public function chord_post_ids(): array {
		$ids = get_posts(
			array(
				'post_type'      => ChordRepository::POST_TYPE,
				'post_status'    => self::POST_STATUSES,
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);

		return array_map( 'intval', $ids );
	}

	/**
	 * Read post meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return mixed
	 */
	public function get_post_meta( int $post_id, string $key ): mixed {
		return get_post_meta( $post_id, $key, true );
	}

	/**
	 * Write post meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Meta value.
	 * @return bool
	 */
	public function update_post_meta( int $post_id, string $key, mixed $value ): bool {
		return (bool) update_post_meta( $post_id, $key, $value );
	}

	/**
	 * Delete post meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return bool
	 */
	public function delete_post_meta( int $post_id, string $key ): bool {
		return (bool) delete_post_meta( $post_id, $key );
	}

	/**
	 * Read a transient.
	 *
	 * @param string $name Transient name.
	 * @return mixed
	 */
	public function get_transient( string $name ): mixed {
		return get_transient( $name );
	}

	/**
	 * Write a transient.
	 *
	 * @param string $name       Transient name.
	 * @param mixed  $value      Transient value.
	 * @param int    $expiration Expiration in seconds.
	 * @return bool
	 */
	public function set_transient( string $name, mixed $value, int $expiration ): bool {
		return (bool) set_transient( $name, $value, $expiration );
	}

	/**
	 * Delete a transient.
	 *
	 * @param string $name Transient name.
	 * @return bool
	 */
	public function delete_transient( string $name ): bool {
		return (bool) delete_transient( $name );
	}

	/**
	 * Delete all chord posts in batches.
	 *
	 * @return int
	 */
	public function delete_chord_posts(): int {
		$deleted = 0;

		while ( true ) {
			$ids = get_posts(
				array(
					'post_type'      => ChordRepository::POST_TYPE,
					'post_status'    => self::POST_STATUSES,
					'posts_per_page' => 100,
					'fields'         => 'ids',
					'no_found_rows'  => true,
					'orderby'        => 'ID',
					'order'          => 'ASC',
				)
			);

			if ( array() === $ids ) {
				break;
			}

			$batch_deleted = 0;

			foreach ( $ids as $post_id ) {
				if ( wp_delete_post( (int) $post_id, true ) ) {
					++$batch_deleted;
				}
			}

			$deleted += $batch_deleted;

			if ( 0 === $batch_deleted ) {
				break;
			}
		}

		return $deleted;
	}

	/**
	 * Delete user meta whose key starts with a prefix.
	 *
	 * @param string $prefix Meta key prefix.
	 * @return void
	 */
	public function delete_user_meta_by_prefix( string $prefix ): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup of prefixed user meta.
		$keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				$wpdb->esc_like( $prefix ) . '%'
			)
		);

		foreach ( (array) $keys as $key ) {
			delete_metadata( 'user', 0, (string) $key, '', true );
		}
	}

	/**
	 * Delete transients whose names start with a prefix.
	 *
	 * @param string $prefix Transient name prefix.
	 * @return void
	 */
	public function delete_transients_by_prefix( string $prefix ): void {
		global $wpdb;

		$transient = $wpdb->esc_like( '_transient_' . $prefix ) . '%';
		$timeout   = $wpdb->esc_like( '_transient_timeout_' . $prefix ) . '%';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup of prefixed transients.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$transient,
				$timeout
			)
		);

		wp_cache_flush();
	}
}
