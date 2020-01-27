<?php 
/*
Plugin Name: ChordPress
Plugin URI:  http://www.lewe.com/chordpress-for-wordpress
Description: This plugin renders ChordPro formatted text in WordPress sites.
Version:     1.4.0
Date:        2020-01-05
Author:      George Lewe
Author URI:  http://www.lewe.com
License:     GPLv3
License URI: https://www.gnu.org/licenses/gpl.html
*/
/*
CREDITS
-------
This plugin derived from the plugin "ChordsAndLyrics" by Ron Lisle (http://Lisles.net/)
that displays the chords and lyrics part of a ChordPro based syntax. Thank you, Ron,
for providing your awesome work to the musician WordPress users and authors.

The beautiful plugin icon was created by Ahkâm:
https://www.freeiconspng.com/img/17579

CHORDPRESS
----------
The ChordPress plugin reads and processes ChordPro formatted text, including several
content and formatting directives, and renders it for WordPress sites.
Details about the ChordPro standard can be found here:
https://www.chordpro.org/chordpro/index.html

LICENSE
-------
This program is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License v3:
https://www.gnu.org/licenses/gpl.html

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/


/** ===========================================================================
 * No direct access
 */
defined( 'ABSPATH' ) or die( 'No direct access allowed!' );

/** ===========================================================================
 * Options Page
 */
include("options-page.php");

/** ===========================================================================
 * Plugin Action Links
 */
add_filter( 'plugin_action_links', 'chordpress_action_links', 10, 5 );
function chordpress_action_links( $actions, $plugin_file ) 
{
   static $plugin;
   if (!isset($plugin)) $plugin = plugin_basename(__FILE__);
   if ($plugin == $plugin_file)
   {
      $settings = array('settings' => '<a href="options-general.php?page=chordpress">' . __('Settings', 'General') . '</a>');
      $actions = array_merge($settings, $actions);
      $documentation = array('documentation' => '<a href="https://support.lewe.com/docs/chordpro-for-wordpress-manual/">' . __('Documentation', 'General') . '</a>');
      $actions = array_merge($documentation, $actions);
   }
   return $actions;
}

/** ===========================================================================
 * Plugin Meta Links
 */
add_filter( 'plugin_row_meta', 'chordpress_meta_links', 10, 2 );
function chordpress_meta_links( $links, $file )
{
   if ( $file === 'chordpress/chordpress.php' )
   {
      $support_link = "https://support.lewe.com";
      $donate_link = "https://www.paypal.me/GeorgeLewe";
      $review_link = "https://wordpress.org/support/plugin/chordpress/reviews/?filter=5#new-post";

      $links[] = '<a href="' . esc_url( $support_link ) . '" target="_blank" title="' . __( 'Support' ) . '"><span style="color:#ffb000;" class="dashicons dashicons-sos"></span>' . __( 'Support' ) . '</a>';
      $links[] = '<a href="' . esc_url( $donate_link ) . '" target="_blank" title="' . __( 'Donate' ) . '"><span style="color:#990000;" class="dashicons dashicons-heart"></span>' . __( 'Donate' ) . '</a>';
      $links[] = '<a href="' . esc_url( $review_link ) . '" target="_blank"><span style="color:#0060b0;" class="dashicons dashicons-thumbs-up"></span>' . __( 'Rate and Review!', 'chordpress' ) . '</a>';
   }
   return $links;
}

// ============================================================================
/*
 * Scripts and Stylesheets
 */
function chordpress_scripts()
{
	wp_enqueue_style('chordpress', plugin_dir_url(__FILE__).'assets/css/chordpress.css',null,'4.4.1');
   wp_enqueue_script('chordpress', plugin_dir_url(__FILE__).'assets/js/chordpress.js',array('jquery'),null);
}
add_action('wp_enqueue_scripts','chordpress_scripts');

// ============================================================================
/*
 * TinyMCE Buttons
 */
add_action( 'after_setup_theme', 'cpr_theme_setup' );
if ( ! function_exists( 'cpr_theme_setup' ) ) {
   function cpr_theme_setup(){
      add_action( 'init', 'cpr_buttons' );
   }
}

