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
   public function __construct($plugin_name, $version) {

      $this->plugin_name = $plugin_name;
      $this->version = $version;
   }

   /**
    * Register the public stylesheets.
    *
    * @since    1.0.0
    */
   public function enqueue_styles() {

      wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/chordpress-public.css', array(), $this->version, 'all');
   }

   /**
    * Register the public javascripts.
    *
    * @since    1.0.0
    */
   public function enqueue_scripts() {

      wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/chordpress-public.js', array('jquery'), $this->version, null);
      wp_enqueue_script($this->plugin_name . '-vexchords', plugin_dir_url(__FILE__) . 'js/vexchords.js');

      wp_register_script('chordpress_prototype', plugin_dir_url(__FILE__) . "js/prototype.js", array('jquery'), false, true);
      wp_enqueue_script('chordpress_prototype');
      wp_register_script('chordpress_raphael', plugin_dir_url(__FILE__) . "js/raphael.js", array('jquery'), false, true);
      wp_enqueue_script('chordpress_raphael');
      wp_register_script('chordpress_jtab', plugin_dir_url(__FILE__) . "js/jtab.js", array('jquery'), false, true);
      wp_enqueue_script('chordpress_jtab');
      wp_register_script('chordpress_form', plugin_dir_url(__FILE__) . "js/form.js", array('jquery'), false, true);
      wp_enqueue_script('chordpress_form');
   }

   /**
    * Shortcode: chordpress.
    * 
    * Shortcode arguments
    *   float=          Rendered text div float style: none/left/right. Default: none.
    *   format=         Whether to render the text or not: yes/no. Default: yes.
    *   hbnotation=     Whether to use H/B notation or not: yes/no. Default: no.
    *   interactive=    Interactive elements on page: yes/no. Default: no.
    *   transpose=      Positive number of semitones to transpose the chords. Default: 0.
    * 
    * @param  array   $atts     Array of settings from shortcode
    * @param  string  $content  Content wrapped in shortcode
    * @return string            Formatted HTML to display on page
    */
   public function shortcode_chordpress($atts, $content = null, $tag = '') {

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

      $content = do_shortcode($content);

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
         $lines = explode("\n", $content);

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

   /**
    * Shortcode: chordpress-chord.
    * 
    * @param  array   $atts       Array of settings from shortcode
    * @param  string  $content    Content wrapped in shortcode
    * @return string              Formatted HTML to display on page
    */
   function shortcode_chordpress_chord($atts, $content = null, $tag = '') {

      $P = new ChordPress_Plugin();
      $C = new ChordPress_Chord();
      $chord = array();
      $id = $this->random_string();
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
            'chord' => '',
            'bgcolor' => '',
            'canvasheight' => '',
            'canvaswidth' => '',
            'circleradius' => '',
            'defaultcolor' => '',
            'divstyle' => '',
            'fontfamily' => '',
            'fontsize' => '',
            'fontstyle' => '',
            'fontweight' => '',
            'fretcolor' => '',
            'fretwidth' => '',
            'labelcolor' => '',
            'labelweight' => '',
            'namestyle' => '',
            'numstrings' => '',
            'stringcolor' => '',
            'stringwidth' => '',
            'strokecolor' => '',
            'textcolor' => '',
            'titlestyle' => '',
         ],
         $atts,
         $tag
      );

      if (strlen($args['chord']) && $post = get_post($args['chord'])) {

         /**
          * Retrieve meta data from chord post
          */
         $guitar_chord_shortname     = get_post_meta($args['chord'], 'guitar_chord_shortname', 1);
         $guitar_chord_position      = get_post_meta($args['chord'], 'guitar_chord_position', 1);
         $guitar_chord_numstrings    = get_post_meta($args['chord'], 'guitar_chord_numstrings', 1);
         $guitar_chord_tuning        = get_post_meta($args['chord'], 'guitar_chord_tuning', 1);
         $guitar_chord_single_frets  = get_post_meta($args['chord'], 'guitar_chord_single_frets', 1);
         $guitar_chord_single_labels = get_post_meta($args['chord'], 'guitar_chord_single_labels', 1);
         $guitar_chord_barre_frets   = get_post_meta($args['chord'], 'guitar_chord_barre_frets', 1);

         /**
          * Retrieve shortcode parameters
          */
         if (strlen($args['bgcolor']))      $C->setProperty('bgColor', $args['bgcolor']);
         if (strlen($args['canvasheight'])) $C->setProperty('canvasHeight', $args['canvasheight']);
         if (strlen($args['canvaswidth']))  $C->setProperty('canvasWidth', $args['canvaswidth']);
         if (strlen($args['circleradius'])) $C->setProperty('circleRadius', $args['circleradius']);
         if (strlen($args['defaultcolor'])) $C->setProperty('defaultColor', $args['defaultcolor']);
         if (strlen($args['divstyle']))     $C->setProperty('divStyle', $args['divstyle']);
         if (strlen($args['fontfamily']))   $C->setProperty('fontFamily', $args['fontfamily']);
         if (strlen($args['fontsize']))     $C->setProperty('fontSize', $args['fontsize']);
         if (strlen($args['fontstyle']))    $C->setProperty('fontStyle', $args['fontstyle']);
         if (strlen($args['fontweight']))   $C->setProperty('fontWeight', $args['fontweight']);
         if (strlen($args['fretcolor']))    $C->setProperty('fretColor', $args['fretcolor']);
         if (strlen($args['fretwidth']))    $C->setProperty('fretWidth', $args['fretwidth']);
         if (strlen($args['labelcolor']))   $C->setProperty('labelColor', $args['labelcolor']);
         if (strlen($args['labelweight']))  $C->setProperty('labelWeight', $args['labelweight']);
         if (strlen($args['namestyle']))    $C->setProperty('nameStyle', $args['namestyle']);
         if (strlen($args['numstrings']))   $C->setProperty('numStrings', $args['numstrings']);
         if (strlen($args['stringcolor']))  $C->setProperty('stringColor', $args['stringcolor']);
         if (strlen($args['stringwidth']))  $C->setProperty('stringWidth', $args['stringwidth']);
         if (strlen($args['strokecolor']))  $C->setProperty('strokeColor', $args['strokecolor']);
         if (strlen($args['textcolor']))    $C->setProperty('textColor', $args['textcolor']);
         if (strlen($args['titlestyle']))   $C->setProperty('titleStyle', $args['titlestyle']);

         /**
          * Build the chord diagram
          */
         $C->setProperty('title', $post->post_title);
         $C->setProperty('name', $guitar_chord_shortname);
         $C->setProperty('position', $guitar_chord_position);
         $C->setProperty('numStrings', $guitar_chord_numstrings);

         /**
          * Tuning
          */
         $tuning_array = explode(",", $guitar_chord_tuning);
         $tuning = "[";
         foreach ($tuning_array as $note) {

            $tuning .= "'" . $note . "',";
         }

         $tuning = rtrim($tuning, ",");
         $tuning .= "]";
         $C->setProperty('tuning', $tuning);

         /**
          * Chord
          */
         if (strlen($guitar_chord_single_frets)) {

            $single_frets = explode(",", $guitar_chord_single_frets);
            $single_frets = array_reverse($single_frets);

            if (strlen($guitar_chord_single_labels)) {

               $single_labels = explode(",", $guitar_chord_single_labels);
               $single_labels = array_reverse($single_labels);
            } else {

               $single_labels = array('', '', '', '', '', '',);
            }

            for ($i = 1; $i <= $guitar_chord_numstrings; $i++) {

               $my_chord[$i]['fret'] = $single_frets[$i - 1];
               $my_chord[$i]['label'] = $single_labels[$i - 1];
            }

            $C->setProperty('chord', $my_chord);
         }

         /**
          * Barre
          */
         if (strlen($guitar_chord_barre_frets)) {

            $barre_array = explode(",", $guitar_chord_barre_frets);
            $C->addBarre($C->flipString($barre_array[0]), $C->flipString($barre_array[1]), $barre_array[2]);
         }

         // if($postid = $this->find_guitar_chord_post("E")) $postid = "Found"; else $postid = "Not found";
         // $output = '<p id="cpress-chord-'.$post->ID.'-modal-button" class="cpress-pointer">'.$C->getProperty('name').'</p>';
         // $output .= $this->create_modal_chord($post->ID, $C->addVexchord());

         $output .= $C->addVexchord();
      } else {

         /**
          * Chord not found. Create warning message.
          */
         $alert_style = 'warning';
         $alert_title = esc_html(__('Warning', 'chordpress'));
         $alert_text = esc_html(__('There is no chord record with the ID ' . $args['chord'] . '.', 'chordpress')) . ' ' . esc_html(__('Please create a proper post for this chord.', 'chordpress'));
         $output = $P->alert($alert_style, $alert_title, 'h4', $alert_text, true, '98%');
      }

      /** 
       * Return output
       */
      return $output;
   }

   /**
    * Shortcode: chordpress-jtab.
    * 
    * jTab phrase examples:
    * E / / / | Am / B / || 
    * A x.0.2.2.3.0 $2 2 0 2 3 5/3 A B Cdim
    * Bm $3 4 4h5p3h4 5 $2 3 5 7 7h8p7 5/7 | A $4 7 9 $3 7 6 $5 9 $4 7h9 7 $5 9\7 5/7 ||
    *
    * @param  array   $atts       Array of settings from shortcode
    * @param  string  $content    Content wrapped in shortcode
    * @return string              Formatted HTML to display on page
    */
   function shortcode_chordpress_jtab($atts, $content = null, $tag = '') {

      /**
       * Get shortcode arguments and change to lower case
       */
      $atts = array_change_key_case((array)$atts, CASE_LOWER);

      /**
       * Set shortcode argument defaults
       */
      $args = shortcode_atts(
         [
            'buttontext' => 'Show jTab',
            'color' => '',
            'form' => 'false',
            'id' => '',
            'phrase' => '',
         ],
         $atts,
         $tag
      );


      /**
       * Provide option to id the object
       */
      $divid = "";
      if (strlen($args['id'])) {

         $divid = " id='" . $args['id'] . "' ";
      }

      /**
       * Overwrite divid cause form script expects id=jtab
       */
      if ($args['form'] == 'true') {

         $divid = " id='jtab' ";
      }

      /**
       * Allow coloring
       */
      $color = "#000000"; // Default: black
      if (strlen($args['color'])) {

         $color = " style='color:" . $args['color'] . ";' ";
      }


      $output = "\n\n<!-- ChorDpress jTab Shortcode Beginn -->\n";
      $output .= '<div class=\'jtab\'' . $color . $divid . '>' . $args['phrase'] . '</div>';

      if ($args['form'] == 'true') {

         $output = '
            <input id="jtab_phrase" type="text" class="jtab-input" size="40" value="' . $args['phrase'] . '">
            <input id="jtab_button" type="button" class="cpress-btn cpress-btn-primary" value="' . $args['buttontext'] . '">
            ' . $output;
      }

      $output .= "\n<!-- ChorDpress jTab Shortcode End -->\n";

      return $output;
   }

   /**
    * Create a modal guitar chord dialog.
    * 
    * @param  integer    $postid    ID of the chord post
    * @param  string     $chord     Vexchord HTML
    * @return string                Modal chord dialog HTML
    */
   private function create_modal_chord($postid, $chord) {

      $modal_chord = '<!-- <button id="cpress-chord-' . $postid . '-modal-button">Click here to trigger the modal!</button> -->
      <div id="cpress-chord-' . $postid . '-modal" class="cpress-modal">
          <div class="cpress-modal-content">
              <span id="cpress-chord-' . $postid . '-modal-close" class="cpress-modal-close">&times;</span>
              ' . $chord . '
          </div>
      </div>
      <script>
      var cpress_' . $postid . '_modal = document.getElementById("cpress-chord-' . $postid . '-modal");
      var cpress_' . $postid . '_btn = document.getElementById("cpress-chord-' . $postid . '-modal-button");
      var cpress_' . $postid . '_close = document.getElementById("cpress-chord-' . $postid . '-modal-close");

      cpress_' . $postid . '_btn.onclick = function() {
         cpress_' . $postid . '_modal.style.display = "block";
      }

      cpress_' . $postid . '_close.onclick = function() {
         cpress_' . $postid . '_modal.style.display = "none";
      }

      window.onclick = function(event) {
         if (event.target == cpress_' . $postid . '_modal) {
            cpress_' . $postid . '_modal.style.display = "none";
         }
      } 
      </script>';

      return $modal_chord;
   }


   /**
    * Find a guitar chord type post by shortname.
    * 
    * @param  string    $shortname    Shortname of the guitar post to look for.
    * @return integer                 Post ID or NULL
    */
   private function find_guitar_chord_post($shortname) {

      $query_args = array(
         'posts_per_page'   => 1,
         'post_type'        => 'guitar_chord',
         'meta_key'         => 'guitar_chord_shortname',
         'meta_value'       => $shortname
      );
      $query_result = new WP_Query($query_args);

      return $query_result->posts[0]->ID;
   }

   /**
    * Generate random string.
    * 
    * @param  integer    $length    Length of random string to generate.
    * @return string                Random string
    */
   private function random_string($length = 10) {

      return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
   }

   // ---------------------------------------------------------------------------
   /**
    * Pretty prints an array.
    *
    * @since    2.0.0
    * @param    array    $a    Array to print out pretty
    * @return   string
    */
   private function pretty_dump($a) {

      return highlight_string("<?php\n\$data =\n" . var_export($a, true) . ";\n?>");
   }
}
