<?php
/**
 * ChordPress settings page.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Admin;

use ChordPress\License\License;
use ChordPress\License\LicensePage;
use ChordPress\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the tabbed settings screen.
 */
final class SettingsPage {

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * License service.
	 *
	 * @var License
	 */
	private License $license;

	/**
	 * License page renderer.
	 *
	 * @var LicensePage
	 */
	private LicensePage $license_page;

	/**
	 * Constructor.
	 *
	 * @param Settings    $settings     Settings service.
	 * @param License     $license      License service.
	 * @param LicensePage $license_page License page renderer.
	 */
	public function __construct( Settings $settings, License $license, LicensePage $license_page ) {
		$this->settings     = $settings;
		$this->license      = $license;
		$this->license_page = $license_page;
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'chordpress' ) );
		}

		$tabs   = $this->tabs();
		$active = $this->active_tab( $tabs );
		$values = $this->settings->all();

		echo '<div class="wrap chordpress-admin">';
		echo '<div class="chordpress-header">';
		echo '<img class="chordpress-header-icon" src="' . esc_url( plugins_url( 'resources/images/icon-80x80.png', CHORDPRESS_FILE ) ) . '" alt="" width="40" height="40" decoding="async">';
		echo '<div>';
		echo '<h1>' . esc_html__( 'Lewe ChordPress', 'chordpress' ) . ' ' . esc_html( CHORDPRESS_VERSION ) . '</h1>';
		echo '<p class="chordpress-header-subtitle">' . esc_html__( 'Render ChordPro texts and guitar chord diagrams on your WordPress site.', 'chordpress' ) . '</p>';
		echo '</div>';
		echo '</div>';

		settings_errors();

		echo '<nav class="chordpress-tabs-nav" aria-label="' . esc_attr__( 'ChordPress settings', 'chordpress' ) . '">';
		echo '<ul class="nav nav-tabs chordpress-tabs">';

		foreach ( $tabs as $tab => $label ) {
			$is_active = $tab === $active;

			echo '<li class="nav-item">';
			echo '<a class="nav-link' . ( $is_active ? ' active' : '' ) . '" href="' . esc_url( $this->tab_url( $tab ) ) . '"' . ( $is_active ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</nav>';

		echo '<div class="card chordpress-settings-card">';
		echo '<div class="card-body">';

		echo '<form method="post" action="' . esc_url( admin_url( 'options.php' ) ) . '" class="chordpress-settings-form">';
		settings_fields( Settings::GROUP );
		echo '<input type="hidden" name="chordpress_active_tab" value="' . esc_attr( $active ) . '">';

		foreach ( $tabs as $tab => $label ) {
			if ( 'tab_license' === $tab ) {
				continue;
			}

			echo '<div id="content-' . esc_attr( $tab ) . '" class="chordpress-tab-panel"' . ( $tab === $active ? '' : ' hidden' ) . '>';
			$this->render_tab( $tab, $values );
			echo '</div>';
		}

		echo '</form>';

		if ( isset( $tabs['tab_license'] ) ) {
			echo '<div id="content-tab_license" class="chordpress-tab-panel"' . ( 'tab_license' === $active ? '' : ' hidden' ) . '>';

			if ( 'tab_license' === $active ) {
				$this->license_page->render();
			}

			echo '</div>';
		}

		echo '</div>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Return the available tabs.
	 *
	 * @return array<string, string>
	 */
	private function tabs(): array {
		$tabs = Fields::tabs();

		if ( $this->license->is_required() ) {
			$tabs['tab_license'] = __( 'License', 'chordpress' );
		}

		return $tabs;
	}

	/**
	 * Return the active tab slug.
	 *
	 * @param array<string, string> $tabs Available tabs.
	 * @return string
	 */
	private function active_tab( array $tabs ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab selection is a read-only view parameter.
		$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

		if ( isset( $tabs[ $requested ] ) ) {
			return $requested;
		}

		$first = array_key_first( $tabs );

		return is_string( $first ) ? $first : 'tab_output';
	}

	/**
	 * Return the URL for one tab.
	 *
	 * @param string $tab Tab slug.
	 * @return string
	 */
	private function tab_url( string $tab ): string {
		return add_query_arg(
			array(
				'page' => Admin::MENU_SLUG,
				'tab'  => $tab,
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Render one tab.
	 *
	 * @param string               $tab    Tab slug.
	 * @param array<string, mixed> $values Current settings.
	 * @return void
	 */
	private function render_tab( string $tab, array $values ): void {
		if ( 'tab_info' === $tab ) {
			$this->render_info();
			return;
		}

		echo '<div class="chordpress-fields">';

		if ( 'tab_chord' === $tab ) {
			$this->render_sample_preview();
		}

		foreach ( Fields::for_tab( $tab ) as $key => $field ) {
			Fields::render( $key, $field, $values[ $key ] ?? null );
		}

		echo '</div>';

		echo '<div class="chordpress-actions">';
		echo '<button type="submit" class="btn btn-primary">' . esc_html__( 'Save Changes', 'chordpress' ) . '</button>';
		echo '</div>';
	}

	/**
	 * Render the chord diagram sample preview.
	 *
	 * @return void
	 */
	private function render_sample_preview(): void {
		$sample = array(
			'title'    => 'C Major',
			'fingers'  => array(
				array( 6, 'x' ),
				array( 5, 3, '3' ),
				array( 4, 2, '2' ),
				array( 3, 0 ),
				array( 2, 1, '1' ),
				array( 1, 0 ),
			),
			'barres'   => array(),
			'position' => 1,
			'tuning'   => array( 'E', 'A', 'D', 'G', 'B', 'E' ),
			'strings'  => 6,
			'frets'    => 4,
		);

		echo '<div class="mb-3 row chordpress-field chordpress-field--preview">';
		echo '<div class="col-12 col-sm-4 chordpress-field-label">' . esc_html__( 'Sample Display', 'chordpress' ) . '</div>';
		echo '<div class="col-12 col-sm-8">';
		echo '<div class="chordpress-preview" data-chordpress-preview="settings" data-chord="' . esc_attr( (string) wp_json_encode( $sample ) ) . '" aria-live="polite"></div>';
		echo '<div class="form-text">' . esc_html__( 'This sample updates when you change the chord diagram settings.', 'chordpress' ) . '</div>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render the plugin information tab.
	 *
	 * @return void
	 */
	private function render_info(): void {
		$documentation = 'https://lewe.gitbook.io/lewe-chordpress/';
		$support       = 'https://desk.lewe.com';
		$review        = 'https://wordpress.org/support/plugin/chordpress/reviews/';
		$donate        = 'https://www.paypal.me/GeorgeLewe';

		echo '<div class="chordpress-info-banner">';
		echo '<img src="' . esc_url( plugins_url( 'resources/images/icon-128x128.png', CHORDPRESS_FILE ) ) . '" alt="" width="64" height="64" decoding="async">';
		echo '</div>';
		echo '<dl class="chordpress-info-list">';
		echo '<dt>' . esc_html__( 'Name', 'chordpress' ) . '</dt><dd>Lewe ChordPress</dd>';
		echo '<dt>' . esc_html__( 'Version', 'chordpress' ) . '</dt><dd>' . esc_html( CHORDPRESS_VERSION ) . '</dd>';
		echo '<dt>' . esc_html__( 'Author', 'chordpress' ) . '</dt><dd><a href="' . esc_url( 'https://www.lewe.com' ) . '" target="_blank" rel="noopener noreferrer">George Lewe</a></dd>';
		echo '<dt>' . esc_html__( 'Documentation', 'chordpress' ) . '</dt><dd><a href="' . esc_url( $documentation ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $documentation ) . '</a></dd>';
		echo '<dt>' . esc_html__( 'Support', 'chordpress' ) . '</dt><dd><a href="' . esc_url( $support ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $support ) . '</a></dd>';
		echo '<dt>' . esc_html__( 'Review', 'chordpress' ) . '</dt><dd><a href="' . esc_url( $review ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Rate and review ChordPress', 'chordpress' ) . '</a></dd>';

		if ( ! $this->license->is_required() ) {
			echo '<dt>' . esc_html__( 'Donate', 'chordpress' ) . '</dt><dd><a href="' . esc_url( $donate ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support the plugin', 'chordpress' ) . '</a></dd>';
		}

		echo '<dt>' . esc_html__( 'Credits', 'chordpress' ) . '</dt><dd><ul class="chordpress-credits">';
		echo '<li>' . esc_html__( 'The ChordPro team', 'chordpress' ) . '</li>';
		echo '<li>' . esc_html__( 'The SVGuitar maintainers', 'chordpress' ) . '</li>';
		echo '<li>' . esc_html__( 'The Poppins font project (SIL Open Font License)', 'chordpress' ) . '</li>';
		echo '<li>' . esc_html__( 'Everyone who reported bugs and suggested improvements', 'chordpress' ) . '</li>';
		echo '</ul></dd>';
		echo '</dl>';
	}
}