if ( !function_exists('cpr_buttons') ) {
   function cpr_buttons() {
      if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
         return;
      }
      if ( get_user_option( 'rich_editing' ) !== 'true' ) {
         return;
      }
      add_filter( 'mce_external_plugins', 'cpr_add_buttons' );
      add_filter( 'mce_buttons', 'cpr_register_buttons' );
   }
}

if ( !function_exists('cpr_add_buttons') ) {
   function cpr_add_buttons( $plugin_array ) {
      $plugin_array['cprbtn'] = plugins_url( 'assets/js/tinymce_buttons.js' , __FILE__ );
      return $plugin_array;
   }
}

if ( !function_exists('cpr_register_buttons') ) {
   function cpr_register_buttons( $buttons ) {
      array_push( $buttons, '|' );
      array_push( $buttons, 'cprbtn' );
      return $buttons;
   }
}

/** ===========================================================================
 * Shortcode Handler
 * 
 * Shortcode: [chordpress param=value ...] ... [/chordpress]
 * 
 * Parameters overwriting the default when used:
 * 
 *   float=        Forces the rendered text block to float left or right
 *   format=       Format the output or not. Default = yes.
 *   hbnotation=   Interprete chords as German notation (AHCDEFG, B=Bb). 
 *                 Default = no.
 *                 https://sayandsound.lewe.com/note-h/
 *   interactive=  Enables interactive mode. A listbox will be shown on the right
 *                 of the title that allows you to transpose the chords on the fly.
 *   transpose=    Positive or negative number to transpose up/down in semitones
 * 
 * @param  array   $atts    Array of settings from shortcode
 * @param  string  content  Content wrapped in shortcode
 * @return string  Formatted HTML to display on page
 */
function chordpress_shortcode($atts, $content = null)
{
   $CPR = new ChordPress();

   extract(shortcode_atts(array
      (
         'float' => 'none',
         'format' => 'yes',
         'hbnotation' => 'no',
         'interactive' => 'no',
         'transpose' => '0',
      ),
      $atts
   ));

   if($format != 'yes') return "[chordpress]" . $content . "[/chordpress]";
   
   $CPR->setFloat($float);
   $CPR->setHBNotation($hbnotation);
   $CPR->setInteractive($interactive);
   $CPR->setTranspose($transpose);
   
   // 
   // Break content into separate lines
   //
   $lines = explode("\n",$content);

   // 
   // Get the directives of the ChordPro text
   //
   $CPR->getDirectives($lines);

   // 
   // Parse content line by line
   //
   return $CPR->displayText($lines);
}
add_shortcode('chordpress', 'chordpress_shortcode');

/** ===========================================================================
 *  ChordPress Class providing methods and attributes to format a ChordPro
 *  formatted text into HTML to be used in WordPress sites.
 * 
 */
class ChordPress
{
   private $displayHBNotation;
   private $hideAlbum;
   private $hideArtist;
   private $hideChords;
   private $hideComments;
   private $hideComposer;
   private $hideTitle;
   private $hideSubtitle;
   private $hideYear;

   private $titleLevel;
   private $subtitleLevel;
   private $metaLevel;
   private $metaStyle;
   private $chordTextcolor;
   private $chordTextsize;
   private $lyricsTextcolor;
   private $lyricsTextsize;
   private $commentStyle;
   private $chorusStyle;
   private $verseStyle;

   private $float;
   private $hbNotation;
   private $interactive;
   private $transpose;

   private $strDebug;

   //
   // Array of supported ChordPro directives
   //
   private $arrDirectives = array (
      'album' => '',
      'artist' => '',
      'composer' => '',
      'subtitle' => '',
      'title' => '',
      'year' => '',
   );
   
