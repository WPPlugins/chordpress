<?php

/**
 * Public functionality of the plugin
 *
 * @link       https://www.lewe.com
 * @since      2.0.0
 *
 * @package    ChordPress
 * @subpackage ChordPress/public
 */

/**
 * Public functionality of the plugin.
 *
 * - Defines the plugin name and version
 * - Enqueues stylesheet and JavaScript
 * - Shortcode handlers
 *
 * @package    ChordPress
 * @subpackage ChordPress/includes
 * @author     George Lewe <george@lewe.com>
 */
class ChordPress_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the public stylesheets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in the Loader class as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader class will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chordpress-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the public javascripts.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in the Loader class as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loader class will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chordpress-public.js', array('jquery'), $this->version, null );

	}

   /**
    * Shortcode: helloworld
    * 
    * Arguments
    *   firstname=    First name
    *   lastname=     Last name
    * 
    * @param  array   $atts     Array of settings from shortcode
    * @param  string  $content  Content wrapped in shortcode
    * @return string            Formatted HTML to display on page
    */
   function shortcode_chordpress( $atts, $content = null, $tag='' ) {

      $CPR = new ChordPress_Renderer();
      $output = '';

      /**
       * Get shortcode arguments and change to lower case
       */
      $atts = array_change_key_case((array)$atts, CASE_LOWER);

      /**
       * Set shortcode argument defaults
       */
      $args = shortcode_atts(
         [
            'float' => 'none',
            'format' => 'yes',
            'hbnotation' => 'no',
            'interactive' => 'no',
            'transpose' => '0',
         ],
         $atts,
         $tag
      );      

      /** 
       * If no formatting is requested, get out of here and display the text as is
       */
      if ($args['format'] != 'yes') return "[chordpress]" . apply_filters('the_content', $content) . "[/chordpress]";

      /**
       * Check content
       */
      if (!is_null($content)) {

         /** 
          * Initiate ChordPress properties
          */
         $CPR->setFloat($args['float']);
         $CPR->setHBNotation($args['hbnotation']);
         $CPR->setInteractive($args['interactive']);
         $CPR->setTranspose($args['transpose']);
         
         /** 
          * Break content into separate lines
          */
         $lines = explode("\n",$content);
      
         /** 
          * Process the directives of the ChordPro text
          */
         $CPR->getDirectives($lines);
      
         /** 
          * Process and return the whole thing
          */
         $output = $CPR->displayText($lines);
   
      }

      return $output;

   }

}
