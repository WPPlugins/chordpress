<?php

/**
 * Chord rendering functions of the plugin
 *
 * @link       https://www.lewe.com
 * @since      2.1.0
 *
 * @package    ChordPress
 * @subpackage ChordPress/includes
 */

/**
 * Chord rendering functions of the plugin.
 *
 * This class is based open source Javascript chord rendering module "Vexchords"
 * (https://github.com/0xfe/vexchords).
 *
 * @package    ChordPress
 * @subpackage ChordPress/includes
 * @author     George Lewe <george@lewe.com>
 */
class ChordPress_Chord {

	/**
	 * Background color.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $bgColor    Default background color in CSS hex, e.g. #FFFFFF.
	 */
   private $bgColor;

	/**
	 * Canvas height.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      integer    $canvasHeight    Canvas height in pixel.
	 */
   private $canvasHeight;

	/**
	 * Canvas width.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      integer    $canvasWidth    Canvas width in pixel.
	 */
   private $canvasWidth;

	/**
	 * Circle radius.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      integer    $circleRadius    Circle radius in pixel (canvasWidth/20).
	 */
   private $circleRadius;

	/**
	 * Chord.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      array    $chord    The chord. Array of chord properties.
	 */
   private $chord;

	/**
	 * Default color.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $defaultColor    Default color in CSS hex, e.g. #666666.
	 */
   private $defaultColor;

	/**
	 * DIV styles.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $divStyle    CSS styles of the chord div.
	 */
   private $divStyles;

	/**
	 * Font Family.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $fontFamily    CSS font family.
	 */
   private $fontFamily;

	/**
	 * Font Size.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $fontSize    CSS font size.
	 */
   private $fontSize;

	/**
	 * Font Style.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $fontStyle    CSS font family.
	 */
   private $fontStyle;

	/**
	 * Font Weight.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $fontWeight    CSS font weight.
	 */
   private $fontWeight;

	/**
	 * Fret color.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $fretColor    Fret color. Overwrites default color.
	 */
   private $fretColor;

	/**
	 * Fret width.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      integer    $fretWidth    Fret width in pixel.
	 */
   private $fretWidth;

	/**
	 * Label color.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $labelColor    Label color. Overwrites default color.
	 */
   private $labelColor;

	/**
	 * Label Weight.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $labelWeight    CSS label weight.
	 */
   private $labelWeight;

	/**
	 * Name.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $name    Name of the chord.
	 */
   private $name;

	/**
	 * CSS styles for the chord name.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $nameStyle    CSS styles for the chord name.
	 */
   private $nameStyle;

	/**
	 * Number of strings.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      integer    $numStrings    Number of strings to show.
	 */
   private $numStrings;

	/**
	 * Position. Fret to start render at.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      integer    $position    Position. Fret to start render at.
	 */
   private $position;

	/**
	 * String color.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $stringColor    String color. Overwrites default color.
	 */
   private $stringColor;

	/**
	 * String width.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      integer    $stringWidth    String width in pixel.
	 */
   private $stringWidth;

	/**
	 * Stroke color.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $strokeColor    Stroke color. Overwrites default color.
	 */
   private $strokeColor;

	/**
	 * Text color.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $textColor    Stroke color. Overwrites default color.
	 */
   private $textColor;

	/**
	 * Title.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $name    Full title of the chord.
	 */
   private $title;

	/**
	 * CSS styles for the chord title.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $titleStyle    CSS styles for the chord title.
	 */
   private $titleStyle;

	/**
	 * Tuning.
	 *
    * @since    2.1.0
	 * @access   private
	 * @var      string    $tuning    The tuning of the strings.
	 */
   private $tuning;