   /** ------------------------------------------------------------------------
    * Constructs the ChordPress class instance
    */    
   public function __construct()
   {
      $options = get_option( 'chordpress_settings' );

      //
      // Shortcode Defaults
      //
      $this->float = 'no';
      $this->hbNotation = 'no';
      $this->interactive = 'no';
      $this->transpose = 0;

      //
      // Get options
      //
      if(!$this->hideAlbum = $options['chordpress_checkbox_hide_album']) $this->hideAlbum = 0;
      if(!$this->hideArtist = $options['chordpress_checkbox_hide_artist']) $this->hideArtist = 0;
      if(!$this->hideChords = $options['chordpress_checkbox_hide_chords']) $this->hideChords = 0;
      if(!$this->hideComments = $options['chordpress_checkbox_hide_comments']) $this->hideComments = 0;
      if(!$this->hideComposer = $options['chordpress_checkbox_hide_composer']) $this->hideComposer = 0;
      if(!$this->hideSubtitle = $options['chordpress_checkbox_hide_subtitle']) $this->hideSubtitle = 0;
      if(!$this->hideTitle = $options['chordpress_checkbox_hide_title']) $this->hideTitle = 0;
      if(!$this->hideYear = $options['chordpress_checkbox_hide_year']) $this->hideYear = 0;
      if(!$this->displayHBNotation = $options['chordpress_checkbox_hb_notation']) $this->displayHBNotation = 0;

      if(!$this->titleLevel = $options['chordpress_select_title_level']) $this->titleLevel = 'h1';
      if(!$this->subtitleLevel = $options['chordpress_select_subtitle_level']) $this->subtitleLevel = 'h2';
      if(!$this->metaLevel = $options['chordpress_select_meta_level']) $this->metaLevel = 'h3';
      if(!$this->metaStyle = $options['chordpress_text_meta_style']) $this->metaStyle = '';
      if(!$this->chordTextcolor = $options['chordpress_text_chord_textcolor']) $this->chordTextColor = 'inherit';
      if(!$this->chordTextsize = $options['chordpress_text_chord_textsize']) $this->chordTextSize = 'inherit';
      if(!$this->lyricsTextcolor = $options['chordpress_text_lyrics_textcolor']) $this->lyricsTextColor = 'inherit';
      if(!$this->lyricsTextsize = $options['chordpress_text_lyrics_textsize']) $this->lyricsTextSize = 'inherit';
      if(!$this->commentStyle = $options['chordpress_text_comment_style']) $this->commentStyle = '';
      if(!$this->chorusStyle = $options['chordpress_text_chorus_style']) $this->chorusStyle = '';
      if(!$this->verseStyle = $options['chordpress_text_verse_style']) $this->verseStyle = '';
   }

   /** ------------------------------------------------------------------------
    * Gets the ChordPro directives and stores the supported ones in an array
    *
    * @param string  $text  Text inside shortcode wrapper
    */    
   public function getDirectives( $text )
   {
      foreach( $text as $line )
      {
         $line = trim($this->stripTags($line));
         if($line[0] == '{')
         {
            // Line starts with "{": It's a directive
            // Get directive name and value and store
            //
            $arrDirective = explode(":",$line);
            $directiveName = strtolower(str_replace('{','',trim($arrDirective[0])));
            $directiveValue = substr(trim($arrDirective[1]),0,strpos(trim($arrDirective[1]),'}'));

            //
            // Process directive alternatives
            //
            switch (strtolower($directiveName))
            {
               case "t":
                  $directiveName = 'title';
                  break;

               case "st":
                  $directiveName = 'subtitle';
                  break;

               case "meta":
                  //
                  // {meta: title Heart of Gold}
                  // We have split the "meta:" off already.
                  // Now comes the part after.
                  // We only handle the supported directives ($this->arrDirectives)
                  //
                  $arrMetaDirective = explode(" ",$directiveValue,2);
                  foreach ($this->arrDirectives as $key => $value)
                  {
                     if(trim($arrMetaDirective[0]) == $key)
                     {
                        $directiveName = $key;
                        $directiveValue = trim($arrMetaDirective[1]);
                     }
                  }
                  break;
            }

            //
            // Store directive in directives array for later use
            //
            if(array_key_exists($directiveName,$this->arrDirectives))
            {
               $this->arrDirectives[$directiveName] = $directiveValue;
            }
         }
      }
   }

