<?php
/**
 * Opt-in uninstall cleanup.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Migration;

use ChordPress\License\License;
use ChordPress\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Deletes plugin data only when the corresponding uninstall options are enabled.
 */
final class Uninstaller {

	/**
	 * Migration store.
	 *
	 * @var Store
	 */
	private Store $store;

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Store    $store    Migration store.
	 * @param Settings $settings Settings service.
	 */
	public function __construct( Store $store, Settings $settings ) {
		$this->store    = $store;
		$this->settings = $settings;
	}

	/**
	 * Run opt-in uninstall cleanup.
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$stored   = $this->store->get_option( Settings::OPTION, array() );
		$settings = $this->settings->sanitize( is_array( $stored ) ? $stored : array() );

		$delete_options = ! empty( $settings['checkbox_uninstall_delete_options'] )
			|| (bool) $this->store->get_option( 'chordpress_checkbox_uninstall_delete_options', false );
		$delete_chords  = ! empty( $settings['checkbox_uninstall_delete_chordposts'] )
			|| (bool) $this->store->get_option( 'chordpress_checkbox_uninstall_delete_chordposts', false );

		if ( ! $delete_options && ! $delete_chords ) {
			return;
		}

		if ( $delete_options ) {
			$this->delete_options();
		}

		if ( $delete_chords ) {
			$this->store->delete_chord_posts();
		}
	}

	/**
	 * Delete options, transients and user meta.
	 *
	 * @return void
	 */
	private function delete_options(): void {
		$this->store->delete_option( Settings::OPTION );
		$this->store->delete_option( Migrator::DB_VERSION_OPTION );
		$this->store->delete_option( Migrator::INSTALL_STATE_OPTION );
		$this->store->delete_option( License::KEY_OPTION );
		$this->store->delete_transient( Migrator::ERROR_TRANSIENT );
		$this->store->delete_transient( License::STATUS_TRANSIENT );

		foreach ( LegacyOptions::names() as $name ) {
			$this->store->delete_option( $name );
		}

		$this->store->delete_transients_by_prefix( LegacyOptions::PREFIX );
		$this->store->delete_transients_by_prefix( 'chordpress_license_' );
		$this->store->delete_user_meta_by_prefix( LegacyOptions::PREFIX );
	}
}
