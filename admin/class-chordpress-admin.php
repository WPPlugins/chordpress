<?php

/**
 * Admin functionality of the plugin
 *
 * @link       https://www.lewe.com
 * @since      1.0.0
 *
 * @package    ChordPress
 * @subpackage ChordPress/admin
 */

/**
 * Admin functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    ChordPress
 * @subpackage ChordPress/admin
 * @author     George Lewe <george@lewe.com>
 */
class ChordPress_Admin {

	/**
	 * The unique name (slug) of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name    The slug of this plugin.
	 * @param      string    $version        The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the admin stylesheets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chordpress-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the admin javascripts.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chordpress-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register Settings
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {

      $options = array (

         $this->plugin_name.'_checkbox_uninstall_delete',
         $this->plugin_name.'_checkbox_hide_album',
         $this->plugin_name.'_checkbox_hide_artist',
         $this->plugin_name.'_checkbox_hide_chords',
         $this->plugin_name.'_checkbox_hide_comments',
         $this->plugin_name.'_checkbox_hide_composer',
         $this->plugin_name.'_checkbox_hide_subtitle',
         $this->plugin_name.'_checkbox_hide_title',
         $this->plugin_name.'_checkbox_hide_year',
         $this->plugin_name.'_checkbox_hb_notation',
         $this->plugin_name.'_select_title_level',
         $this->plugin_name.'_select_subtitle_level',
         $this->plugin_name.'_text_meta_style',
         $this->plugin_name.'_text_chord_textcolor',
         $this->plugin_name.'_text_chord_textsize',
         $this->plugin_name.'_text_lyrics_textcolor',
         $this->plugin_name.'_text_lyrics_textsize',
         $this->plugin_name.'_text_comment_style',
         $this->plugin_name.'_text_chorus_style',
         $this->plugin_name.'_text_verse_style',

      );
   
      foreach ($options as $option) {

         add_option( $option, '');
         register_setting( $this->plugin_name.'_options_group', $option, $this->plugin_name.'_callback' );

      }

	}

	/**
	 * Create Admin Menu
	 *
	 * @since    1.0.0
	 */
	public function create_menu() {

      add_menu_page(
         'ChordPress Admin',                                                   // The title to be displayed in the browser window for this page.
         'ChordPress',                                                         // The text to be displayed for this menu item
         'manage_options',                                                     // Which type of users can see this menu item
         $this->plugin_name.'_admin_menu',                                     // The unique ID for this menu item or file reference
         null,                                                                 // The name of the function to call when rendering this menu's page (null if file ref above)
         plugin_dir_url(__FILE__).'images/icon-20x20.png'                      // The icon of the menu entry
      );

      add_submenu_page(
         $this->plugin_name.'_admin_menu',                                     // The parent menu slug
         'ChordPress Options',                                                 // The title to be displayed in the browser window for this page.
         'Options',                                                            // The text to be displayed for this menu item
         'manage_options',                                                     // Which type of users can see this menu item
         plugin_dir_path(__FILE__).'partials/chordpress-admin-options.php',    // The unique ID for this menu item or file reference
         null                                                                  // The name of the function to call when rendering this menu's page (null if file ref above)
      );

      if (CHORDPRESS_LICENSE_REQUIRED) {

         add_submenu_page(
            $this->plugin_name.'_admin_menu',                                     // The parent menu slug
            'ChordPress License',                                                 // The title to be displayed in the browser window for this page.
            'License',                                                            // The text to be displayed for this menu item
            'manage_options',                                                     // Which type of users can see this menu item
            plugin_dir_path(__FILE__).'partials/chordpress-admin-license.php',    // The unique ID for this menu item or file reference
            null                                                                  // The name of the function to call when rendering this menu's page (null if file ref above)
         );

      }

      remove_submenu_page($this->plugin_name.'_admin_menu', $this->plugin_name.'_admin_menu');

	}

	/**
	 * Action Links (shown underneath the plugin name on the admin plugin page)
	 *
	 * @since    1.0.0
	 */
	public function action_links($actions, $file) {

      if ($file == $this->plugin_name.'/'.$this->plugin_name.'.php') {

         $settings = array('settings' => '<a href="admin.php?page='.$this->plugin_name.'%2Fadmin%2Fpartials%2Fchordpress-admin-options.php">' . __( 'Settings', 'chordpress' ) . '</a>');
         $actions = array_merge($settings, $actions);
         $documentation = array('documentation' => '<a href="https://support.lewe.com/docs/chordpress-manual/" target="_blank">' . __( 'Documentation', 'chordpress' ) . '</a>');
         $actions = array_merge($documentation, $actions);

      }

      return $actions;

	}

	/**
	 * Meta Links (shown underneath the plugin description on the admin plugin page)
	 *
	 * @since    1.0.0
	 */
	public function meta_links($links, $file) {

      if ( $file == $this->plugin_name.'/'.$this->plugin_name.'.php' ) {

         $support_link = "https://support.lewe.com";
         $donate_link = "https://www.paypal.me/GeorgeLewe";
         $review_link = "https://wordpress.org/support/plugin/chordpress/reviews/?filter=5#new-post";
         $links[] = '<a href="' . esc_url( $support_link ) . '" target="_blank" title="' . __( 'Support', 'chordpress' ) . '"><span style="color:#999999;margin-right:4px;" class="dashicons dashicons-sos"></span>' . __( 'Support', 'chordpress' ) . '</a>';
         if (!CHORDPRESS_LICENSE_REQUIRED) $links[] = '<a href="' . esc_url( $donate_link ) . '" target="_blank" title="' . __( 'Donate', 'chordpress' ) . '"><span style="color:#999999;margin-right:4px;" class="dashicons dashicons-heart"></span>' . __( 'Donate', 'chordpress' ) . '</a>';
         $links[] = '<a href="' . esc_url( $review_link ) . '" target="_blank"><span style="color:#999999;margin-right:4px;" class="dashicons dashicons-thumbs-up"></span>' . __( 'Rate and Review!', 'chordpress' ) . '</a>';
         
      }

      return $links;

	}

}