   /** ------------------------------------------------------------------------
    * Creates the formatted HTML of the ChordPro text
    *
    * HTML Structure
    *
    * <div class="cpress">
    *    <h1>{title}</h1>
    *    <h2>{subtitle}</h2>
    *    <h3>
    *       Album: {album}
    *       Artist: {artist}<br>
    *       Composer: {composer}<br>
    *       Year: {year}
    *    </h3>
    *    <div class="cpress-chorus/verse">
    *       <div class="cpress-line">
    *          <div class="cpress-line-section">
    *             <div class="chord">C#</div>
    *             <div class="lyric">Lyrics</div>
    *          </div>
    *          ...
    *       </div>
    *       ...
    *    </div>
    * </div>
    *
    * @param   string  $text  Text inside shortcode wrapper
    * @return  string  Formatted HTML of the ChordPro text
    */
   public function displayText( $text )
   {
      //
      // Start of ChordPress wrapper
      //
      $returnText = "\n\n
      <!-- ChordPress Start -->
      <div id=\"%cpressID%\" class=\"cpress\">\n";

      //
      // Style sheet
      //
      $returnText .= 
      '<style>
         div.cpress { float: '.$this->float.'; }
         div.cpress_line { margin: 1em 0 1em 0; }
         div.cpress_line_section { display: inline; float: left; }
         div.cpress_line_section .chord { color: '.$this->chordTextcolor.'; font-size: '.$this->chordTextsize.'; }
         div.cpress_line_section .lyric { color: '.$this->lyricsTextcolor.'; font-size: '.$this->lyricsTextsize.'; }
         div.cpress_chorus { '.$this->chorusStyle.' }
         div.cpress_verse { '.$this->verseStyle.' }
         div.cpress_clear { clear: both; }
         div.cpress_interaction { float: right; }
         div.cpress_interaction select { width: auto; }
         span.cpress_comment { '.$this->commentStyle.' }
         span.cpress_meta { '.$this->metaStyle.' }
      </style>';
               
      //
      // DEBUG
      //
      // $returnText .= '<pre>' . $this->strDebug . '</pre>';
      // $returnText .= '<pre>' . count($text) . '</pre>';

      //
      // Interactive transpose listbox
      //
      if (strtolower($this->interactive) == "yes")
      {
         $returnText .= "<div class=\"cpress_interaction\">Transpose up:
            <select id=\"selTranspose\" onchange=\"transposeChords();return false;\">
               <option value=\"0\">0</option>
               <option value=\"1\">1</option>
               <option value=\"2\">2</option>
               <option value=\"3\">3</option>
               <option value=\"4\">4</option>
               <option value=\"5\">5</option>
               <option value=\"6\">6</option>
               <option value=\"7\">7</option>
               <option value=\"8\">8</option>
               <option value=\"9\">9</option>
               <option value=\"10\">10</option>
               <option value=\"11\">11</option>
            </select><br>
            <button onclick=\"printCpress('%cpressID%');return false;\" style=\"float:right;margin-top:8px;\">Print</button>
         </div>";
      }

      //
      // Print header if directives exist
      //
      if(strlen($this->arrDirectives['title']) && !$this->hideTitle)
      {
         $cpressID = str_replace(' ', '-', strtolower(trim($this->arrDirectives['title'])));
         $returnText = str_replace('%cpressID%', $cpressID, $returnText);
         $returnText .= '<'.$this->titleLevel.'>'.$this->arrDirectives['title'].'</'.$this->titleLevel.'>';
      }
      else {
         $cpressID = 'ccpress-id';
         $returnText = str_replace('%cpressID%', $cpressID, $returnText);
      }

      if(strlen($this->arrDirectives['subtitle']) && !$this->hideSubtitle)
      {
         $returnText .= '<'.$this->subtitleLevel.'>'.$this->arrDirectives['subtitle'].'</'.$this->subtitleLevel.'>';
      }

      if(strlen($this->arrDirectives['album']) || 
         strlen($this->arrDirectives['artist']) ||
         strlen($this->arrDirectives['composer']) ||
         strlen($this->arrDirectives['year'])
         )
      {
         $returnText .= '<'.$this->metaLevel.'><span class="cpress_meta">';
         if(strlen($this->arrDirectives['composer']) && !$this->hideComposer) $returnText .= 'Composer: '.$this->arrDirectives['composer'].'<br>';
         if(strlen($this->arrDirectives['artist']) && !$this->hideArtist)     $returnText .= 'Artist: '.$this->arrDirectives['artist'].'<br>';
         if(strlen($this->arrDirectives['year']) && !$this->hideYear)         $returnText .= 'Year: '.$this->arrDirectives['year'].'<br>';
         if(strlen($this->arrDirectives['album']) && !$this->hideAlbum)       $returnText .= 'Album: '.$this->arrDirectives['album'].'<br>';
         $returnText .= '</span></'.$this->metaLevel.'>';
      }

      //
      // Go through each line and process it
      //
      foreach( $text as $line )
      {
         $returnText .= $this->formatAndDisplayLine($line);
      }

      //
      // End of ChordPress wrapper
      //
      $returnText .= "</div>\n
      <!-- ChordPress End -->\n\n";
      return $returnText;
   }
               