   //--------------------------------------------------------------------------
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
   public function __construct() {

      /**
       * Set single chord defaults
       */
      $this->divStyle = "";
      $this->name = "";
      $this->nameStyle = "";
      $this->position = 0;
      $this->tuning = "['E', 'A', 'D', 'G', 'B', 'E']";
      $this->barres = array();

      $this->chord = array(

         '1' => array('fret' => "'x'", 'label' => ''),
         '2' => array('fret' => "'x'", 'label' => ''),
         '3' => array('fret' => "'x'", 'label' => ''),
         '4' => array('fret' => "'x'", 'label' => ''),
         '5' => array('fret' => "'x'", 'label' => ''),
         '6' => array('fret' => "'x'", 'label' => '')

      );

      /**
       * Get global chord options or set defaults
       */
      if (!$this->bgColor = get_option('chordpress_text_chord_bg_color')) $this->bgColor = '';
      if (!$this->canvasHeight = get_option('chordpress_text_chord_canvas_height')) $this->canvasHeight = 180;
      if (!$this->canvasWidth = get_option('chordpress_text_chord_canvas_width')) $this->canvasWidth = 220;
      if (!$this->circleRadius = get_option('chordpress_text_chord_circle_radius')) $this->circleRadius = 5;
      if (!$this->defaultColor = get_option('chordpress_text_chord_default_color')) $this->defaultColor = '#666666';
      if (!$this->divStyle = get_option('chordpress_text_chord_div_style')) $this->divStyle = 'text-align:center;';
      if (!$this->fontFamily = get_option('chordpress_text_chord_font_family')) $this->fontFamily = '';
      if (!$this->fontSize = get_option('chordpress_text_chord_font_size')) $this->fontSize = '';
      if (!$this->fontStyle = get_option('chordpress_text_chord_font_style')) $this->fontStyle = '';
      if (!$this->fontWeight = get_option('chordpress_text_chord_font_weight')) $this->fontWeight = '';
      if (!$this->fretColor = get_option('chordpress_text_chord_fret_color')) $this->fretColor = '';
      if (!$this->fretWidth = get_option('chordpress_text_chord_fret_width')) $this->fretWidth = 1;
      if (!$this->labelColor = get_option('chordpress_text_chord_label_color')) $this->labelColor = '';
      if (!$this->labelWeight = get_option('chordpress_text_chord_label_weight')) $this->labelWeight = '';
      if (!$this->nameStyle = get_option('chordpress_text_chord_name_style')) $this->nameStyle = 'font-size:1.2em;text-align:center;';
      if (!$this->numStrings = get_option('chordpress_text_chord_num_strings')) $this->numStrings = 6;
      if (!$this->stringColor = get_option('chordpress_text_chord_string_color')) $this->stringColor = '';
      if (!$this->stringWidth = get_option('chordpress_text_chord_string_width')) $this->stringWidth = 1;
      if (!$this->strokeColor = get_option('chordpress_text_chord_stroke_color')) $this->strokeColor = '';
      if (!$this->textColor = get_option('chordpress_text_chord_text_color')) $this->textColor = '';
      if (!$this->titleStyle = get_option('chordpress_text_chord_title_style')) $this->titleStyle = 'font-size:1.2em;text-align:center;';

      if ($this->customTuning = get_option('chordpress_text_chord_tuning')) $this->setProperty('tuning',$customTuning);

   }

   //--------------------------------------------------------------------------
   /**
    * Build the chord parameter for Vexchords
    *
    * @since     2.1.0
    * @param     boolean    $simple    If true, just return basic chord block
    * @return    string
    */    
   public function buildChordParmeters($simple=false) {

      $output = "{\n";

      /**
       * Chord name
       */
      if (strlen($this->name)) $output .= "         name: '".$this->name."',\n";


      /**
       * Chord
       */
      $output .= "         chord: [\n";

      /**
       * Loop through each string note
       */
      foreach ($this->chord as $key => $note) {

         $output .= "            [";
         $output .= $key.",".$note['fret']; // TODO
         if (strlen($note['label'])) $output .= ",'" . $note['label'] ."'";
         $output .= "],\n";

      }
      $output = rtrim($output,",\n");
      $output .= "\n         ],\n";

      /**
       * Position
       */
      $output .= "         position: ".$this->position.",\n";

      /**
       * Barres
       */
      if (!empty($this->barres)) {

         $output .= "         barres: [\n";

         foreach ($this->barres as $barre) {

            $output .= "            ".$barre.",\n";
   
         }
         $output = rtrim($output,",\n");
         $output .= "\n         ],\n";

      }

      /**
       * Barres
       */
      if (strlen($this->tuning)) {

         $output .= "         tuning: ".$this->tuning."\n";

      }
      $output .= "      },\n";

      if ($simple) {

         return $output;

      }

      /**
       * Options (always set)
       */
      $output .= "      {\n";
      $output .= "         height: ".$this->canvasHeight.",\n";
      $output .= "         width: ".$this->canvasWidth.",\n";
      $output .= "         circleRadius: ".$this->circleRadius.",\n";
      $output .= "         fretWidth: ".$this->fretWidth.",\n";
      $output .= "         numStrings: ".$this->numStrings.",\n";

      /**
       * Options (only if they have a value)
       */
      if (strlen($this->bgColor))      $output .= "         bgColor: '".$this->bgColor."',\n";
      if (strlen($this->defaultColor)) $output .= "         defaultColor: '".$this->defaultColor."',\n";
      if (strlen($this->fontFamily))   $output .= "         fontFamily: '".$this->fontFamily."',\n";
      if (strlen($this->fontSize))     $output .= "         fontSize: '".$this->fontSize."',\n";
      if (strlen($this->fontStyle))    $output .= "         fontStyle: '".$this->fontStyle."',\n";
      if (strlen($this->fontWeight))   $output .= "         fontWeight: '".$this->fontWeight."',\n";
      if (strlen($this->fretColor))    $output .= "         fretColor: '".$this->fretColor."',\n";
      if (strlen($this->labelColor))   $output .= "         labelColor: '".$this->labelColor."',\n";
      if (strlen($this->labelWeight))  $output .= "         labelWeight: '".$this->labelWeight."',\n";
      if (strlen($this->stringColor))  $output .= "         stringColor: '".$this->stringColor."',\n";
      if (strlen($this->stringWidth))  $output .= "         stringWidth: ".$this->stringWidth.",\n";
      if (strlen($this->strokeColor))  $output .= "         strokeColor: '".$this->strokeColor."',\n";
      if (strlen($this->textColor))    $output .= "         textColor: '".$this->textColor.",'\n";
      $output = rtrim($output,",\n");
      $output .= "\n      }\n";

      $output .= "   );\n";

      return $output;

   }
 
