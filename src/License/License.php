<?php
/**
 * License service.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\License;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Talks to the LeweLic server only while licensing is enabled.
 */
final class License {

	public const KEY_OPTION       = 'chordpress_license_key';
	public const STATUS_TRANSIENT = 'chordpress_license_status';
	public const NOTICE_PREFIX    = 'chordpress_license_notice_';

	/**
	 * Whether licensing is enabled for this build.
	 *
	 * @return bool
	 */
	public function is_required(): bool {
		$constant = 'CHORDPRESS_LICENSE_REQUIRED';

		if ( ! defined( $constant ) ) {
			return false;
		}

		return (bool) constant( $constant );
	}

	/**
	 * Return a configuration error when licensing is enabled but incomplete.
	 *
	 * @return string|null
	 */
	public function config_error(): ?string {
		if ( ! $this->is_required() ) {
			return null;
		}

		$missing = array();

		foreach ( array( 'CHORDPRESS_LICENSE_SERVER', 'CHORDPRESS_LICENSE_ITEM', 'CHORDPRESS_LICENSE_ITEM_KEY' ) as $constant ) {
			if ( '' === $this->constant( $constant ) ) {
				$missing[] = $constant;
			}
		}

		if ( array() === $missing ) {
			return null;
		}

		return sprintf(
			/* translators: %s: comma-separated configuration constant names. */
			__( 'Licensing is enabled, but the following license configuration values are missing: %s.', 'chordpress' ),
			implode( ', ', $missing )
		);
	}

	/**
	 * Return the stored license key.
	 *
	 * @return string
	 */
	public function key(): string {
		$key = get_option( self::KEY_OPTION, '' );

		return is_string( $key ) ? sanitize_text_field( $key ) : '';
	}

	/**
	 * Store a license key.
	 *
	 * @param string $key License key.
	 * @return void
	 */
	public function save_key( string $key ): void {
		update_option( self::KEY_OPTION, sanitize_text_field( $key ), false );
	}

	/**
	 * Delete the stored license key.
	 *
	 * @return void
	 */
	public function delete_key(): void {
		delete_option( self::KEY_OPTION );
	}

	/**
	 * Activate a license.
	 *
	 * @param string $key License key.
	 * @return array<string, mixed>|WP_Error
	 */
	public function activate( string $key ): array|WP_Error {
		if ( $this->is_required() ) {
			$this->clear_status_cache();
		}

		return $this->request( 'activate', $key );
	}

	/**
	 * Deactivate a license.
	 *
	 * @param string $key License key.
	 * @return array<string, mixed>|WP_Error
	 */
	public function deactivate( string $key ): array|WP_Error {
		if ( $this->is_required() ) {
			$this->clear_status_cache();
		}

		return $this->request( 'deactivate', $key );
	}

	/**
	 * Check a license.
	 *
	 * @param string $key License key.
	 * @return array<string, mixed>|WP_Error
	 */
	public function check( string $key ): array|WP_Error {
		return $this->request( 'check', $key );
	}

	/**
	 * Return the normalized license status.
	 *
	 * @return array<string, mixed>
	 */
	public function status(): array {
		if ( ! $this->is_required() ) {
			return array(
				'status'  => 'disabled',
				'message' => __( 'Licensing is disabled for this build.', 'chordpress' ),
			);
		}

		$error = $this->config_error();

		if ( is_string( $error ) ) {
			return array(
				'status'  => 'error',
				'message' => $error,
			);
		}

		$key = $this->key();

		if ( '' === $key ) {
			return array(
				'status'  => 'missing',
				'message' => __( 'No license key has been saved yet.', 'chordpress' ),
			);
		}

		$cached = get_transient( self::STATUS_TRANSIENT );

		if ( is_array( $cached ) && isset( $cached['status'] ) ) {
			return $cached;
		}

		$data = $this->check( $key );

		if ( is_wp_error( $data ) ) {
			$status = array(
				'status'  => 'error',
				'message' => $data->get_error_message(),
			);

			set_transient( self::STATUS_TRANSIENT, $status, 5 * MINUTE_IN_SECONDS );

			return $status;
		}

		$status = $this->normalize_status( $data );
		set_transient( self::STATUS_TRANSIENT, $status, HOUR_IN_SECONDS );

		return $status;
	}

	/**
	 * Clear the cached license status.
	 *
	 * @return void
	 */
	public function clear_status_cache(): void {
		delete_transient( self::STATUS_TRANSIENT );
	}

	/**
	 * Return whether a response reports success.
	 *
	 * @param array<string, mixed> $data Response data.
	 * @return bool
	 */
	public function is_success( array $data ): bool {
		return isset( $data['result'] ) && is_scalar( $data['result'] ) && 'success' === (string) $data['result'];
	}

	/**
	 * Return the response message.
	 *
	 * @param array<string, mixed> $data Response data.
	 * @return string
	 */
	public function response_message( array $data ): string {
		return isset( $data['message'] ) && is_scalar( $data['message'] )
			? sanitize_text_field( (string) $data['message'] )
			: '';
	}