   /** ------------------------------------------------------------------------
    * Processes a single of the ChordPro text and returns the formatted HTML
    *
    * @param  string   $line     Single line from inside shortcode wrapper
    * @return string   Formatted HTML
    */    
   public function formatAndDisplayLine ($line)
   {
      $returnText = "";
      $startOfChorus = false;
      $endOfChorus = false;
      $startOfVerse = false;
      $endOfVerse = false;

      //
      // Array of chords
      //
      $arrChords = array();

      //
      // Array of corresponding lyrics starting at a chord and ending prior
      // to the next chord or the end-of-line.
      //
      $arrLyrics = array();

      //
      // Now check what this line is all about
      //
      if(substr_count($line,"[") == 0)
      {
         //
         // No chords on this line
         //
         if($line[0] == '#')
         {
            // Line starts with "#": It's a comment line => skip
         }
         else if($line[0] == '{' || strpos($line,'{'))
         {
            // Line starts with "{" or has one in it: It's a directive
            // Some of them need consideration for the output
            //
            $directive = strstr($line,'{'); // Remove all before the {
            $arrDirective = explode(":",$directive);
            $directiveName = strtolower(str_replace('{','',trim($arrDirective[0])));
            $directiveValue = substr(trim($arrDirective[1]),0,strpos(trim($arrDirective[1]),'}'));

            //
            // In case the directive had no colon and value, $directiveName will
            // still contain the trailing }. So let's remove that.
            //
            if (substr_count($directiveName,"}")) $directiveName = str_replace("}","",$directiveName);

            if (strpos($directiveName,'start_of_verse') !== false)
            {
               $startOfVerse = true;
            }
            elseif (strpos($directiveName,'end_of_verse') !== false)
            {
               $endOfVerse = true;
            }
            elseif (strpos($directiveName,'start_of_chorus') !== false)
            {
               $startOfChorus = true;
            }
            elseif (strpos($directiveName,'end_of_chorus') !== false)
            {
               $endOfChorus = true;
            }
            elseif (strpos($directiveName,'comment') !== false && !$this->hideComments)
            {
               $arrLyrics[] = '<span class="cpress_comment">'.$directiveValue.'</span>';
            }
         }
         else
         {
            // Just lyrics without chords => print it
            $arrLyrics[] = $line;
         }
      }
      else if(substr_count($line,"[") != substr_count($line,"]"))
      {
         //
         // Square bracket missing in this line
         //
         $arrLyrics[] = "Missing square bracket in line: " . $line . "<br />";   
      }
      else
      {
         //
         // Split the line into segments beginning with '['
         //
         $arrBracketSegments = explode("[",$line);

         foreach($arrBracketSegments as $segment)
         {
            $pad = ($segment[strlen($segment)-1]==' ') ? '&nbsp;' : '';
            // 
            // Does the first segment start before the 1st '['?
            //
            if(substr_count($segment,"]")==0)
            {
               $arrChords[] = " ";
               $arrLyrics[] = $segment . $pad;
            }
            else
            {
               //
               // Split this segment into chord an lyrics
               //
               $arrChordLyric = explode("]",$segment);
               $arrChords[] = trim($arrChordLyric[0]);
               $arrLyrics[] = $arrChordLyric[1] . $pad;
            }
         }
      }

      //
      // Start of chorus?
      //
      if ($startOfChorus)
      {
         $returnText .= '<div class="cpress_chorus">';
         $startOfChorus = false;
      }

      //
      // Start of verse?
      //
      if ($startOfVerse)
      {
         $returnText .= '<div class="cpress_verse">';
         $startOfVerse = false;
      }

      //
      // Start of line wrapper (lyrics and chords)
      //
      $returnText .= "<div class=\"cpress_line\">\n   ";
      
      // 
      // Align chords and lyrics together by wrapping each in a floating inline div
      //
      if(!$this->hideChords)
      {
         for($i=0; $i<count($arrChords); $i++)
         {
            if(strlen(trim($arrChords[$i])) > 0 || strlen(trim($arrLyrics[$i])) > 0)
            {
               $lyrics = trim($this->stripTags($arrLyrics[$i]));

               //
               // Start of line-section (one chord and its lyrics)
               //
               $returnText .= "<div class=\"cpress_line_section\">\n";

               if(strlen(trim($arrChords[$i])) > 0)
               {
                  $returnText .= "<div class=\"chord chord-hidable key-0\"><strong>";
                  $returnText .= $this->formatChord($arrChords[$i]);
                  $returnText .= '</strong>&nbsp;</div>';

                  //
                  // Prepare all other 11 transposed versions of the chord 
                  // but hide them. They need to be on the page for interactive
                  // mode to show/hide them via Javascript.
                  // But do not mark empty chord divs as hidable. They need to stay.
                  //
                  $returnText .= "\n";
                  $tempTranspose = $this->getTranspose();
                  for ($t=1; $t<=11; $t++) {
                     $this->setTranspose($t);
                     $returnText .= '<div class="chord chord-hidable key-'.$t.'" style="display:none"><strong>'. $this->formatChord($arrChords[$i]) . '</strong>&nbsp;</div>';
                  }
                  $this->setTranspose($tempTranspose);

                  //
                  // Now add the lyrics for this section.
                  //
                  $returnText .= '<div class="lyric">';
                  if(strlen($lyrics) > 0)
                  {
                     $endOf1stWord = strpos($lyrics,' ');
                     $numSpaces = substr_count($lyrics,' ');

                     if($endOf1stWord > 0 && $numSpaces == 1)
                     {
                        $returnText .= substr($lyrics,0,$endOf1stWord) . '&nbsp;';
                        $lyrics = substr($lyrics,$endOf1stWord);
                        $returnText .= "</div></div>\n<div class=\"cpress_line_section\">\n<div class=\"chord\">&nbsp;</div><div class=\"lyric\">";
                     }
                  }
               }
               else
               {
                  $returnText .= '<div class="chord"><strong></strong>&nbsp;</div>';
                  $returnText .= '<div class="lyric">';
               }

               if(strlen($lyrics) > 0) $returnText .= $lyrics;
               $returnText .= "</div>\n"; // end of lyric
               $returnText .= "</div>\n"; // end of line
            }
         }

         //
         // If there are more lyric parts than chord parts, add them here
         //
         for($i=count($arrChords); $i<count($arrLyrics); $i++)
         {
            $returnText .= $arrLyrics[$i];
         }
      }
      else
      {
         // 
         // Hide chords was selected, so only show the lyrics
         //
         $returnText .= '<div class="cpress_line_section"><div class="lyric">';
         for($i=0; $i<count($arrLyrics); $i++)
         {
            $returnText .= $arrLyrics[$i];
         }
         $returnText .= "</div></div>";
      }

      //
      // End of line wrapper
      //
      $returnText .= "</div>\n";

      //
      // End of chorus?
      //
      if ($endOfChorus)
      {
         $returnText .= "</div>\n";
         $endOfChorus = false;
      }

      //
      // End of verse?
      //
      if ($endOfVerse)
      {
         $returnText .= "</div>\n";
         $endOfVerse = false;
      }

      //
      // Add a clear:both div
      //
      $returnText .= "<div class='cpress_clear'></div>\n";

      return $returnText;
   }
   
