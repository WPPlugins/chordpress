<?php
/**
 * Plugin migration and installation coordinator.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Migration;

use ChordPress\Chords\Chord;
use ChordPress\Chords\ChordRepository;
use ChordPress\Settings\Settings;
use InvalidArgumentException;
use Throwable;

defined( 'ABSPATH' ) || exit;

/**
 * Migrates 4.x data and installs fresh data once.
 */
final class Migrator {

	/**
	 * Database/migration version. Keep in sync with package.json.
	 */
	public const DB_VERSION = '5.0.0';

	/**
	 * Database version option.
	 */
	public const DB_VERSION_OPTION = 'chordpress_db_version';

	/**
	 * Install state option.
	 */
	public const INSTALL_STATE_OPTION = 'chordpress_install_state';

	/**
	 * Migration failure transient.
	 */
	public const ERROR_TRANSIENT = 'chordpress_migration_error';

	/**
	 * Legacy chord meta keys that are replaced by structured JSON.
	 *
	 * @var array<string, string>
	 */
	private const LEGACY_CHORD_META = array(
		'guitar_chord_barres'   => 'barres',
		'guitar_chord_fingers'  => 'fingers',
		'guitar_chord_frets'    => 'frets',
		'guitar_chord_strings'  => 'strings',
		'guitar_chord_position' => 'position',
		'guitar_chord_tuning'   => 'tuning',
	);

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
	 * Seeder.
	 *
	 * @var SeederInterface
	 */
	private SeederInterface $seeder;

	/**
	 * Constructor.
	 *
	 * @param Store                $store    Migration store.
	 * @param Settings|null        $settings Settings service.
	 * @param SeederInterface|null $seeder Seeder.
	 */
	public function __construct( Store $store, ?Settings $settings = null, ?SeederInterface $seeder = null ) {
		$this->store    = $store;
		$this->settings = $settings ?? new Settings();
		$this->seeder   = $seeder ?? new Seeder( new ChordRepository() );
	}

	/**
	 * Register the admin notice hook.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'admin_notices', array( self::class, 'admin_notice' ) );
	}

	/**
	 * Run the migration check on activation and upgrade.
	 *
	 * @return void
	 */
	public static function maybe_migrate(): void {
		if ( defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			return;
		}

		$migrator = new self(
			new WordPressStore(),
			new Settings(),
			new Seeder( new ChordRepository() )
		);

		$migrator->migrate_if_needed();
	}

	/**
	 * Display a migration failure to administrators.
	 *
	 * @return void
	 */
	public static function admin_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$message = get_transient( self::ERROR_TRANSIENT );

		if ( ! is_string( $message ) || '' === $message ) {
			return;
		}

		echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Migrate, seed or record the current installation.
	 *
	 * @return bool True when the installation is current or was migrated successfully.
	 */
	public function migrate_if_needed(): bool {
		$current = $this->store->get_option( self::DB_VERSION_OPTION, '' );

		if ( is_string( $current ) && version_compare( $current, self::DB_VERSION, '>=' ) ) {
			return true;
		}

		try {
			$legacy_options  = $this->legacy_option_values();
			$chord_plan      = $this->legacy_chord_plan();
			$removed_options = $this->removed_options_present();

			if ( array() !== $chord_plan['errors'] ) {
				$this->fail( implode( ' ', $chord_plan['errors'] ) );
			}

			$has_legacy_data = array() !== $legacy_options
				|| array() !== $chord_plan['updates']
				|| array() !== $chord_plan['name_updates']
				|| array() !== $chord_plan['cleanup']
				|| $removed_options;

			if ( $has_legacy_data ) {
				$this->migrate_options( $legacy_options );
				$this->migrate_chords( $chord_plan );
				$this->delete_legacy_options();
				$this->store->update_option( self::INSTALL_STATE_OPTION, 'upgraded' );
				$this->record_success();

				return true;
			}

			$has_new_settings = null !== $this->store->get_option( Settings::OPTION, null );
			$has_chords       = array() !== $this->store->chord_post_ids();
			$install_state    = (string) $this->store->get_option( self::INSTALL_STATE_OPTION, '' );

			if ( 'seeding' === $install_state ) {
				$this->seeder->seed();
				$this->store->update_option( self::INSTALL_STATE_OPTION, 'fresh' );
				$this->record_success();

				return true;
			}

			if ( ! $has_new_settings && ! $has_chords && '' === $install_state ) {
				$this->store->update_option( self::INSTALL_STATE_OPTION, 'seeding' );
				$this->seeder->seed();
				$this->store->update_option( self::INSTALL_STATE_OPTION, 'fresh' );
				$this->record_success();

				return true;
			}

			if ( '' === $install_state ) {
				$this->store->update_option( self::INSTALL_STATE_OPTION, 'existing' );
			}

			$this->record_success();

			return true;
		} catch ( Throwable $exception ) {
			$this->store->set_transient(
				self::ERROR_TRANSIENT,
				'ChordPress migration failed: ' . $exception->getMessage(),
				DAY_IN_SECONDS
			);

			return false;
		}
	}

