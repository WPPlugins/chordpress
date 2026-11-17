<?php
/**
 * License admin tab.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\License;

use ChordPress\Admin\Admin;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and handles the license tab.
 */
final class LicensePage {

	/**
	 * License service.
	 *
	 * @var License
	 */
	private License $license;

	/**
	 * Constructor.
	 *
	 * @param License $license License service.
	 */
	public function __construct( License $license ) {
		$this->license = $license;
	}

	/**
	 * Handle license form submissions.
	 *
	 * @return void
	 */
	public function handle(): void {
		if ( ! $this->license->is_required() ) {
			wp_die( esc_html__( 'Licensing is disabled for this build.', 'chordpress' ) );
		}

		if ( ! current_user_can( Admin::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to manage the license.', 'chordpress' ) );
		}

		check_admin_referer( 'chordpress_license', 'chordpress_license_nonce' );

		$action = isset( $_POST['chordpress_license_action'] ) ? sanitize_key( wp_unslash( (string) $_POST['chordpress_license_action'] ) ) : '';
		$key    = isset( $_POST['chordpress_license_key'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['chordpress_license_key'] ) ) : '';

		$config_error = $this->license->config_error();

		if ( is_string( $config_error ) ) {
			$this->redirect( 'error', $config_error );
		}

		if ( '' === $key ) {
			$this->redirect( 'error', __( 'Please enter a license key.', 'chordpress' ) );
		}

		$this->license->save_key( $key );

		switch ( $action ) {
			case 'activate':
				$response = $this->license->activate( $key );
				$this->finish_response( $response, __( 'The license was activated successfully.', 'chordpress' ) );
				break;

			case 'deactivate':
				$response = $this->license->deactivate( $key );

				if ( $response instanceof WP_Error ) {
					$this->redirect( 'error', $response->get_error_message() );
				}

				if ( ! $this->license->is_success( $response ) ) {
					$message = $this->license->response_message( $response );
					$this->redirect( 'error', '' !== $message ? $message : __( 'The license could not be deactivated.', 'chordpress' ) );
				}

				$this->license->delete_key();
				$this->license->clear_status_cache();
				$this->redirect( 'success', __( 'The license was deactivated successfully.', 'chordpress' ) );
				// redirect() exits, so no break is needed.

			case 'check':
				$this->license->clear_status_cache();
				$status = $this->license->status();

				if ( 'error' === ( $status['status'] ?? '' ) ) {
					$this->redirect( 'error', (string) ( $status['message'] ?? __( 'The license status could not be retrieved.', 'chordpress' ) ) );
				}

				$this->redirect( 'success', __( 'The license status was refreshed.', 'chordpress' ) );
				// redirect() exits, so no break is needed.

			default:
				$this->redirect( 'error', __( 'Unknown license action.', 'chordpress' ) );
		}
	}

	/**
	 * Render the license tab.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! $this->license->is_required() ) {
			return;
		}

		if ( ! current_user_can( Admin::CAPABILITY ) ) {
			return;
		}

		$config_error = $this->license->config_error();

		if ( is_string( $config_error ) ) {
			echo '<div class="alert alert-danger" role="alert">' . esc_html( $config_error ) . '</div>';
			return;
		}

		$this->render_notice();

		$key    = $this->license->key();
		$status = $this->license->status();

		$this->render_status( $status, $key );

		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="chordpress-license-form">';
		echo '<input type="hidden" name="action" value="chordpress_license">';
		wp_nonce_field( 'chordpress_license', 'chordpress_license_nonce' );

		echo '<div class="mb-3 row chordpress-field chordpress-field--license">';
		echo '<label class="col-12 col-sm-4 col-form-label" for="chordpress_license_key">' . esc_html__( 'License Key', 'chordpress' ) . '</label>';
		echo '<div class="col-12 col-sm-8">';
		echo '<input type="text" class="form-control" id="chordpress_license_key" name="chordpress_license_key" value="' . esc_attr( $key ) . '" autocomplete="off">';
		echo '<div class="form-text">' . esc_html__( 'Enter the license key you received for ChordPress.', 'chordpress' ) . '</div>';
		echo '</div>';
		echo '</div>';

		echo '<div class="chordpress-actions">';
		echo '<button type="submit" class="btn btn-primary" name="chordpress_license_action" value="activate">' . esc_html__( 'Activate', 'chordpress' ) . '</button>';

		if ( '' !== $key ) {
			echo '<button type="submit" class="btn btn-secondary" name="chordpress_license_action" value="check">' . esc_html__( 'Refresh Status', 'chordpress' ) . '</button>';
			echo '<button type="submit" class="btn btn-outline-secondary" name="chordpress_license_action" value="deactivate">' . esc_html__( 'Deactivate', 'chordpress' ) . '</button>';
		}

		echo '<a class="btn btn-link" href="' . esc_url( 'https://support.lewe.com/how-to-get-a-license/' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get a license', 'chordpress' ) . '</a>';
		echo '</div>';
		echo '</form>';
	}

	/**
	 * Finish an activate response.
	 *
	 * @param array<string, mixed>|WP_Error $response Response.
	 * @param string                        $success_message Success message.
	 * @return void
	 */
	private function finish_response( array|WP_Error $response, string $success_message ): void {
		if ( $response instanceof WP_Error ) {
			$this->redirect( 'error', $response->get_error_message() );
		}

		if ( $this->license->is_success( $response ) ) {
			$this->license->clear_status_cache();
			$this->redirect( 'success', $success_message );
		}

		$message = $this->license->response_message( $response );
		$this->redirect( 'error', '' !== $message ? $message : __( 'The license request failed.', 'chordpress' ) );
	}

	/**
	 * Render a stored admin notice.
	 *
	 * @return void
	 */
	private function render_notice(): void {
		$notice_key = License::NOTICE_PREFIX . get_current_user_id();
		$notice     = get_transient( $notice_key );

		if ( ! is_array( $notice ) || ! isset( $notice['type'], $notice['message'] ) ) {
			return;
		}

		delete_transient( $notice_key );

		$type = 'success' === (string) $notice['type'] ? 'success' : 'danger';

		echo '<div class="alert alert-' . esc_attr( $type ) . '" role="alert">' . esc_html( (string) $notice['message'] ) . '</div>';
	}

	/**
	 * Render the license status.
	 *
	 * @param array<string, mixed> $status Normalized status.
	 * @param string               $key    Stored key.
	 * @return void
	 */
	private function render_status( array $status, string $key ): void {
		$name    = isset( $status['status'] ) && is_scalar( $status['status'] ) ? (string) $status['status'] : 'invalid';
		$labels  = array(
			'active'       => __( 'Active License', 'chordpress' ),
			'blocked'      => __( 'Blocked License', 'chordpress' ),
			'expired'      => __( 'Expired License', 'chordpress' ),
			'invalid'      => __( 'Invalid License', 'chordpress' ),
			'missing'      => __( 'Missing License', 'chordpress' ),
			'pending'      => __( 'Pending License', 'chordpress' ),
			'unregistered' => __( 'Unregistered License', 'chordpress' ),
			'error'        => __( 'License Error', 'chordpress' ),
		);
		$label   = $labels[ $name ] ?? __( 'License Status', 'chordpress' );
		$type    = 'active' === $name ? 'success' : ( in_array( $name, array( 'blocked', 'invalid', 'error' ), true ) ? 'danger' : 'warning' );
		$message = isset( $status['message'] ) && is_scalar( $status['message'] ) ? (string) $status['message'] : '';

		echo '<div class="alert alert-' . esc_attr( $type ) . ' chordpress-license-status">';
		echo '<p><strong>' . esc_html( $label ) . '</strong></p>';

		if ( '' !== $message ) {
			echo '<p>' . esc_html( $message ) . '</p>';
		}

		if ( '' === $key ) {
			echo '<p>' . esc_html__( 'Save a license key to activate this site.', 'chordpress' ) . '</p>';
		}

		echo '</div>';

		if ( '' === $key || in_array( $name, array( 'invalid', 'error' ), true ) ) {
			return;
		}

		echo '<table class="table table-sm chordpress-license-details"><tbody>';
		$this->render_detail_row( __( 'Product', 'chordpress' ), (string) ( $status['product'] ?? '' ) );
		$this->render_detail_row( __( 'License Key', 'chordpress' ), (string) ( $status['license_key'] ?? '' ) );
		$this->render_detail_row( __( 'Licensee', 'chordpress' ), (string) ( $status['name'] ?? '' ) );
		$this->render_detail_row( __( 'E-mail', 'chordpress' ), (string) ( $status['email'] ?? '' ) );
		$this->render_detail_row( __( 'Company', 'chordpress' ), (string) ( $status['company'] ?? '' ) );
		$this->render_detail_row( __( 'Date Created', 'chordpress' ), (string) ( $status['date_created'] ?? '' ) );
		$this->render_detail_row( __( 'Date Renewed', 'chordpress' ), (string) ( $status['date_renewed'] ?? '' ) );
		$this->render_detail_row( __( 'Date Expiry', 'chordpress' ), (string) ( $status['date_expiry'] ?? '' ) );

		$days = isset( $status['days_left'] ) && is_numeric( $status['days_left'] ) ? (int) $status['days_left'] : null;

		if ( null !== $days ) {
			$this->render_detail_row(
				__( 'Days Left', 'chordpress' ),
				sprintf(
					/* translators: %d: number of days. */
					_n( '%d day', '%d days', $days, 'chordpress' ),
					$days
				)
			);
		}

		$domains = isset( $status['domains'] ) && is_array( $status['domains'] ) ? array_map( 'strval', $status['domains'] ) : array();
		$this->render_detail_row( __( 'Registered Domains', 'chordpress' ), implode( ', ', $domains ) );

		echo '</tbody></table>';
	}

	/**
	 * Render one status detail row.
	 *
	 * @param string $label Row label.
	 * @param string $value Row value.
	 * @return void
	 */
	private function render_detail_row( string $label, string $value ): void {
		if ( '' === $value ) {
			return;
		}

		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
	}

	/**
	 * Store a notice and redirect back to the license tab.
	 *
	 * @param string $type    Notice type.
	 * @param string $message Notice message.
	 * @return never
	 */
	private function redirect( string $type, string $message ): never {
		set_transient(
			License::NOTICE_PREFIX . get_current_user_id(),
			array(
				'type'    => 'success' === $type ? 'success' : 'error',
				'message' => $message,
			),
			MINUTE_IN_SECONDS
		);

		wp_safe_redirect(
			add_query_arg(
				array(
					'page' => Admin::MENU_SLUG,
					'tab'  => 'tab_license',
				),
				admin_url( 'admin.php' )
			)
		);

		exit;
	}
}