   /** ------------------------------------------------------------------------
    * Removes all <> wrapped text from a string
    *
    * @param  string  $string  String to process
    * @return string  Stripped text
    */    
   private function stripTags($string)
   {
      $retString = "";
      $isVisible = true;
      for($i = 0; $i < strlen($string); $i++)
      {
         if($isVisible)
         {
            if($string[$i]=='<')
               $isVisible = false;
            else 
               $retString .= $string[$i];
         }
         else if($string[$i]=='>')
            $isVisible = true;
      }
      return $retString;
   }
   
   /** ------------------------------------------------------------------------
    * Processes a note text and returns the formatted HTML
    *
    * @param  string   $ch  Chord text, e.g. "A" or "B#" or "Db/F"
    * @return string   Uppercase note string
    */    
   public function formatChord( $ch )
   {
      $useFlats = false;
      
      if(strlen($ch)==0) return;
      
      //
      // The first letter should be the key note
      //
      $note = substr($ch,0,1);
      $xlatedNote = $note;
      $rem = substr($ch,1);
   
      //
      // Convert note to a number
      //
      $noteVal = $this->noteToNumber($note);
      
      //
      // Check sharp/flat
      //
      if(strlen($rem) > 0)
      {
         if(substr($rem,0,1) == '#')
         {
            $noteVal++;
            $rem = substr($rem,1);
         }
         else if(strncasecmp($rem,"b",1)==0)
         {
            $useFlats = true;
            $noteVal--;
            $rem = substr($rem,1);
         }
      }
      
      //
      // Add transpose
      //
      $noteVal += $this->transpose;
      if($noteVal > 11) $noteVal -= 12;
      if($noteVal < 0) $noteVal += 12;
   
      //
      // Build note to display from numeric note value
      //
      $xlatedNote = $this->numberToNote($noteVal, $useFlats);

      //
      // Return the chord display plus the bass note (if any)
      //
      return $xlatedNote . $this->formatRemainder($rem);
   }