	/**
	 * Record the current database version and clear the failure notice.
	 *
	 * @return void
	 * @throws MigrationException When the version cannot be stored.
	 */
	private function record_success(): void {
		$this->store->update_option( self::DB_VERSION_OPTION, self::DB_VERSION );

		if ( self::DB_VERSION !== $this->store->get_option( self::DB_VERSION_OPTION ) ) {
			$this->fail( 'Could not record the database version.' );
		}

		$this->store->delete_transient( self::ERROR_TRANSIENT );
	}

	/**
	 * Determine whether any legacy options that should only be removed are present.
	 *
	 * @return bool
	 */
	private function removed_options_present(): bool {
		foreach ( LegacyOptions::REMOVED_OPTIONS as $name ) {
			if ( null !== $this->store->get_option( $name, null ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Read all legacy option values keyed by settings key.
	 *
	 * @return array<string, mixed>
	 */
	private function legacy_option_values(): array {
		$values = array();

		foreach ( LegacyOptions::map() as $legacy_name => $settings_key ) {
			$value = $this->store->get_option( $legacy_name, null );

			if ( null !== $value ) {
				$values[ $settings_key ] = $value;
			}
		}

		return $values;
	}

	/**
	 * Write and verify migrated settings.
	 *
	 * @param array<string, mixed> $legacy Legacy values keyed by settings key.
	 * @return void
	 * @throws MigrationException When the settings cannot be written or verified.
	 */
	private function migrate_options( array $legacy ): void {
		if ( array() === $legacy ) {
			return;
		}

		$existing = $this->store->get_option( Settings::OPTION, array() );

		if ( ! is_array( $existing ) ) {
			$existing = array();
		}

		$expected = $this->settings->sanitize( array_merge( $existing, $legacy ) );

		$this->store->update_option( Settings::OPTION, $expected );

		$stored = $this->store->get_option( Settings::OPTION, null );

		if ( ! is_array( $stored ) || $stored !== $expected ) {
			$this->fail( 'Settings verification failed.' );
		}
	}

	/**
	 * Build the chord migration plan without writing anything.
	 *
	 * @return array<string, mixed>
	 */
	private function legacy_chord_plan(): array {
		$updates      = array();
		$name_updates = array();
		$cleanup      = array();
		$errors       = array();

		foreach ( $this->store->chord_post_ids() as $post_id ) {
			$new_json = $this->store->get_post_meta( $post_id, ChordRepository::META_DATA );
			$existing = null;

			if ( is_string( $new_json ) && '' !== $new_json ) {
				try {
					$existing = Chord::from_json( $new_json );
				} catch ( InvalidArgumentException ) {
					$existing = null;
				}
			}

			$name         = $this->store->get_post_meta( $post_id, ChordRepository::META_NAME );
			$cleanup_keys = array();

			foreach ( array_keys( self::LEGACY_CHORD_META ) as $legacy_key ) {
				if ( '' !== $this->meta_string( $post_id, $legacy_key ) ) {
					$cleanup_keys[] = $legacy_key;
				}
			}

			if ( $existing instanceof Chord ) {
				$lookup = $existing->lookup_key();

				if ( ! is_string( $name ) || $lookup !== $name ) {
					$name_updates[ $post_id ] = $lookup;
				}

				if ( array() !== $cleanup_keys ) {
					$cleanup[ $post_id ] = $cleanup_keys;
				}

				continue;
			}

			if ( ! is_string( $name ) || '' === trim( $name ) ) {
				continue;
			}

			$legacy = array( 'name' => $name );

			foreach ( self::LEGACY_CHORD_META as $legacy_key => $field ) {
				$legacy[ $field ] = $this->meta_string( $post_id, $legacy_key );
			}

			try {
				$chord = Chord::from_array( $legacy );
			} catch ( InvalidArgumentException $exception ) {
				$errors[] = 'Chord post ' . $post_id . ': ' . $exception->getMessage();
				continue;
			}

			$updates[ $post_id ] = $chord;

			if ( array() !== $cleanup_keys ) {
				$cleanup[ $post_id ] = $cleanup_keys;
			}
		}

		return array(
			'updates'      => $updates,
			'name_updates' => $name_updates,
			'cleanup'      => $cleanup,
			'errors'       => $errors,
		);
	}

	/**
	 * Write, verify and clean up migrated chord data.
	 *
	 * @param array<string, mixed> $plan Chord migration plan.
	 * @return void
	 * @throws MigrationException When chords cannot be written or verified.
	 */
	private function migrate_chords( array $plan ): void {
		if ( array() !== $plan['errors'] ) {
			$this->fail( implode( ' ', $plan['errors'] ) );
		}

		foreach ( $plan['updates'] as $post_id => $chord ) {
			$this->store->update_post_meta( $post_id, ChordRepository::META_DATA, $chord->to_json() );
			$this->store->update_post_meta( $post_id, ChordRepository::META_NAME, $chord->lookup_key() );
		}

		foreach ( $plan['name_updates'] as $post_id => $lookup ) {
			$this->store->update_post_meta( $post_id, ChordRepository::META_NAME, $lookup );
		}

		foreach ( $plan['updates'] as $post_id => $chord ) {
			$this->verify_chord( $post_id, $chord );
		}

		foreach ( $plan['name_updates'] as $post_id => $lookup ) {
			$name = $this->store->get_post_meta( $post_id, ChordRepository::META_NAME );

			if ( ! is_string( $name ) || Chord::normalize_name( $name ) !== $lookup ) {
				$this->fail( 'Chord name verification failed for post ' . $post_id . '.' );
			}
		}

		foreach ( $plan['cleanup'] as $post_id => $keys ) {
			foreach ( $keys as $key ) {
				$this->store->delete_post_meta( $post_id, $key );
			}

			foreach ( $keys as $key ) {
				if ( '' !== $this->meta_string( $post_id, $key ) ) {
					$this->fail( 'Legacy chord meta cleanup failed for post ' . $post_id . '.' );
				}
			}
		}
	}

	/**
	 * Verify one migrated chord.
	 *
	 * @param int   $post_id Post ID.
	 * @param Chord $chord   Expected chord.
	 * @return void
	 * @throws MigrationException When the stored data does not match.
	 */
	private function verify_chord( int $post_id, Chord $chord ): void {
		$json = $this->store->get_post_meta( $post_id, ChordRepository::META_DATA );
		$name = $this->store->get_post_meta( $post_id, ChordRepository::META_NAME );

		if ( ! is_string( $json ) || '' === $json ) {
			$this->fail( 'Chord JSON verification failed for post ' . $post_id . '.' );
		}

		try {
			$stored = Chord::from_json( $json );
		} catch ( InvalidArgumentException ) {
			$this->fail( 'Chord JSON verification failed for post ' . $post_id . '.' );
		}

		if ( $stored->to_array() !== $chord->to_array() ) {
			$this->fail( 'Chord data verification failed for post ' . $post_id . '.' );
		}

		if ( ! is_string( $name ) || Chord::normalize_name( $name ) !== $chord->lookup_key() ) {
			$this->fail( 'Chord name verification failed for post ' . $post_id . '.' );
		}
	}

	/**
	 * Delete legacy options after every migration step has succeeded.
	 *
	 * @return void
	 * @throws MigrationException When a legacy option cannot be removed.
	 */
	private function delete_legacy_options(): void {
		$names = LegacyOptions::names();

		foreach ( $names as $name ) {
			$this->store->delete_option( $name );
		}

		foreach ( $names as $name ) {
			if ( null !== $this->store->get_option( $name, null ) ) {
				$this->fail( 'Legacy option cleanup failed: ' . $name );
			}
		}
	}

	/**
	 * Throw a migration exception.
	 *
	 * @param string $message Error message.
	 * @return never
	 * @throws MigrationException Always.
	 */
	private function fail( string $message ): never {
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are not rendered output.
		throw new MigrationException( $message );
	}

	/**
	 * Read a scalar post meta value as a string.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return string
	 */
	private function meta_string( int $post_id, string $key ): string {
		$value = $this->store->get_post_meta( $post_id, $key );

		return is_scalar( $value ) ? (string) $value : '';
	}
}
