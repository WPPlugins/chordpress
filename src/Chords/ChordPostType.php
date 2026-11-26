<?php
/**
 * Guitar chord post type registration and admin columns.
 *
 * @package ChordPress
 */

declare(strict_types=1);

namespace ChordPress\Chords;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the guitar chord post type.
 */
final class ChordPostType {

	/**
	 * Register the post type.
	 *
	 * @return void
	 */
	public function register(): void {
		register_post_type( 'guitar_chord', $this->arguments() );
	}

	/**
	 * Register the chord list table columns.
	 *
	 * @return void
	 */
	public function register_admin_columns(): void {
		add_filter( 'manage_guitar_chord_posts_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_guitar_chord_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	/**
	 * Add chord-specific columns after the title column.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function add_columns( array $columns ): array {
		$result = array();

		foreach ( $columns as $key => $label ) {
			$result[ $key ] = $label;

			if ( 'title' === $key ) {
				$result['guitar_chord_name']      = __( 'Name', 'chordpress' );
				$result['guitar_chord_strings']   = __( 'Strings', 'chordpress' );
				$result['guitar_chord_shortcode'] = __( 'Shortcode', 'chordpress' );
			}
		}

		if ( ! isset( $result['guitar_chord_name'] ) ) {
			$result['guitar_chord_name']      = __( 'Name', 'chordpress' );
			$result['guitar_chord_strings']   = __( 'Strings', 'chordpress' );
			$result['guitar_chord_shortcode'] = __( 'Shortcode', 'chordpress' );
		}

		return $result;
	}

	/**
	 * Render a chord list table column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( string $column, int $post_id ): void {
		$chord = $this->chord_for_post( $post_id );

		switch ( $column ) {
			case 'guitar_chord_name':
				if ( $chord instanceof Chord ) {
					echo esc_html( $chord->name );
					return;
				}

				echo esc_html( (string) get_post_meta( $post_id, ChordRepository::META_NAME, true ) );
				return;

			case 'guitar_chord_strings':
				if ( $chord instanceof Chord ) {
					echo esc_html( (string) $chord->strings );
					return;
				}

				echo esc_html( (string) get_post_meta( $post_id, 'guitar_chord_strings', true ) );
				return;

			case 'guitar_chord_shortcode':
				echo esc_html( '[chordpress-chord chord="' . $post_id . '"]' );
				return;
		}
	}

	/**
	 * Read and validate a chord post's structured data.
	 *
	 * @param int $post_id Post ID.
	 * @return Chord|null
	 */
	private function chord_for_post( int $post_id ): ?Chord {
		$json = get_post_meta( $post_id, ChordRepository::META_DATA, true );

		if ( ! is_string( $json ) || '' === $json ) {
			return null;
		}

		try {
			return Chord::from_json( $json );
		} catch ( InvalidArgumentException ) {
			return null;
		}
	}

	/**
	 * Return the post type registration arguments.
	 *
	 * @return array<string, mixed>
	 */
	private function arguments(): array {
		$labels = array(
			'name'                  => __( 'Guitar Chords', 'chordpress' ),
			'singular_name'         => __( 'Guitar Chord', 'chordpress' ),
			'menu_name'             => __( 'Guitar Chords', 'chordpress' ),
			'name_admin_bar'        => __( 'Guitar Chord', 'chordpress' ),
			'archives'              => __( 'Guitar Chord Archives', 'chordpress' ),
			'attributes'            => __( 'Guitar Chord Attributes', 'chordpress' ),
			'parent_item_colon'     => __( 'Parent Chord:', 'chordpress' ),
			'all_items'             => __( 'All Chords', 'chordpress' ),
			'add_new_item'          => __( 'Add New Chord', 'chordpress' ),
			'add_new'               => __( 'Add New', 'chordpress' ),
			'new_item'              => __( 'New Chord', 'chordpress' ),
			'edit_item'             => __( 'Edit Chord', 'chordpress' ),
			'update_item'           => __( 'Update Chord', 'chordpress' ),
			'view_item'             => __( 'View Chord', 'chordpress' ),
			'view_items'            => __( 'View Chords', 'chordpress' ),
			'search_items'          => __( 'Search Chord', 'chordpress' ),
			'not_found'             => __( 'Not found', 'chordpress' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'chordpress' ),
			'featured_image'        => __( 'Featured Image', 'chordpress' ),
			'set_featured_image'    => __( 'Set featured image', 'chordpress' ),
			'remove_featured_image' => __( 'Remove featured image', 'chordpress' ),
			'use_featured_image'    => __( 'Use as featured image', 'chordpress' ),
			'insert_into_item'      => __( 'Insert into chord', 'chordpress' ),
			'uploaded_to_this_item' => __( 'Uploaded to this chord', 'chordpress' ),
			'items_list'            => __( 'Chords list', 'chordpress' ),
			'items_list_navigation' => __( 'Chords list navigation', 'chordpress' ),
			'filter_items_list'     => __( 'Filter chords list', 'chordpress' ),
		);

		return array(
			'label'               => __( 'Guitar Chord', 'chordpress' ),
			'description'         => __( 'Guitar chord diagram', 'chordpress' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-format-audio',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'show_in_rest'        => false,
		);
	}
}