   /** ------------------------------------------------------------------------
    * Processes the remainder of the given chord
    *
    * @param   string   $remainder  Chord text starting at second char
    * @return  string   Formatted remainder
    */    
   public function formatRemainder( $remainder )
   {
      $indexOfSlash = strpos($remainder,'/');
      if($indexOfSlash === false) return $remainder;
      
      //
      // We have a slash, indicating that a bass note follows.
      // Break the string into 2 parts at the slash. 
      // The slash will be lost, and will need to be added back later.
      //
      $chord = substr($remainder,0,$indexOfSlash);
      $bass = substr($remainder,$indexOfSlash+1);
      
      $useFlats = false;
      
      if(strlen($bass)==0) return $chord . "/?";
      
      // 
      // The first letter should be the bass note
      //
      $note = substr($bass,0,1);
      $xlatedNote = $note;
      $rem = substr($bass,1);
      
      // 
      // Convert note to a number
      //
      $noteVal = $this->noteToNumber($note);

      // 
      // Check sharp/flat
      //
      if(strlen($rem) > 0)
      {
         if(substr($rem,0,1) == '#')
         {
            $noteVal++;
            $rem = substr($rem,1);
         }
         else if(strncasecmp($rem,"b",1)==0)
         {
            $useFlats = true;
            $noteVal--;
            $rem = substr($rem,1);
         }
      }
      
      //      
      // Add transpose
      //
      $noteVal += $this->transpose;
      if($noteVal > 11) $noteVal -= 12;
      if($noteVal < 0) $noteVal += 12;
   
      //
      // Build note to display from numeric note value
      //
      $xlatedNote = $this->numberToNote($noteVal);

      return $chord . "/" . $xlatedNote . $rem;
   }