	/**
	 * Send an authenticated POST request to the license server.
	 *
	 * @param string $action Action: activate, deactivate or check.
	 * @param string $key    License key.
	 * @return array<string, mixed>|WP_Error
	 */
	private function request( string $action, string $key ): array|WP_Error {
		if ( ! in_array( $action, array( 'activate', 'deactivate', 'check' ), true ) ) {
			return new WP_Error( 'chordpress_license_action', __( 'Unknown license action.', 'chordpress' ) );
		}

		if ( ! $this->is_required() ) {
			return new WP_Error( 'chordpress_license_disabled', __( 'Licensing is disabled for this build.', 'chordpress' ) );
		}

		$error = $this->config_error();

		if ( is_string( $error ) ) {
			return new WP_Error( 'chordpress_license_config', $error );
		}

		$server = $this->constant( 'CHORDPRESS_LICENSE_SERVER' );

		if ( '' === $server || ! wp_http_validate_url( $server ) ) {
			return new WP_Error( 'chordpress_license_server', __( 'The license server URL is invalid.', 'chordpress' ) );
		}

		$response = wp_remote_post(
			$server,
			array(
				'timeout'     => 15,
				'redirection' => 2,
				'sslverify'   => true,
				'headers'     => array(
					'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
				),
				'body'        => array(
					'slm_action'        => 'slm_' . $action,
					'secret_key'        => $this->constant( 'CHORDPRESS_LICENSE_ITEM_KEY' ),
					'license_key'       => sanitize_text_field( $key ),
					'registered_domain' => $this->domain(),
					'item_reference'    => $this->constant( 'CHORDPRESS_LICENSE_ITEM' ),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error(
				'chordpress_license_http',
				sprintf(
					/* translators: %d: HTTP status code. */
					__( 'The license server returned HTTP %d.', 'chordpress' ),
					$code
				)
			);
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $data ) ) {
			return new WP_Error( 'chordpress_license_response', __( 'The license server returned an invalid response.', 'chordpress' ) );
		}

		return $data;
	}

	/**
	 * Normalize a license server response.
	 *
	 * @param array<string, mixed> $data Response data.
	 * @return array<string, mixed>
	 */
	private function normalize_status( array $data ): array {
		$result  = isset( $data['result'] ) && is_scalar( $data['result'] ) ? (string) $data['result'] : '';
		$message = $this->response_message( $data );

		if ( 'error' === $result ) {
			return array(
				'status'  => 'invalid',
				'message' => '' !== $message ? $message : __( 'The license is invalid.', 'chordpress' ),
			);
		}

		$status  = isset( $data['status'] ) && is_scalar( $data['status'] ) ? sanitize_key( (string) $data['status'] ) : 'invalid';
		$allowed = array( 'active', 'expired', 'blocked', 'pending', 'unregistered', 'invalid' );

		if ( ! in_array( $status, $allowed, true ) ) {
			$status = 'invalid';
		}

		$domains = array();

		if ( isset( $data['registered_domains'] ) && is_array( $data['registered_domains'] ) ) {
			foreach ( $data['registered_domains'] as $domain ) {
				if ( is_array( $domain ) && isset( $domain['registered_domain'] ) && is_scalar( $domain['registered_domain'] ) ) {
					$domains[] = sanitize_text_field( (string) $domain['registered_domain'] );
				}
			}
		}

		if ( 'active' === $status && ! in_array( $this->domain(), $domains, true ) ) {
			$status = 'unregistered';
		}

		$expiry = $this->field( $data, 'date_expiry' );
		$days   = null;

		if ( '' !== $expiry ) {
			$timestamp = strtotime( $expiry );

			if ( false !== $timestamp ) {
				$days = (int) ceil( ( $timestamp - time() ) / DAY_IN_SECONDS );
			}
		}

		return array(
			'status'       => $status,
			'message'      => $message,
			'product'      => $this->field( $data, 'product_ref' ),
			'license_key'  => $this->field( $data, 'license_key' ),
			'name'         => trim( $this->field( $data, 'first_name' ) . ' ' . $this->field( $data, 'last_name' ) ),
			'email'        => $this->field( $data, 'email' ),
			'company'      => $this->field( $data, 'company_name' ),
			'date_created' => $this->field( $data, 'date_created' ),
			'date_renewed' => $this->field( $data, 'date_renewed' ),
			'date_expiry'  => $expiry,
			'days_left'    => $days,
			'domains'      => $domains,
		);
	}

	/**
	 * Read a scalar response field.
	 *
	 * @param array<string, mixed> $data Response data.
	 * @param string               $key  Field key.
	 * @return string
	 */
	private function field( array $data, string $key ): string {
		return isset( $data[ $key ] ) && is_scalar( $data[ $key ] )
			? sanitize_text_field( (string) $data[ $key ] )
			: '';
	}

	/**
	 * Read a defined constant as a string.
	 *
	 * @param string $name Constant name.
	 * @return string
	 */
	private function constant( string $name ): string {
		if ( ! defined( $name ) ) {
			return '';
		}

		$value = constant( $name );

		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Return the current site host.
	 *
	 * @return string
	 */
	private function domain(): string {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );

		return is_string( $host ) ? $host : '';
	}
}