   //--------------------------------------------------------------------------
   /**
    * Add a barre to private array: barres.
    *
    * @since     2.1.0
    * @param     array    $value
    */    
    public function addBarre( $fromString, $toString, $fret ) {

      $from = $fromString;
      $to = $toString;

      if ($fromString < $toString) { 
         
         $from = $toString;
         $to = $fromString;
      
      }

      $this->barres[] = "{ fromString: ".$from.",toString: ".$to.",fret: ".$fret."}";

   }

   //--------------------------------------------------------------------------
   /**
    * Create Vexchord div and script.
    *
    * @since     2.1.0
    * @return    string             HTML Vexchord div and script.
    */    
    public function addVexchord() {

      $id = $this->random_string();

      $shortname = "";
      if (strlen($this->getProperty('name'))) $shortname = " ( ".$this->getProperty('name')." )";

      /** 
       * Build output
       */
      $output = "\n\n<!-- ChordPress Chord -->\n";
      $output .= "<div style=\"width:".$this->getProperty('canvasWidth')."px;\"><div style=\"".$this->getProperty('titleStyle').";\">".$this->getProperty('title').$shortname."</div>";
      $output .= "<div id=\"".$id."\" style=\"".$this->getProperty('divStyle')."\"></div></div>
<script>
   const draw".$id." = vexchords.draw;\n
   draw".$id."(
      '#".$id."',
      ".$this->buildChordParmeters()."</script>\n";

      return $output;

   }

   //--------------------------------------------------------------------------
   /**
    * Create Vexchord array with div and script.
    *
    * @since     2.1.0
    * @return    array             Vexchord array with divh and script.
    */    
   public function createBasicVexchord() {

      $vexchord = "      ".$this->buildChordParmeters(true)."\n";

      return $vexchord;

   }

   //--------------------------------------------------------------------------
   /**
    * Flip the string number. 1=6, 2=5, 3=4, 4=3, 5=2, 6=1
    *
    * @since     2.1.0
    * @param     integer    $string
    * @return    integer    Flipped string count 
    */    
   public function flipString( $string ) {

      switch($string) {

         case 1:
            return 6;

         case 2:
            return 5;

         case 3:
            return 4;

         case 4:
            return 3;

         case 5:
            return 2;

         case 6;
            return 1;

         default:
            return $string;

      }

   }

   //--------------------------------------------------------------------------
   /**
    * Get private property: barres.
    *
    * @since     2.1.0
    * @return    array
    */    
    public function getProperty($property) {

      return $this->{$property};

   }
 
   //--------------------------------------------------------------------------
   /**
    * Get private property: barres.
    *
    * @since     2.1.0
    * @param     string    $property    Prioperty name
    * @param     misc      $value       Value to set the property to
    */    
   public function setProperty($property, $value) {

      $this->{$property} = $value;

   }
 
   
   // ---------------------------------------------------------------------------
   /**
    * Pretty prints an array.
    *
    * @since    2.0.0
    * @param    array    $a    Array to print out pretty
    * @return   string
    */
   public function pretty_dump($a) {

      return highlight_string("<?php\n\$data =\n" . var_export($a, true) . ";\n?>");

   }
 
   /**
    * Generate random string.
    * 
    * @param  integer    $length    Length of random string to generate.
    * @return string                Random string
    */
   private function random_string( $length=10 ) {

      return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);

   }

}