   /** ------------------------------------------------------------------------
    * Convert note to number
    *
    * @param  string  $n  Note to convert
    * @return integer
    */    
   public function noteToNumber( $n )
   {
      $regularNotation = array (
         'A' => 0,
         'B' => 2,
         'C' => 3,
         'D' => 5,
         'E' => 7,
         'F' => 8,
         'G' => 10,
      );
      
      $hbNotation = array (
         'A' => 0,
         'B' => 1,
         'H' => 2,
         'C' => 3,
         'D' => 5,
         'E' => 7,
         'F' => 8,
         'G' => 10,
      );
      
      if($this->hbNotation == 'yes') {
         return $hbNotation[strtoupper($n)];
      }
      else {
         return $regularNotation[strtoupper($n)];
      }
   }

   /** ------------------------------------------------------------------------
    * Convert number to note
    *
    * @param  integer  $n         Numeric note value to convert to note string
    * @param  boolean  $useFlats  Whether to use flat notes or not
    * @return string
    */    
   public function numberToNote( $n, $useFlats=false )
   {
      $retVal = '';

      switch($n)
      {
         case 0:
            $retVal = 'A';
            break;

         case 1:
            if($this->displayHBNotation){
               $retVal = 'B';
            }else{
               if($useFlats) $retVal = "Bb";
               else $retVal = "A#";
            }
            break;

         case 2:
            if($this->displayHBNotation){
               $retVal = 'H';
            }else{
               $retVal = 'B';
            }
            break;

         case 3:
            $retVal = 'C';
            break;

         case 4:
            if($useFlats) $retVal = "Db";
            else $retVal = "C#";
            break;

         case 5:
            $retVal = 'D';
            break;

         case 6:
            if($useFlats) $retVal = "Eb";
            else $retVal = "D#";
            break;

         case 7:
            $retVal = 'E';
            break;

         case 8:
            $retVal = 'F';
            break;

         case 9:
            if($useFlats) $retVal = "Gb";
            else $retVal = "F#";
            break;

         case 10:
            $retVal = 'G';
            break;

         case 11:
            if($useFlats) $retVal = "Ab";
            else $retVal = "G#";
            break;
      }
      return $retVal;
   }

   /** ------------------------------------------------------------------------
    * Sets class variable: displayHBNotation
    *
    * @param  boolean  $g
    */    
   public function setDisplayHBNotation( $g )
   {
      $this->displayHBNotation = $g;
   }
 
   /** ------------------------------------------------------------------------
    * Gets class variable: displayHBNotation
    *
    * @return  boolean
    */    
   public function getDisplayHBNotation()
   {
      return $this->displayHBNotation;
   }
    
   /** ------------------------------------------------------------------------
    * Sets class variable: float
    *
    * @param  string  $f  left/right/none/inherit
    */    
   public function setFloat( $f )
   {
      $this->float = $f;
   }

   /** ------------------------------------------------------------------------
    * Gets class variable: float
    *
    * @return string
    */    
   public function getFloat()
   {
      return $this->float;
   }

   /** ------------------------------------------------------------------------
    * Sets class variable: hbNotation
    *
    * @param  string  $g  yes/no
    */    
   public function setHBNotation( $g )
   {
      $this->hbNotation = $g;
   }
 
   /** ------------------------------------------------------------------------
    * Gets class variable: hbNotation
    *
    * @return  string  yes/no
    */    
   public function getHBNotation()
   {
      return $this->hbNotation;
   }
 
   /** ------------------------------------------------------------------------
    * Sets class variable: interactive
    *
    * @param  string  $g  yes/no
    */    
   public function setInteractive( $i )
   {
      $this->interactive = $i;
   }
 
   /** ------------------------------------------------------------------------
    * Gets class variable: interactive
    *
    * @return  string  yes/no
    */    
   public function getInteractive()
   {
      return $this->interactive;
   }

    /** ------------------------------------------------------------------------
    * Sets class variable: transpose
    *
    * @param  integer  $t  Positive or negative number of steps for transponation
    */    
   public function setTranspose( $t )
   {
      $this->transpose = $t;
   }
 
   /** ------------------------------------------------------------------------
    * Gets class variable: transpose
    *
    * @return  integer
    */    
   public function getTranspose()
   {
      return $this->transpose;
   }
   
 }
?>
