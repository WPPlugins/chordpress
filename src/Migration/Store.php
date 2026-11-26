<?php
/**
 * Migration and uninstall storage contract.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Migration;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress state used by migrations and uninstall.
 */
interface Store {

	/**
	 * Read an option.
	 *
	 * @param string $name     Option name.
	 * @param mixed  $fallback Default value.
	 * @return mixed
	 */
	public function get_option( string $name, mixed $fallback = null ): mixed;

	/**
	 * Write an option.
	 *
	 * @param string $name  Option name.
	 * @param mixed  $value Option value.
	 * @return bool
	 */
	public function update_option( string $name, mixed $value ): bool;

	/**
	 * Delete an option.
	 *
	 * @param string $name Option name.
	 * @return bool
	 */
	public function delete_option( string $name ): bool;

	/**
	 * Return all chord post IDs, including non-published posts.
	 *
	 * @return array<int, int>
	 */
	public function chord_post_ids(): array;

	/**
	 * Read post meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return mixed
	 */
	public function get_post_meta( int $post_id, string $key ): mixed;

	/**
	 * Write post meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $value   Meta value.
	 * @return bool
	 */
	public function update_post_meta( int $post_id, string $key, mixed $value ): bool;

	/**
	 * Delete post meta.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return bool
	 */
	public function delete_post_meta( int $post_id, string $key ): bool;

	/**
	 * Read a transient.
	 *
	 * @param string $name Transient name.
	 * @return mixed
	 */
	public function get_transient( string $name ): mixed;

	/**
	 * Write a transient.
	 *
	 * @param string $name       Transient name.
	 * @param mixed  $value      Transient value.
	 * @param int    $expiration Expiration in seconds.
	 * @return bool
	 */
	public function set_transient( string $name, mixed $value, int $expiration ): bool;

	/**
	 * Delete a transient.
	 *
	 * @param string $name Transient name.
	 * @return bool
	 */
	public function delete_transient( string $name ): bool;

	/**
	 * Delete all chord posts in batches.
	 *
	 * @return int Number of deleted posts.
	 */
	public function delete_chord_posts(): int;

	/**
	 * Delete user meta whose key starts with a prefix.
	 *
	 * @param string $prefix Meta key prefix.
	 * @return void
	 */
	public function delete_user_meta_by_prefix( string $prefix ): void;

	/**
	 * Delete transients whose names start with a prefix.
	 *
	 * @param string $prefix Transient name prefix.
	 * @return void
	 */
	public function delete_transients_by_prefix( string $prefix ): void;
}
