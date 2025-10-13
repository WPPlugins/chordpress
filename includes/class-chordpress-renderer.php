<?php

/**
 * Render ChordPro text
 *
 * @link       https://www.lewe.com
 * @since      1.0.0
 *
 * @package    ChordPress
 * @subpackage ChordPress/includes
 */

/**
 * Render ChordPro text.
 *
 * Parses a given ChordPro formatted text line by line and renders a pretty print output.
 *
 * @package    ChordPress
 * @subpackage ChordPress/includes
 * @author     George Lewe <george@lewe.com>
 */
class ChordPress_Renderer {
  /**
   * Array of all chords in this song.
   *
   * @since    2.1.0
   * @access   private
   * @var      array $allChords Array of all chords in this song.
   */
  private $allChords = array();

  /**
   * Array of allowed prefixes for chords.
   *
   * @since    3.6.1
   * @access   private
   * @var      array
   */
  private $allowedChordPrefixes = array('~', '|', '/', '.', '-', '+', '1', '2', '3', '4', '5', '6', '7', '8', '9');

  /**
   * Array of chromatic harmonica transpose notation.
   *
   * @since    3.8.0
   * @access   private
   * @var      array
   */
  private $arrChromaticTranspose = array(
    '1' => ['1', '1s', '-1', '-1s', '2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4'],
    '1s' => ['1s', '-1', '-1s', '2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4'],
    '-1' => ['-1', '-1s', '2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s'],
    '-1s' => ['-1s', '2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s', '-5'],
    '2' => ['2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s'],
    '-2' => ['-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6'],
    '-2s' => ['-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6'],
    '3' => ['3', '3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s'],
    '3s' => ['3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s', '7'],
    '-3' => ['-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s'],
    '-3s' => ['-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7'],
    '-4' => ['-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s'],
    '4' => ['4', '4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8'],
    '4s' => ['4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8'],
    '-5' => ['-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8', '9'],
    '-5s' => ['-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8', '9', '9s'],
    '6' => ['6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8', '9', '9s', '-9'],
    '-6' => ['-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s'],
    '-6s' => ['-6s', '7', '7s', '-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s', '10'],
    '7' => ['7', '7s', '-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s', '10', '-10'],
    '7s' => ['7s', '-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s', '10', '-10', '-10s'],
    '-7' => ['-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s', '10', '-10', '-10s', '11'],
    '-7s' => ['-7s', '-8', '8', '9', '9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s'],
    '-8' => ['-8', '8', '9', '9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11'],
    '8' => ['8', '9', '9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s'],
    '9' => ['9', '9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12'],
    '9s' => ['9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12'],
    '-9' => ['-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s'],
    '-9s' => ['-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s', ' '],
    '10' => ['10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s', ' ', ' '],
    '-10' => ['-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s', ' ', ' ', ' '],
    '-10s' => ['-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s', ' ', ' ', ' ', ' '],
    '11' => ['11', '11s', '-11', '-11s', '-12', '12', '12s', ' ', ' ', ' ', ' ', ' '],
    '11s' => ['11s', '-11', '-11s', '-12', '12', '12s', ' ', ' ', ' ', ' ', ' ', ' '],
    '-11' => ['-11', '-11s', '-12', '12', '12s', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
    '-11s' => ['-11s', '-12', '12', '12s', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
    '-12' => ['-12', '12', '12s', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
    '12' => ['12', '12s', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
    '12s' => ['12s', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
  );

  /**
   * Array of supported ChordPro directives.
   *
   * @since    1.0.0
   * @access   private
   * @var      array $arrDirectives Array of supported ChordPro directives.
   */
  private $arrDirectives = array(
    'album' => '',
    'artist' => '',
    'capo' => '',
    'composer' => '',
    'key' => '',
    'subtitle' => '',
    'tempo' => '',
    'time' => '',
    'title' => '',
    'year' => '',
  );

  /**
   * Array of supported Key directives.
   *
   * @since    3.1.0
   * @access   private
   * @var      array
   */
  private $arrKeys = array('A', 'Am', 'A#', 'A#m', 'Bb', 'Bbm', 'B', 'Bm', 'C', 'Cm', 'C#', 'C#m', 'Db', 'Dbm', 'D', 'Dm', 'D#', 'D#m', 'Eb', 'Ebm', 'E', 'Em', 'F', 'Fm', 'G', 'Gm', 'G#', 'G#m', 'Ab', 'Abm');

  /**
   * Array of transposed keys.
   *
   * @since    3.1.0
   * @access   private
   * @var      array
   */
  private $arrTransposeValues = array(
    'A' => array('A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#'),
    'Am' => array('Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m'),
    'A#' => array('A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A'),
    'Bb' => array('Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A'),
    'A#m' => array('A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am'),
    'Bbm' => array('Bbm', 'Bm', 'Cm', 'Dbm', 'Dm', 'Ebm', 'Em', 'Fm', 'Gbm', 'Gm', 'Abm', 'Am'),
    'B' => array('B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#'),
    'Bm' => array('Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m'),
    'C' => array('C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'),
    'Cm' => array('Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm'),
    'C#' => array('C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C'),
    'Db' => array('Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B', 'C'),
    'C#m' => array('C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm'),
    'Dbm' => array('Dbm', 'Dm', 'Ebm', 'Em', 'Fm', 'Gbm', 'Gm', 'Abm', 'Am', 'Bbm', 'Bm', 'Cm'),
    'D' => array('D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#'),
    'Dm' => array('Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m'),
    'D#' => array('D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D'),
    'Eb' => array('Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B', 'C', 'Db', 'D'),
    'D#m' => array('D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm'),
    'Ebm' => array('Ebm', 'Em', 'Fm', 'Gbm', 'Gm', 'Abm', 'Am', 'Bbm', 'Bm', 'Cm', 'Dbm', 'Dm'),
    'E' => array('E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#'),
    'Em' => array('Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m'),
    'F' => array('F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E'),
    'Fm' => array('Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em'),
    'F#' => array('F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F'),
    'Gb' => array('Gb', 'G', 'Ab', 'A', 'Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F'),
    'F#m' => array('F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm'),
    'Gbm' => array('Gbm', 'Gm', 'Abm', 'Am', 'Bbm', 'Bm', 'Cm', 'Dbm', 'Dm', 'Ebm', 'Em', 'Fm'),
    'G' => array('G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#'),
    'Gm' => array('Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m'),
    'G#' => array('G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G'),
    'Ab' => array('Ab', 'A', 'Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G'),
    'G#m' => array('G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm'),
    'Abm' => array('Abm', 'Am', 'Bbm', 'Bm', 'Cm', 'Dbm', 'Dm', 'Ebm', 'Em', 'Fm', 'Gbm', 'Gm'),
  );

  /**
   * Guitar chord instance.
   *
   * @since    2.1.0
   * @access   private
   * @var      ChordPress_Chord $C Guitar chord instance.
   */
  private $C;

  /**
   * CSS styles for the chords.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $chordStyle CSS styles for the chorus sections.
   */
  private $chordStyle;

  /**
   * CSS styles for the chorus sections.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $chorusStyle CSS styles for the chorus sections.
   */
  private $chorusStyle;

  /**
   * CSS styles for comments.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $commentStyle CSS styles for comments.
   */
  private $commentStyle;

  /**
   * Display H/B notation flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $displayHBNotation Display H/B notation flag.
   */
  private $displayHBNotation;

  /**
   * Place interaction bar fixed at bottom of page.
   *
   * @since    3.6.0
   * @access   private
   * @var      boolean $fixedInteraction
   */
  private $fixedInteraction;

  /**
   * CSS float directive: no/left/right.
   *
   * @since    1.5.0
   * @access   private
   * @var      string $float CSS float directive: no/left/right.
   */
  private $float;

  /**
   * Harmonica type shortcode flag.
   *
   * @since    3.8.0
   * @access   private
   * @var      string $harmonica Harmonica type.
   */
  private $harmonica;

  /**
   * Array of harmonica notation.
   *
   * @since    3.8.0
   * @access   private
   * @var      array
   */
  private $harmonicaNotes = array(
    array('tremolo' => '1', 'diatonic' => '1', 'chromatic' => '1', 'note' => "C1"),
    array('tremolo' => '-', 'diatonic' => '-1/', 'chromatic' => '1s', 'note' => "C#1"),
    array('tremolo' => '-1', 'diatonic' => '-1', 'chromatic' => '-1', 'note' => "D1"),
    array('tremolo' => '-', 'diatonic' => '-', 'chromatic' => '-1s', 'note' => "D#1"),
    array('tremolo' => '2', 'diatonic' => '2', 'chromatic' => '2', 'note' => "E1"),
    array('tremolo' => '-2', 'diatonic' => '-2//', 'chromatic' => '-2', 'note' => "F1"),
    array('tremolo' => '-', 'diatonic' => '-2/', 'chromatic' => '-2s', 'note' => "F#1"),
    array('tremolo' => '3', 'diatonic' => '3', 'chromatic' => '3', 'note' => "G1"),
    array('tremolo' => '3', 'diatonic' => '-2', 'chromatic' => '3', 'note' => "G1"),
    array('tremolo' => '-', 'diatonic' => '-3///', 'chromatic' => '3s', 'note' => "G#1"),
    array('tremolo' => '-3', 'diatonic' => '-3//', 'chromatic' => '-3', 'note' => "A1"),
    array('tremolo' => '-', 'diatonic' => '-3/', 'chromatic' => '-3s', 'note' => "A#1"),
    array('tremolo' => '-4', 'diatonic' => '-3', 'chromatic' => '-4', 'note' => "B1"),
    array('tremolo' => '4', 'diatonic' => '4', 'chromatic' => '4', 'note' => "C2"),
    array('tremolo' => '5', 'diatonic' => '4', 'chromatic' => '5', 'note' => "C2"),
    array('tremolo' => '-', 'diatonic' => '-4/', 'chromatic' => '4s', 'note' => "C#2"),
    array('tremolo' => '-', 'diatonic' => '-4/', 'chromatic' => '5s', 'note' => "C#2"),
    array('tremolo' => '-5', 'diatonic' => '-4', 'chromatic' => '-5', 'note' => "D2"),
    array('tremolo' => '-', 'diatonic' => '-', 'chromatic' => '-5s', 'note' => "D#2"),
    array('tremolo' => '6', 'diatonic' => '5', 'chromatic' => '6', 'note' => "E2"),
    array('tremolo' => '-6', 'diatonic' => '-5', 'chromatic' => '-6', 'note' => "F2"),
    array('tremolo' => '-', 'diatonic' => '-', 'chromatic' => '-6s', 'note' => "F#2"),
    array('tremolo' => '7', 'diatonic' => '6', 'chromatic' => '7', 'note' => "G2"),
    array('tremolo' => '-', 'diatonic' => '-6/', 'chromatic' => '7s', 'note' => "G#2"),
    array('tremolo' => '-7', 'diatonic' => '-6', 'chromatic' => '-7', 'note' => "A2"),
    array('tremolo' => '-', 'diatonic' => '-', 'chromatic' => '-7s', 'note' => "A#2"),
    array('tremolo' => '-8', 'diatonic' => '-7', 'chromatic' => '-8', 'note' => "B2"),
    array('tremolo' => '8', 'diatonic' => '7', 'chromatic' => '8', 'note' => "C3"),
    array('tremolo' => '9', 'diatonic' => '7', 'chromatic' => '9', 'note' => "C3"),
    array('tremolo' => '-', 'diatonic' => '-', 'chromatic' => '8s', 'note' => "C#3"),
    array('tremolo' => '-', 'diatonic' => '-', 'chromatic' => '9s', 'note' => "C#3"),
    array('tremolo' => '-9', 'diatonic' => '-8', 'chromatic' => '-9', 'note' => "D3"),
    array('tremolo' => '-', 'diatonic' => '8/', 'chromatic' => '-9s', 'note' => "D#3"),
    array('tremolo' => '10', 'diatonic' => '8', 'chromatic' => '10', 'note' => "E3"),
    array('tremolo' => '-10', 'diatonic' => '-9', 'chromatic' => '-10', 'note' => "F3"),
    array('tremolo' => '', 'diatonic' => '9/', 'chromatic' => '-10s', 'note' => "F#3"),
    array('tremolo' => '11', 'diatonic' => '9', 'chromatic' => '11', 'note' => "G3"),
    array('tremolo' => '-', 'diatonic' => '-', 'chromatic' => '11s', 'note' => "G#3"),
    array('tremolo' => '-11', 'diatonic' => '-10', 'chromatic' => '-11', 'note' => "A3"),
    array('tremolo' => '-', 'diatonic' => '10//', 'chromatic' => '-11s', 'note' => "A#3"),
    array('tremolo' => '-12', 'diatonic' => '10/', 'chromatic' => '-12', 'note' => "B3"),
    array('tremolo' => '12', 'diatonic' => '10', 'chromatic' => '12', 'note' => "C4"),
    array('tremolo' => '-', 'diatonic' => '-', 'chromatic' => '12s', 'note' => "C#4"),
  );

  /**
   * Harmonica types.
   *
   * @since    3.8.0
   * @access   private
   * @var      array $harmonicaTypes Harmonica types.
   */
  private $harmonicaTypes = ['tremolo', 'diatonic', 'chromatic'];

  /**
   * Shortcode directive hbNotation: no/yes.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $hbNotation Shortcode directive hbNotation: no/yes.
   */
  private $hbNotation;

  /**
   * Hide album flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideAlbum Hide album flag.
   */
  private $hideAlbum;

  /**
   * Hide artist flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideArtist Hide artist flag.
   */
  private $hideArtist;

  /**
   * Hide chords flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideChords Hide chords flag.
   */
  private $hideChords;

  /**
   * Hide comments flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideComments Hide comments flag.
   */
  private $hideComments;

  /**
   * Hide composer flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideComposer Hide composer flag.
   */
  private $hideComposer;

  /**
   * Hide font size form element.
   *
   * @since    3.8.1
   * @access   private
   * @var      boolean $hideFontsize Hide fontsize form element.
   */
  private $hideFontsize;

  /**
   * Hide key flag.
   *
   * @since    3.7.0
   * @access   private
   * @var      boolean $hideKey Hide key flag.
   */
  private $hideKey;

  /**
   * Hide print button.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hidePrint Hide print button.
   */
  private $hidePrint;

  /**
   * Hide subtitle flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideSubtitle Hide subtitle flag.
   */
  private $hideSubtitle;

  /**
   * Hide title flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideTitle Hide title flag.
   */
  private $hideTitle;

  /**
   * Hide transpose button.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideTranspose Hide transpose button.
   */
  private $hideTranspose;

  /**
   * Hide year flag.
   *
   * @since    1.0.0
   * @access   private
   * @var      boolean $hideYear Hide year flag.
   */
  private $hideYear;

  /**
   * In monospace mode.
   * As long as true, lines are not analyzed, just printed out.
   *
   * @since    3.5.0
   * @access   private
   * @var      boolean $inMonospace Monospace mode flag.
   */
  private $inMonospace;

  /**
   * Shortcode directive interactive: no/yes.
   *
   * @since    1.5.0
   * @access   private
   * @var      string $interactive Shortcode directive interactive: no/yes.
   */
  private $interactive;

  /**
   * CSS styles for the line sections.
   *
   * @since    3.1.2
   * @access   private
   * @var      string $lineStyle CSS styles for the line sections.
   */
  private $lineStyle;

  /**
   * CSS styles for lyrics.
   *
   * @since    2.3.0
   * @access   private
   * @var      string $lyricsStyle CSS styles for lyrics.
   */
  private $lyricsStyle;

  /**
   * CSS styles for meta info.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $metaStyle CSS styles for meta info.
   */
  private $metaStyle;

  /**
   * Show Chord Sheet.
   *
   * @since    2.1.0
   * @access   private
   * @var      string $showChordSheet Show Chord Sheet: no/yes.
   */
  private $showChordSheet;

  /**
   * Show Chord Sheet on top.
   *
   * @since    2.1.0
   * @access   private
   * @var      string $showChordSheetOnTop Show Chord Sheet on top: no/yes.
   */
  private $showChordSheetOnTop;

  /**
   * Show numeric transpose input.
   *
   * @since    3.6.0
   * @access   private
   * @var      boolean $showNumericTranspose Show numeric transpose input.
   */
  private $showNumericTranspose;

  /**
   * Holds the styles for the songsheet
   *
   * @since    3.9.3
   * @access   private
   * @var      string
   */
  private $songsheetStyles = '';

  /**
   * HTML header level for subtitle.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $subtitleLevel HTML header level for subtitle.
   */
  private $subtitleLevel;

  /**
   * HTML header level for title.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $titleLevel HTML header level for title.
   */
  private $titleLevel;

  /**
   * Shortcode directive transpose: 0-11 (semitones).
   *
   * @since    1.0.0
   * @access   private
   * @var      string $transpose Shortcode directive transpose: 0-11 (semitones).
   */
  private $transpose;

  /**
   * Array of unique chords in this song.
   *
   * @since    2.1.0
   * @access   private
   * @var      array $uniqueChords Array of unique chords in this song.
   */
  private $uniqueChords = array();

  /**
   * CSS styles for the verse sections.
   *
   * @since    1.0.0
   * @access   private
   * @var      string $verseStyle CSS styles for the verse sections.
   */
  private $verseStyle;

  /**
   * --------------------------------------------------------------------------
   * Constructor.
   * --------------------------------------------------------------------------
   *
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   */
  public function __construct() {
    $this->C = new ChordPress_Chord();
    /**
     * Shortcode Defaults
     */
    $this->float = 'no';
    $this->hbNotation = 'no';
    $this->interactive = 'no';
    $this->transpose = 0;
    /**
     * Get options or set defaults
     */
    if (!$this->hideAlbum = get_option('chordpress_checkbox_hide_album')) $this->hideAlbum = 0;
    if (!$this->hideArtist = get_option('chordpress_checkbox_hide_artist')) $this->hideArtist = 0;
    if (!$this->hideChords = get_option('chordpress_checkbox_hide_chords')) $this->hideChords = 0;
    if (!$this->hideComments = get_option('chordpress_checkbox_hide_comments')) $this->hideComments = 0;
    if (!$this->hideComposer = get_option('chordpress_checkbox_hide_composer')) $this->hideComposer = 0;
    if (!$this->hideKey = get_option('chordpress_checkbox_hide_key')) $this->hideKey = 0;
    if (!$this->hidePrint = get_option('chordpress_checkbox_hide_print')) $this->hidePrint = 0;
    if (!$this->hideSubtitle = get_option('chordpress_checkbox_hide_subtitle')) $this->hideSubtitle = 0;
    if (!$this->hideTitle = get_option('chordpress_checkbox_hide_title')) $this->hideTitle = 0;
    if (!$this->hideFontsize = get_option('chordpress_checkbox_hide_fontsize')) $this->hideFontsize = 0;
    if (!$this->hideTranspose = get_option('chordpress_checkbox_hide_transpose')) $this->hideTranspose = 0;
    if (!$this->hideYear = get_option('chordpress_checkbox_hide_year')) $this->hideYear = 0;
    if (!$this->displayHBNotation = get_option('chordpress_checkbox_hb_notation')) $this->displayHBNotation = 0;
    if (!$this->showChordSheet = get_option('chordpress_checkbox_show_chord_sheet')) $this->showChordSheet = 0;
    if (!$this->showChordSheetOnTop = get_option('chordpress_checkbox_show_chord_sheet_on_top')) $this->showChordSheetOnTop = 0;
    if (!$this->showNumericTranspose = get_option('chordpress_checkbox_show_numeric_transpose')) $this->showNumericTranspose = 0;
    if (!$this->fixedInteraction = get_option('chordpress_checkbox_fixed_interaction')) $this->fixedInteraction = 0;

    if (!$this->titleLevel = get_option('chordpress_select_title_level')) $this->titleLevel = 'h1';
    if (!$this->subtitleLevel = get_option('chordpress_select_subtitle_level')) $this->subtitleLevel = 'h2';
    if (!$this->metaStyle = get_option('chordpress_text_meta_style')) $this->metaStyle = '';
    if (!$this->chordStyle = get_option('chordpress_text_chord_style')) $this->chordStyle = '';
    if (!$this->lyricsStyle = get_option('chordpress_text_lyrics_style')) $this->lyricsStyle = '';
    if (!$this->commentStyle = get_option('chordpress_text_comment_style')) $this->commentStyle = '';
    if (!$this->chorusStyle = get_option('chordpress_text_chorus_style')) $this->chorusStyle = '';
    if (!$this->verseStyle = get_option('chordpress_text_verse_style')) $this->verseStyle = '';
    if (!$this->lineStyle = get_option('chordpress_text_line_style')) $this->lineStyle = 'margin: 1em 0 1em 0;';
  }

  /**
   * --------------------------------------------------------------------------
   * Get Directives.
   * --------------------------------------------------------------------------
   *
   * Get the ChordPro directives and store the supported ones in an array.
   *
   * @param array $text - Text inside shortcode wrapper in an array (each line is an array element).
   *
   * @since 1.0.0
   */
  public function getDirectives(array $text) {
    foreach ($text as $line) {
      $line = trim($this->stripTags($line));
      if (strlen($line) && $line[0] == '{') {
        /**
         * Line starts with "{": It's a directive
         * Get directive name and value and store
         */
        $arrDirective = explode(":", $line);
        // $this->pretty_dump($arrDirective);
        $directiveName = strtolower(str_replace('{', '', trim($arrDirective[0])));
        if (isset($arrDirective[1])) $directiveValue = substr(trim($arrDirective[1]), 0, strpos(trim($arrDirective[1]), '}'));
        /**
         * Process directive alternatives
         */
        switch (strtolower($directiveName)) {
          case "t":
            $directiveName = 'title';
            break;
          case "st":
            $directiveName = 'subtitle';
            break;
          case "meta":
            /**
             * {meta: title Heart of Gold}
             * We have split the "meta:" off already.
             * Now comes the part after.
             * We only handle the supported directives ($this->arrDirectives)
             */
            $arrMetaDirective = explode(" ", $directiveValue, 2);
            foreach ($this->arrDirectives as $key => $value) {
              if (trim($arrMetaDirective[0]) == $key) {
                $directiveName = $key;
                $directiveValue = trim($arrMetaDirective[1]);
              }
            }
            break;
        }
        /**
         * Store directive in directives array for later use
         */
        if (array_key_exists($directiveName, $this->arrDirectives)) {
          $this->arrDirectives[$directiveName] = $directiveValue;
        }
      }
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Display Text.
   * --------------------------------------------------------------------------
   *
   * Creates the formatted HTML of the ChordPro text.
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
   * @param array $text - Text inside shortcode wrapper in an array (each line is an array element).
   *
   * @return string - Formatted HTML of the ChordPro text.
   * @since 1.0.0
   */
  public function displayText(array $text): string {
    /**
     * The styles need to be a one-line string in a separate variable because it is passed to the Javascript print function
     */
    $this->songsheetStyles = "<style>div.cpress { float: " . $this->float . "; clear: both; } div.cpress_line { " . $this->lineStyle . "; } div.cpress_line_section { display: inline; float: left; } div.cpress_line_section .chord .chordshort { " . $this->chordStyle . "; } div.cpress_line_section .lyric { " . $this->lyricsStyle . "; } div.cpress_chorus { " . $this->chorusStyle . "; } div.cpress_verse { " . $this->verseStyle . "; } div.cpress_clear { clear: both; } div.cpress_meta { " . $this->metaStyle . "; } div.cpress_interaction { float: right; } div.cpress_interaction select { width: auto; } span.cpress_comment { " . $this->commentStyle . "; } div.cpress_chordsheet { text-align: center; } div.cpress-chord-container { display: inline-block; margin: 10px; vertical-align: top; }</style>";
    $this->songsheetStyles = str_replace(';;', ';', $this->songsheetStyles);

    $songsheetHtml = "\n\n
      <!-- ChordPress -->\n\n
      <!-- ChordPress Styles -->
      <div>
      " . $this->songsheetStyles . "
      </div>\n
      <!-- /ChordPress Styles -->\n\n";

    /**
     * Interaction menu
     */
    if (in_array(strtolower($this->interactive), array("yes", "1", "true"))) {
      $songsheetHtml .= $this->buildInteractionMenu();
    }

    $songsheetHtml .= "<!-- ChordPress Song %cpressID% -->\n
      <div id=\"%cpressID%\" class=\"cpress\">\n";

    /**
     * Print header and subtitle if directives exist
     */
    $randId = $this->random_string(true);
    if (strlen($this->arrDirectives['title']) && !$this->hideTitle) {
      $cpressID = str_replace(' ', '-', strtolower(trim($this->arrDirectives['title']))) . '-' . $randId;
      $songsheetHtml = str_replace('%cpressID%', $cpressID, $songsheetHtml);
      $songsheetHtml .= '<' . $this->titleLevel . '>' . esc_html($this->arrDirectives['title']) . '</' . $this->titleLevel . ">\n";
    } else {
      $cpressID = 'cpress-id-' . $randId;
      $songsheetHtml = str_replace('%cpressID%', $cpressID, $songsheetHtml);
    }
    if (strlen($this->arrDirectives['subtitle']) && !$this->hideSubtitle) {
      $songsheetHtml .= '<' . $this->subtitleLevel . '>' . esc_html($this->arrDirectives['subtitle']) . '</' . $this->subtitleLevel . ">\n";
    }

    /**
     * Print meta data if directives exist
     */
    if (
      strlen($this->arrDirectives['album']) ||
      strlen($this->arrDirectives['artist']) ||
      strlen($this->arrDirectives['capo']) ||
      strlen($this->arrDirectives['composer']) ||
      strlen($this->arrDirectives['key']) ||
      strlen($this->arrDirectives['tempo']) ||
      strlen($this->arrDirectives['time']) ||
      strlen($this->arrDirectives['year'])
    ) {
      $songsheetHtml .= "<div class=\"cpress_meta\">\n";
      if (strlen($this->arrDirectives['composer']) && !$this->hideComposer) $songsheetHtml .= esc_html(__('Composer', 'chordpress')) . ': ' . esc_html($this->arrDirectives['composer']) . "<br>\n";
      if (strlen($this->arrDirectives['artist']) && !$this->hideArtist) $songsheetHtml .= esc_html(__('Artist', 'chordpress')) . ': ' . esc_html($this->arrDirectives['artist']) . "<br>\n";
      if (strlen($this->arrDirectives['year']) && !$this->hideYear) $songsheetHtml .= esc_html(__('Year', 'chordpress')) . ': ' . esc_html($this->arrDirectives['year']) . "<br>\n";
      if (strlen($this->arrDirectives['album']) && !$this->hideAlbum) $songsheetHtml .= esc_html(__('Album', 'chordpress')) . ': ' . esc_html($this->arrDirectives['album']) . "<br>\n";

      if (strlen($this->arrDirectives['key']) && !$this->hideKey) {
        $songsheetHtml .= esc_html(__('Key (original)', 'chordpress')) . ': ' . esc_html($this->arrDirectives['key']) . "<br>\n";
        $songsheetHtml .= esc_html(__('Key (transposed)', 'chordpress')) . ': <span class="chord-hidable-%cpressID% key-%cpressID%-0">' . esc_html($this->arrDirectives['key']) . '</span>';
        for ($t = 1; $t <= 11; $t++) {
          $songsheetHtml .= '<span class="chord-hidable-%cpressID% key-%cpressID%-' . $t . '" style="display:none">' . esc_html($this->arrTransposeValues[$this->arrDirectives['key']][$t]) . '</span>';
        }
        $songsheetHtml .= "<br>\n";
      }

      if (strlen($this->arrDirectives['time'])) $songsheetHtml .= esc_html(__('Time', 'chordpress')) . ': ' . esc_html($this->arrDirectives['time']) . "<br>\n";
      if (strlen($this->arrDirectives['tempo'])) $songsheetHtml .= esc_html(__('Tempo', 'chordpress')) . ': ' . esc_html($this->arrDirectives['tempo']) . "<br>\n";
      if (strlen($this->arrDirectives['capo'])) $songsheetHtml .= esc_html(__('Capo', 'chordpress')) . ': ' . esc_html($this->arrDirectives['capo']) . "<br>\n";
      $songsheetHtml = preg_replace('/<br>$/', '', $songsheetHtml); // Remove last <br>
      $songsheetHtml .= "</div>\n";
    }

    /**
     * Go through each line and process it
     */
    $this->inMonospace = false;
    $openSections = []; // Stack to track open sections
    foreach ($text as $line) {
      $trimmed = trim($line);

      if (strpos($trimmed, '{start_of_verse}') !== false) {
        $songsheetHtml .= "<div class=\"cpress_verse\">\n<div class=\"cpress_line\">\n";
        $openSections[] = 'verse';
        continue; // no need to process further for this line
      }

      if (strpos($trimmed, '{end_of_verse}') !== false) {
        if (!empty($openSections) && end($openSections) === 'verse') {
          array_pop($openSections);
          $songsheetHtml .= "</div></div>\n";
        }
        continue; // no need to process further for this line
      }

      if (strpos($trimmed, '{start_of_chorus}') !== false) {
        $songsheetHtml .= "<div class=\"cpress_chorus\">\n<div class=\"cpress_line\">\n";
        $openSections[] = 'chorus';
        continue; // no need to process further for this line
      }

      if (strpos($trimmed, '{end_of_chorus}') !== false) {
        if (!empty($openSections) && end($openSections) === 'chorus') {
          array_pop($openSections);
          $songsheetHtml .= "</div></div>\n";
        }
        continue; // no need to process further for this line
      }

      if ($this->inMonospace && strpos($line, "{end_of_monospace}") === false) {
        $songsheetHtml .= $line;
      } else {
        $songsheetHtml .= $this->formatAndDisplayLine($line);
      }
    }
    // Close any remaining open sections
    while (!empty($openSections)) {
      $section = array_pop($openSections);
      $songsheetHtml .= "</div></div>\n";
    }

    /**
     * End of ChordPress song
     */
    $songsheetHtml .= "</div>\n
    <!-- /ChordPress Song %cpressID% -->\n
    <!-- /ChordPress -->\n\n";

    $songsheetHtml = str_replace('%cpressID%', $cpressID, $songsheetHtml);

    /**
     * Generate chord data for transpose functionality (always needed)
     */
    $this->uniqueChords = array_unique($this->allChords);
    $chordData = array();
    
    error_log('ChordPress Debug: Processing ' . count($this->uniqueChords) . ' unique chords for ' . $cpressID);
    
    foreach ($this->uniqueChords as $uc) {
      if ($postid = $this->find_guitar_chord_post($uc)) {
        $chordVariations = array();
        
        // Original chord (transpose level 0)
        $this->load_guitar_chord($postid);
        $originalChordHtml = $this->C->createSvgChord(true);
        $chordVariations[0] = $originalChordHtml;
        
        // Generate all transposed versions
        if (isset($this->arrTransposeValues[$uc])) {
          for ($t = 1; $t <= 11; $t++) {
            $transposedChord = $this->arrTransposeValues[$uc][$t];
            if ($transposedPostId = $this->find_guitar_chord_post($transposedChord)) {
              $this->load_guitar_chord($transposedPostId);
              $transposedChordHtml = $this->C->createSvgChord(true);
              $chordVariations[$t] = $transposedChordHtml;
            }
            // Skip chord if transposed version doesn't exist (no post found)
          }
        }
        
        $chordData[$uc] = $chordVariations;
      }
    }
    
    // Add JavaScript data for chord sheet regeneration (always needed for transpose)
    $jsonData = wp_json_encode($chordData, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_SLASHES);
    $songsheetHtml .= "<script type='text/javascript'>\n";
    $songsheetHtml .= "console.log('About to set chord data for %cpressID%');\n";
    $songsheetHtml .= "console.log('Variable name will be: chordSheetData_%cpressID%');\n";
    $songsheetHtml .= "try {\n";
    $songsheetHtml .= "  var safeVarName = 'chordSheetData_' + '%cpressID%'.replace(/-/g, '_');\n";
    $songsheetHtml .= "  window[safeVarName] = " . $jsonData . ";\n";
    $songsheetHtml .= "  console.log('Chord data loaded for %cpressID% using variable:', safeVarName);\n";
    $songsheetHtml .= "  console.log('Total chords loaded:', Object.keys(window[safeVarName]).length);\n";
    $songsheetHtml .= "} catch(e) {\n";
    $songsheetHtml .= "  console.error('Error loading chord data for %cpressID%:', e);\n";
    $songsheetHtml .= "}\n";
    $songsheetHtml .= "</script>\n";
    
    error_log('ChordPress Debug: Generated script tag for ' . $cpressID . ' with ' . count($chordData) . ' chords');

    /**
     * Build chord sheet if option set to true.
     */
    if ($this->showChordSheet) {
      $chordSheetText = "\n<!-- ChordPress ChordSheet-->\n";
      $chordSheetText .= "<div class='cpress_chordsheet'>\n";
      $chordSheetText .= "<div><hr></div>\n";
      $chordSheetText .= "<p><i>" . esc_html(__('These known chords are used in this song', 'chordpress')) . ".</i></p>";
      $chordSheetText .= "<div id='chordsheet-content-%cpressID%'>\n";
      
      // Generate chord sheet display
      foreach ($this->uniqueChords as $uc) {
        if ($postid = $this->find_guitar_chord_post($uc)) {
          $this->load_guitar_chord($postid);
          $originalChordHtml = $this->C->createSvgChord(true);
          $chordSheetText .= $originalChordHtml;
        }
      }
      
      $chordSheetText .= "</div>\n"; // Close chordsheet-content div
      $chordSheetText .= "</div>\n
      <div style=\"clear:both; padding: 16px 0 16px 0;\"><hr></div>\n
      <!-- /ChordPress ChordSheet-->\n\n";
      if ($this->showChordSheetOnTop) {
        $songsheetHtml = $chordSheetText . $songsheetHtml;
      } else {
        $songsheetHtml = $songsheetHtml . $chordSheetText;
      }
    }

    // Final replacement of %cpressID% placeholders for any content added after the initial replacement
    $songsheetHtml = str_replace('%cpressID%', $cpressID, $songsheetHtml);

    // $this->C->dnd($songsheetHtml);
    return $songsheetHtml . "\n\n";
  }

  /**
   * --------------------------------------------------------------------------
   * Builds the Interactive Menu
   * --------------------------------------------------------------------------
   *
   * Builds the interaction menu for the ChordPress song sheet.
   *
   * This function generates the HTML for the interaction menu, which includes
   * options for font size adjustment, transposition, and printing. The menu
   * can be fixed at the bottom of the page if the `fixedInteraction` property
   * is set. The function also handles harmonica-specific options if applicable.
   *
   * @return string The HTML string for the interaction menu.
   * @since  3.9.2
   * @access private
   *
   */
  private function buildInteractionMenu(): string {
    $interactionForm = "
      <!-- ChordPress Interaction -->
      <div class=\"cpress-interaction cpress-row";

    if ($this->fixedInteraction) {
      $interactionForm .= " fixed";
    }

    $interactionForm .= "\">";

    if (!$this->hideFontsize) {
      $interactionForm .= "
        <div class='cpress-col cpress-mb-2'>" . __('Font Size', 'chordpress') . "<br>
          <div class='cpress-btn-group' role='group'>
            <button type='button' class='cpress-btn cpress-btn-secondary cpress-btn-sm' onclick=\"changeFontSize('%cpressID%', -5);\">-</button>
            <button id=\"btnFontSize-%cpressID%\"  type='button' class='cpress-btn cpress-btn-outline-secondary cpress-btn-sm' onclick=\"changeFontSize('%cpressID%', 0);\">100%</button>
            <button type='button' class='cpress-btn cpress-btn-secondary cpress-btn-sm' onclick=\"changeFontSize('%cpressID%', 5);\">+</button>
          </div>
        </div>";
    }

    if (!$this->hideTranspose) {
      $interactionForm .= "<div class='cpress-col' style='display: inline-block;'>";

      if (!$this->harmonica) {
        //
        // Guitar chords
        //
        if ($this->showNumericTranspose && !$this->harmonica) {
          //
          // Let's see if a 'Key' directive was given. If so, we can add the transposed keys to the list.
          //
          if (strlen($this->arrDirectives['key']) && in_array($this->arrDirectives['key'], $this->arrKeys)) {
            $songKey = "'" . $this->arrDirectives['key'] . "'";
            $initialText = '0 (' . $this->arrDirectives['key'] . ')';
          } else {
            $songKey = "''";
            $initialText = '0';
          }
          $interactionForm .= __('Transpose', 'chordpress') . "<br>
            <div class='cpress-btn-group' role='group'>
              <button type='button' class='cpress-btn cpress-btn-secondary cpress-btn-sm' onclick=\"transposeChords('%cpressID%', 'btnTranspose-', 'down', " . $songKey . ");\">-</button>
              <button id=\"btnTranspose-%cpressID%\" type='button' class='cpress-btn cpress-btn-outline-secondary cpress-btn-sm' onclick=\"transposeChords('%cpressID%', 'btnTranspose-', 'reset', " . $songKey . ");\" value='0'>" . $initialText . "</button>
              <button type='button' class='cpress-btn cpress-btn-secondary cpress-btn-sm' onclick=\"transposeChords('%cpressID%', 'btnTranspose-', 'up', " . $songKey . ");\">+</button>
            </div>";
        } else {
          //
          // Let's see if a 'Key' directive was given. If so, we can add the transposed keys to the list.
          //
          if (strlen($this->arrDirectives['key']) && in_array($this->arrDirectives['key'], $this->arrKeys)) {
            $i = 0;
            foreach ($this->arrTransposeValues[$this->arrDirectives['key']] as $val) {
              $tvalues[] = (string)$i . ' => ' . $val;
              $i++;
            }
          } else {
            $tvalues = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11');
          }
          $interactionForm .= __('Transpose', 'chordpress') . "
              <div class=\"float-right\">
                <select class=\"cpress-form-control cpress-form-control-sm cpress-py-0 cpress-px-2\" id=\"selTranspose-%cpressID%\" onchange=\"transposeChords('%cpressID%', 'selTranspose-');return false;\" title=\"" . __('Only works for international key names', 'chordpress') . ".\">
                  <option value=\"0\">" . $tvalues[0] . "</option>
                  <option value=\"1\">" . $tvalues[1] . "</option>
                  <option value=\"2\">" . $tvalues[2] . "</option>
                  <option value=\"3\">" . $tvalues[3] . "</option>
                  <option value=\"4\">" . $tvalues[4] . "</option>
                  <option value=\"5\">" . $tvalues[5] . "</option>
                  <option value=\"6\">" . $tvalues[6] . "</option>
                  <option value=\"7\">" . $tvalues[7] . "</option>
                  <option value=\"8\">" . $tvalues[8] . "</option>
                  <option value=\"9\">" . $tvalues[9] . "</option>
                  <option value=\"10\">" . $tvalues[10] . "</option>
                  <option value=\"11\">" . $tvalues[11] . "</option>
                </select>
              </div>";
        }
      } else {
        //
        // Harmonica notes
        //
        switch ($this->harmonica) {
          case 'tremolo':
            $selectedDiatonic = '';
            $selectedChromatic = '';
            $selectedTremolo = ' selected';
            break;
          case 'diatonic':
            $selectedDiatonic = ' selected';
            $selectedChromatic = '';
            $selectedTremolo = '';
            break;
          case 'chromatic':
            $selectedDiatonic = '';
            $selectedChromatic = ' selected';
            $selectedTremolo = '';
            break;
          default:
            $selectedDiatonic = '';
            $selectedChromatic = '';
            $selectedTremolo = '';
            break;
        }

        $interactionForm .=
          "<div class='cpress-row'>
              <div class='cpress-col'>";

        $interactionForm .= __('Harmonica', 'chordpress') . "<br>
            <select
              class=\"cpress-form-control cpress-form-control-sm cpress-py-0 cpress-px-2\"
              style=\"max-width: 200px;\"
              id=\"selTranspose-%cpressID%\"
              onchange=\"transposeHarmonica('%cpressID%', 'selTranspose-', this.value);return false;\"
              title=\"" . __('Only works when using harmonica notation', 'chordpress') . ".\"
            >
              <option value=\"diatonic\" " . $selectedDiatonic . ">" . __('Diatonic', 'chordpress') . "</option>
              <option value=\"chromatic\" " . $selectedChromatic . ">" . __('Chromatic', 'chordpress') . "</option>
              <option value=\"tremolo\" " . $selectedTremolo . ">" . __('Tremolo', 'chordpress') . "</option>
            </select>";

        $interactionForm .= "
            </div>
            <div class='cpress-col'>";

        if ($this->harmonica == 'chromatic') {
          $baseNote = "''";
          $initialText = '0';
          $interactionForm .= "<span id='btnTranspose-chromatic'>" . __('Transpose', 'chordpress') . "<br>
              <div class='cpress-btn-group' role='group'>
                <button type='button' class='cpress-btn cpress-btn-secondary cpress-btn-sm' onclick=\"transposeChromatic('%cpressID%', 'btnTranspose-', 'down', " . $baseNote . ");\">-</button>
                <button id=\"btnTranspose-%cpressID%\" type='button' class='cpress-btn cpress-btn-outline-secondary cpress-btn-sm' onclick=\"transposeChromatic('%cpressID%', 'btnTranspose-', 'reset', " . $baseNote . ");\" value='0'>" . $initialText . "</button>
                <button type='button' class='cpress-btn cpress-btn-secondary cpress-btn-sm' onclick=\"transposeChromatic('%cpressID%', 'btnTranspose-', 'up', " . $baseNote . ");\">+</button>
              </div>
            </span>";
        }
        $interactionForm .= "
              </div>
            </div>";
      }
      $interactionForm .= "</div>";
    }

    $interactionForm .= "<div class='cpress-col cpress-pr-0'>";
    if (!$this->hidePrint) {
      $printTitle = 'ChordPress SongSheet';
      if (strlen($this->arrDirectives['title']) && !$this->hideTitle) {
        $printTitle = $this->arrDirectives['title'];
      }
      $escapedStyles = htmlspecialchars($this->songsheetStyles, ENT_QUOTES);
      $printFunctionCall = "printElement('%cpressID%', '{$printTitle}', '{$escapedStyles}');";
      $printButton = "<br><button class='cpress-btn cpress-btn-secondary cpress-btn-sm' onclick=\"{$printFunctionCall}\">" . __('Print', 'chordpress') . "</button>";
      $interactionForm .= $printButton;
    }
    $interactionForm .= "</div>
      </div>\n
      <!-- /ChordPress Interaction -->\n";

    return $interactionForm;
  }

  /**
   * --------------------------------------------------------------------------
   * Format and Display Line.
   * --------------------------------------------------------------------------
   *
   * Process a single of the ChordPro text and return the formatted HTML.
   *
   * @param string $line - Single line from inside shortcode wrapper.
   *
   * @return string - Formatted HTML.
   * @since 1.0.0
   */
  public function formatAndDisplayLine(string $line): string {
    $returnText = "";

    /**
     * Array of chords
     */
    $arrChords = array();

    /**
     * Array of corresponding lyrics starting at a chord and ending prior
     * to the next chord or the end-of-line.
     */
    $arrLyrics = array();

    /**
     * Now check what this line is all about
     */
    if (substr_count($line, "[") == 0) {
      /**
       * No chords on this line
       */
      if (isset($line[0]) && ($line[0] == '#' || $line == '<br>' || $line == '<br />')) {
        /**
         * Line starts with "#": It's a comment line => return empty string
         */
        return "";
      } else if ((isset($line[0]) && $line[0] == '{') || (isset($line[0]) && strpos($line, '{'))) {
        /**
         * Line starts with "{" or has one in it: It's a directive
         * Some of them need consideration for the output
         */
        $directive = strstr($line, '{'); // Remove all before the {
        $arrDirective = explode(":", $directive);
        $directiveName = strtolower(str_replace('{', '', trim($arrDirective[0])));
        if (isset($arrDirective[1])) $directiveValue = substr(trim($arrDirective[1]), 0, strpos(trim($arrDirective[1]), '}'));

        /**
         * In case the directive had no colon and value, $directiveName will
         * still contain the trailing }. So let's remove that.
         */
        if (substr_count($directiveName, "}")) $directiveName = str_replace("}", "", $directiveName);

        if (strpos($directiveName, 'start_of_verse') !== false) {
          return "<div class=\"cpress_verse\">\n<div class=\"cpress_line\">\n";
        } elseif (strpos($directiveName, 'end_of_verse') !== false) {
          return "</div></div>\n";
        } elseif (strpos($directiveName, 'start_of_chorus') !== false) {
          return "<div class=\"cpress_chorus\">\n<div class=\"cpress_line\">\n";
        } elseif (strpos($directiveName, 'end_of_chorus') !== false) {
          return "</div></div>\n";
        } elseif (strpos($directiveName, 'start_of_monospace') !== false) {
          $this->inMonospace = true;
          return "<pre>\n";
        } elseif (strpos($directiveName, 'end_of_monospace') !== false) {
          $this->inMonospace = false;
          return "</pre>\n";
        } elseif (
          strpos($directiveName, 'title') !== false ||
          strpos($directiveName, 'subtitle') !== false ||
          strpos($directiveName, 'album') !== false ||
          strpos($directiveName, 'artist') !== false ||
          strpos($directiveName, 'capo') !== false ||
          strpos($directiveName, 'composer') !== false ||
          strpos($directiveName, 'key') !== false ||
          strpos($directiveName, 'tempo') !== false ||
          strpos($directiveName, 'time') !== false ||
          strpos($directiveName, 'year') !== false ||
          strpos($directiveName, 'artist') !== false
        ) {
          //
          // We have already processed these directives. Just return empty for this line.
          //
          return "";
        } elseif (strpos($directiveName, 'comment') !== false && !$this->hideComments) {
          $arrLyrics[] = '<span class="cpress_comment">' . esc_html($directiveValue) . '</span>';
        }
      } else {
        /**
         * Just lyrics without chords => print it
         */
        $arrLyrics[] = $line;
      }
    } elseif (substr_count($line, "[") != substr_count($line, "]")) {
      /**
       * Square bracket missing in this line
       */
      $arrLyrics[] = '<span style="color:#FF0000;"><i>' . esc_html(__('Missing square bracket in line', 'chordpress')) . ':</i></span> <code>' . $line . "</code>";
    } else {
      /**
       * Chords found. Split the line into segments beginning with '['
       */
      $arrBracketSegments = explode("[", $line);
      foreach ($arrBracketSegments as $segment) {
        if (strlen($segment)) {
          $pad = ($segment[strlen($segment) - 1] == ' ') ? '&nbsp;' : '';
          /**
           * Does the first segment start before the 1st '['?
           */
          if (substr_count($segment, "]") == 0) {
            $arrChords[] = " ";
            $arrLyrics[] = $segment . $pad;
          } else {
            /**
             * Split this segment into chord an lyrics
             */
            $arrChordLyric = explode("]", $segment);
            $arrChords[] = trim($arrChordLyric[0]);
            $arrLyrics[] = $arrChordLyric[1] . $pad;
          }
        }
      }
    }

    /**
     * Start of line wrapper (lyrics and chords)
     */
    $returnText .= "<div class=\"cpress_line\">\n";

    /**
     * Align chords and lyrics together by wrapping each in a floating inline div
     */
    if (!$this->hideChords) {

      for ($i = 0; $i < count($arrChords); $i++) {

        if (strlen(trim($arrChords[$i])) > 0 || strlen(trim($arrLyrics[$i])) > 0) {

          $lyrics = trim($this->stripTags($arrLyrics[$i]));

          /**
           * Start of line-section (one chord and its lyrics)
           */
          $returnText .= "<div class=\"cpress_line_section\">\n";

          if (strlen(trim($arrChords[$i])) > 0) {

            if (!$this->harmonica) {
              $returnText .= "<div class=\"chord chord-hidable-%cpressID% key-%cpressID%-0\"><span class=\"chordshort\">";
            } else {
              $this->harmonica = strtolower($this->harmonica);
              $returnText .= "<div class=\"chord chord-hidable-%cpressID% key-%cpressID%-" . esc_attr($this->harmonica) . "\"><span class=\"chordshort\">";
            }

            /**
             * Allow chords starting with allowed prefixes as chord placeholders. Just return the string.
             * Att: ... will end up as &ellip; . Use blanks between each dot.
             */
            if (in_array($arrChords[$i][0], $this->allowedChordPrefixes)) {
              $theChord = $arrChords[$i];
            } else {
              $theChord = $this->formatChord($arrChords[$i]);
              $this->allChords[] = $theChord;
            }

            $returnText .= esc_html($theChord) . "</span>&nbsp;</div>\n";

            if (!$this->harmonica) {
              /**
               * Prepare all other 11 transposed versions of the chord
               * but hide them. They need to be on the page for interactive
               * mode to show/hide them via Javascript.
               * But do not mark empty chord divs as hidable. They need to stay.
               */
              $returnText .= "\n";
              $tempTranspose = $this->getTranspose();

              for ($t = 1; $t <= 11; $t++) {
                $this->setTranspose($t);
                /**
                 * Allow chords starting with a special characters, e.g. ~ as chord placeholders.
                 * Just return the string.
                 */
                if (in_array($arrChords[$i][0], $this->allowedChordPrefixes)) {
                  $returnText .= '<div class="chord chord-hidable-%cpressID% key-%cpressID%-' . $t . '" style="display:none"><span class="chordshort">' . esc_html($arrChords[$i]) . '</span>&nbsp;</div>';
                  $returnText .= "\n";
                } else {
                  /**
                   * This is a real chord.
                   */
                  $theChord = $this->formatChord($arrChords[$i]);
                  $returnText .= '<div class="chord chord-hidable-%cpressID% key-%cpressID%-' . $t . '" style="display:none"><span class="chordshort">' . esc_html($theChord) . '</span>&nbsp;</div>';
                }
              }

              $this->setTranspose($tempTranspose);
            } else {
              /**
               * Prepare all other version of the harmonica note.
               * They need to be on the page for interactive mode to show/hide them via Javascript.
               * But do not mark empty chord divs as hidable. They need to stay.
               */
              $returnText .= "\n";
              foreach ($this->harmonicaTypes as $type) {
                if ($type !== $this->harmonica) {
                  //                  $this->dnd($theChord . "|" . $this->harmonica . "|" . $type . ": " . $this->translateHarmonicaNote($theChord, $this->harmonica, $type, false));
                  $returnText .= '<div class="chord chord-hidable-%cpressID% key-%cpressID%-' . esc_attr($type) . '" style="display:none"><span class="chordshort">' . esc_html($this->translateHarmonicaNote($theChord, $this->harmonica, $type)) . '</span>&nbsp;</div>';
                  $returnText .= "\n";
                }
                $returnText .= "\n";
              }
              /**
               * If harmonica type is 'chromatic' prepare all transposed values of the harmonica note.
               * They need to be on the page for interactive mode to show/hide them via Javascript.
               */
              if ($this->harmonica == 'chromatic') {
                for ($t = 1; $t <= 11; $t++) {
                  $returnText .= '<div class="chord chord-hidable-%cpressID% key-%cpressID%-chromatic-' . $t . '" style="display:none"><span class="chordshort">' . esc_html($this->transposeChromaticNote($theChord, $t)) . '</span>&nbsp;</div>';
                  $returnText .= "\n";
                }
                $returnText .= "\n";
              }
            }

            /**
             * Now add the lyrics for this section.
             */
            $returnText .= '<div class="lyric">';

            if (strlen($lyrics) > 0) {
              $endOf1stWord = strpos($lyrics, ' ');
              $numSpaces = substr_count($lyrics, ' ');
              if ($endOf1stWord > 0 && $numSpaces == 1) {
                $returnText .= esc_html(substr($lyrics, 0, $endOf1stWord)) . '&nbsp;';
                $lyrics = substr($lyrics, $endOf1stWord);
                $returnText .= "</div></div>\n<div class=\"cpress_line_section\">\n<div class=\"chord\">&nbsp;</div><div class=\"lyric\">";
              }
            }
          } else {
            $returnText .= '<div class="chord">&nbsp;</div>';
            $returnText .= '<div class="lyric">';
          }

          if (strlen($lyrics) > 0) $returnText .= esc_html($lyrics);
          $returnText .= "</div>\n"; // end of lyric
          $returnText .= "</div>\n"; // end of line

        }
      }

      /**
       * If there are more lyric parts than chord parts, add them here
       */
      for ($i = count($arrChords); $i < count($arrLyrics); $i++) {
        // Check if this is HTML content that shouldn't be escaped
        if (strpos($arrLyrics[$i], '<') !== false && strpos($arrLyrics[$i], '>') !== false) {
          $returnText .= $arrLyrics[$i];
        } else {
          $returnText .= esc_html($arrLyrics[$i]);
        }
      }
    } else {

      /**
       * Hide chords was selected, so only show the lyrics
       */
      $returnText .= '<div class="cpress_line_section"><div class="lyric">';

      for ($i = 0; $i < count($arrLyrics); $i++) {
        // Check if this is HTML content that shouldn't be escaped
        if (strpos($arrLyrics[$i], '<') !== false && strpos($arrLyrics[$i], '>') !== false) {
          $returnText .= $arrLyrics[$i];
        } else {
          $returnText .= esc_html($arrLyrics[$i]);
        }
      }

      $returnText .= "</div></div>";
    }

    /**
     * End of line wrapper
     */
    $returnText .= "</div>\n";

    /**
     * Add a clear:both div
     */
    $returnText .= "<div class='cpress_clear'></div>\n";

    return $returnText;
  }

  /**
   * --------------------------------------------------------------------------
   * Strip Tags.
   * --------------------------------------------------------------------------
   *
   * Remove all <> wrapped text from a string.
   *
   * @param string $string - String to process.
   *
   * @return string - Stripped text.
   * @since 1.0.0
   */
  private function stripTags(string $string): string {
    $retString = "";
    $isVisible = true;
    for ($i = 0; $i < strlen($string); $i++) {
      if ($isVisible) {
        if ($string[$i] == '<')
          $isVisible = false;
        else
          $retString .= $string[$i];
      } elseif ($string[$i] == '>') {
        $isVisible = true;
      }
    }
    return $retString;
  }

  /**
   * --------------------------------------------------------------------------
   * Format Chord.
   * --------------------------------------------------------------------------
   *
   * Processes a note text and returns the formatted HTML.
   *
   * @param string $ch - Chord text, e.g. "A" or "B#" or "Db/F" or "IV".
   *
   * @return string - Uppercase note string.
   * @since 1.0.0
   */
  public function formatChord(string $ch): string {
    $useFlats = false;
    if (strlen($ch) == 0) return '';

    /**
     * Return other valid chord identifiers as is
     */
    $italianChords = array('Do', 'do', 'Re', 're', 'Mi', 'mi', 'Fa', 'fa', 'Sol', 'sol', 'La', 'la', 'Si', 'si');
    $nashvilleChords = array('1', '2', '3', '4', '5', '6', '7');
    $romanChords = array('I', 'I7', 'II', 'III', 'iii', 'IV', 'V', 'V7', 'VI', 'vi', 'VI7', 'vi7', 'VII');
    if (in_array($ch, $italianChords) or in_array($ch, $nashvilleChords) or in_array($ch, $romanChords)) return $ch;

    /**
     * The first letter should be the key note
     */
    $note = substr($ch, 0, 1);
    $rem = substr($ch, 1);

    /**
     * Convert note to a number
     */
    $noteVal = $this->noteToNumber($note);

    /**
     * Check sharp/flat
     */
    if (strlen($rem) > 0) {
      if (substr($rem, 0, 1) == '#') {
        $noteVal++;
        $rem = substr($rem, 1);
      } elseif (strncasecmp($rem, "b", 1) == 0) {
        $useFlats = true;
        $noteVal--;
        $rem = substr($rem, 1);
      }
    }

    /**
     * Add transpose
     */
    $noteVal += $this->transpose;
    if ($noteVal > 11) $noteVal -= 12;
    if ($noteVal < 0) $noteVal += 12;

    /**
     * Build note to display from numeric note value
     */
    $xlatedNote = $this->numberToNote($noteVal, $useFlats);
    // die($this->pretty_dump($xlatedNote));

    /**
     * Return the chord display plus the bass note (if any)
     */
    return $xlatedNote . $this->formatRemainder($rem);
  }

  /**
   * --------------------------------------------------------------------------
   * Format Chromatic Note.
   * --------------------------------------------------------------------------
   *
   * Processes a Chromatic harmonica note and returns the formatted HTML.
   *
   * @param string $note      - Chromatic note, e.g. "1" or "1s" or "-1" or "-1s".
   * @param int    $transpose - Numeric transpose value, 0-11.
   *
   * @return string - Transposed chromatic note
   * @since 2.8.0
   */
  public function transposeChromaticNote(string $note, int $transpose): string {
    if (strlen($note) == 0) return '';
    if ($transpose === 0 || $transpose > 11) return $note;

    /**
     * Find the submitted note in the chromaticNotes array
     */
    if (array_key_exists($note, $this->arrChromaticTranspose)) {
      $noteToTranspose = $this->arrChromaticTranspose[$note];
      // $noteToTranspose now contains the array object for the submitted note
    } else {
      $noteToTranspose = false; // Note not found
    }

    if ($noteToTranspose !== false) {
      /**
       * Value found. Add transpose
       */
      return $noteToTranspose[$transpose];
    } else {
      /**
       * Value not found
       */
      return $note;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Format Remainder.
   * --------------------------------------------------------------------------
   *
   * Process the remainder of the given chord.
   *
   * @param string $remainder - Chord text starting at second char.
   *
   * @return string - Formatted remainder.
   * @since 1.0.0
   */
  public function formatRemainder(string $remainder): string {
    $indexOfSlash = strpos($remainder, '/');
    if ($indexOfSlash === false) return $remainder;

    /**
     * We have a slash, indicating that a bass note follows.
     * Break the string into 2 parts at the slash.
     * The slash will be lost, and will need to be added back later.
     */
    $chord = substr($remainder, 0, $indexOfSlash);
    $bass = substr($remainder, $indexOfSlash + 1);

    $useFlats = false;

    if (strlen($bass) == 0) return $chord . "/?";

    /**
     * The first letter should be the bass note
     */
    $note = substr($bass, 0, 1);
    $xlatedNote = $note;
    $rem = substr($bass, 1);

    /**
     * Convert note to a number
     */
    $noteVal = $this->noteToNumber($note);

    /**
     * Check sharp/flat
     */
    if (strlen($rem) > 0) {
      if (substr($rem, 0, 1) == '#') {
        $noteVal++;
        $rem = substr($rem, 1);
      } elseif (strncasecmp($rem, "b", 1) == 0) {
        $useFlats = true;
        $noteVal--;
        $rem = substr($rem, 1);
      }
    }

    /**
     * Add transpose
     */
    $noteVal += $this->transpose;
    if ($noteVal > 11) $noteVal -= 12;
    if ($noteVal < 0) $noteVal += 12;

    /**
     * Build note to display from numeric note value
     */
    $xlatedNote = $this->numberToNote($noteVal);

    return $chord . "/" . $xlatedNote . $rem;
  }

  /**
   * --------------------------------------------------------------------------
   * Note to Number.
   * --------------------------------------------------------------------------
   *
   * Convert note to number.
   *
   * @param string $n - Note to convert.
   *
   * @return integer
   * @since 1.0.0
   */
  public function noteToNumber(string $n): int {
    $regularNotation = array(
      'A' => 0,
      'B' => 2,
      'C' => 3,
      'D' => 5,
      'E' => 7,
      'F' => 8,
      'G' => 10,
    );

    $hbNotation = array(
      'A' => 0,
      'B' => 1,
      'H' => 2,
      'C' => 3,
      'D' => 5,
      'E' => 7,
      'F' => 8,
      'G' => 10,
    );

    if (in_array(strtolower($this->hbNotation), array("yes", "1", "true"))) {
      if (array_key_exists(strtoupper($n), $hbNotation)) {
        return $hbNotation[strtoupper($n)];
      } else {
        return 0;
      }
    } else {
      if (array_key_exists(strtoupper($n), $regularNotation)) {
        return $regularNotation[strtoupper($n)];
      } else {
        return 0;
      }
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Number to Note.
   * --------------------------------------------------------------------------
   *
   * Convert number to note.
   *
   * @param integer $n        - Numeric note value to convert to note string.
   * @param boolean $useFlats - Whether to use flat notes or not.
   *
   * @return string
   * @since 1.0.0
   */
  public function numberToNote(string $n, bool $useFlats = false): string {
    $retVal = '';

    switch ($n) {
      case 0:
        $retVal = 'A';
        break;
      case 1:
        if ($this->displayHBNotation) {
          $retVal = 'B';
        } else {
          if ($useFlats) $retVal = "Bb";
          else $retVal = "A#";
        }
        break;
      case 2:
        if ($this->displayHBNotation) {
          $retVal = 'H';
        } else {
          $retVal = 'B';
        }
        break;
      case 3:
        $retVal = 'C';
        break;
      case 4:
        if ($useFlats) $retVal = "Db";
        else $retVal = "C#";
        break;
      case 5:
        $retVal = 'D';
        break;
      case 6:
        if ($useFlats) $retVal = "Eb";
        else $retVal = "D#";
        break;
      case 7:
        $retVal = 'E';
        break;
      case 8:
        $retVal = 'F';
        break;
      case 9:
        if ($useFlats) $retVal = "Gb";
        else $retVal = "F#";
        break;
      case 10:
        $retVal = 'G';
        break;
      case 11:
        if ($useFlats) $retVal = "Ab";
        else $retVal = "G#";
        break;
    }
    return $retVal;
  }

  /**
   * --------------------------------------------------------------------------
   * Translate Harmonica Note.
   * --------------------------------------------------------------------------
   *
   * Translates a given harmonica note and source type to the given target type.
   *
   * @param string $note - Harmonica note to translate
   * @param string $from - Source harmonica type
   * @param string $to   - Target harmonica type
   *
   * @return string
   * @since 3.8.0
   */
  public function translateHarmonicaNote(string $note, string $from, string $to): string {

    $from = strtolower($from);
    $to = strtolower($to);
    $note = strtolower($note);
    $translatedNote = '';

    if ($from === $to) return $note;
    if (!in_array($from, $this->harmonicaTypes) || !in_array($to, $this->harmonicaTypes)) return false;

    switch ($from) {

      case 'tremolo':

        $fromNotes = array_filter($this->harmonicaNotes, function ($item) use ($note) {
          return isset($item['tremolo']) && $item['tremolo'] === $note;
        });
        if (!count($fromNotes)) return '-'; // The original note is not in the harmonica notes array.

        switch ($to) {
          case 'diatonic':
            $toValues = array_map(function ($item) {
              return $item['diatonic'];
            }, $fromNotes);
            $translatedNote = reset($toValues);
            break;

          default: // case 'chromatic'
            $toValues = array_map(function ($item) {
              return $item['chromatic'];
            }, $fromNotes);
            $translatedNote = reset($toValues);
            break;
        }
        break;

      case 'diatonic':

        $fromNotes = array_filter($this->harmonicaNotes, function ($item) use ($note) {
          return isset($item['diatonic']) && $item['diatonic'] === $note;
        });
        if (!count($fromNotes)) return '-'; // The original note is not in the harmonica notes array.

        switch ($to) {
          case 'tremolo':
            $toValues = array_map(function ($item) {
              return $item['tremolo'];
            }, $fromNotes);
            $translatedNote = reset($toValues);
            break;

          default: // case 'chromatic'
            $toValues = array_map(function ($item) {
              return $item['chromatic'];
            }, $fromNotes);
            $translatedNote = reset($toValues);
            break;
        }
        break;

      default: // case 'chromatic':

        $fromNotes = array_filter($this->harmonicaNotes, function ($item) use ($note) {
          return isset($item['chromatic']) && $item['chromatic'] === $note;
        });
        if (!count($fromNotes)) return '-'; // The original note is not in the harmonica notes array.

        switch ($to) {
          case 'diatonic':
            $toValues = array_map(function ($item) {
              return $item['diatonic'];
            }, $fromNotes);
            $translatedNote = reset($toValues);
            break;

          default: // case 'tremolo'
            $toValues = array_map(function ($item) {
              return $item['tremolo'];
            }, $fromNotes);
            $translatedNote = reset($toValues);
            break;
        }
        break;
    }

    return $translatedNote;
  }

  /**
   * --------------------------------------------------------------------------
   * Get Display HB Notation.
   * --------------------------------------------------------------------------
   *
   * Get class variable: displayHBNotation.
   *
   * @return boolean
   * @since 1.0.0
   */
  public function getDisplayHBNotation(): bool {
    return $this->displayHBNotation;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Display HB Notation.
   * --------------------------------------------------------------------------
   *
   * Set class variable: displayHBNotation.
   *
   * @param boolean $g
   *
   * @since 1.0.0
   */
  public function setDisplayHBNotation(bool $g): void {
    $this->displayHBNotation = $g;
  }

  /**
   * --------------------------------------------------------------------------
   * Get Float.
   * --------------------------------------------------------------------------
   *
   * Get class variable: float.
   *
   * @return string
   * @since 1.0.0
   */
  public function getFloat(): string {
    return $this->float;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Float.
   * --------------------------------------------------------------------------
   *
   * Set class variable: float.
   *
   * @param string $f - left/right/none/inherit.
   *
   * @since 1.0.0
   */
  public function setFloat(string $f): void {
    $this->float = $f;
  }

  /**
   * --------------------------------------------------------------------------
   * Get Harmonica.
   * --------------------------------------------------------------------------
   *
   * Get class variable: harmonica.
   *
   * @return string
   * @since 3.8.0
   */
  public function getHarmonica(): string {
    return $this->harmonica;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Harmonica.
   * --------------------------------------------------------------------------
   *
   * Set class variable: harmonica.
   *
   * @param string $g - tremolo/diatonic/chromatic.
   *
   * @since 3.8.0
   */
  public function setHarmonica(string $h): void {
    if (in_array(strtolower($h), $this->harmonicaTypes)) {
      $this->harmonica = strtolower($h);
    } else {
      $this->harmonica = false;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get HB Notation.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hbNotation.
   *
   * @return string -yes/no.
   * @since 1.0.0
   */
  public function getHBNotation(): string {
    return $this->hbNotation;
  }

  /**
   * --------------------------------------------------------------------------
   * Set HB Notation.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hbNotation.
   *
   * @param string $g - yes/no.
   *
   * @since 1.0.0
   */
  public function setHBNotation(string $g): void {
    $this->hbNotation = $g;
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Chords.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hideChords.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getHideChords(): string {
    return $this->hideChords;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Chords.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hideChords.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setHideChords(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hideChords = true;
    } else {
      $this->hideChords = false;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Comments.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hideComments.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getHideComments(): string {
    return $this->hideComments;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Comments.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hideComments.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setHideComments(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hideComments = 1;
    } else {
      $this->hideComments = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Composer.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hideComposer.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getHideComposer(): string {
    return $this->hideComposer;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Composer.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hideComposer.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setHideComposer(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hideComposer = 1;
    } else {
      $this->hideComposer = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Print.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hidePrint.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getHidePrint(): string {
    return $this->hidePrint;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Print.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hidePrint.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setHidePrint(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hidePrint = 1;
    } else {
      $this->hidePrint = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Subtitle.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hideSubtitle.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getHideSubtitle(): string {
    return $this->hideSubtitle;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Subtitle.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hideSubtitle.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setHideSubtitle(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hideSubtitle = 1;
    } else {
      $this->hideSubtitle = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Title.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hideTitle.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getHideTitle(): string {
    return $this->hideTitle;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Title.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hideTitle.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setHideTitle(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hideTitle = 1;
    } else {
      $this->hideTitle = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Fontsize.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hideFontsize.
   *
   * @return string -yes/no.
   * @since 3.8.1
   */
  public function getFontsize(): string {
    return $this->hideFontsize;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Fontsize.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hideFontsize.
   *
   * @param string $g - yes/no.
   *
   * @since 3.8.1
   */
  public function setHideFontsize(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hideFontsize = 1;
    } else {
      $this->hideFontsize = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Transpose.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hideTranspose.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getHideTranspose(): string {
    return $this->hideTranspose;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Transpose.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hideTranspose.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setHideTranspose(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hideTranspose = 1;
    } else {
      $this->hideTranspose = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Numeric Transpose.
   * --------------------------------------------------------------------------
   *
   * Get class variable: showNumericTranspose.
   *
   * @return string -yes/no.
   * @since 3.6.0
   */
  public function getNumericTranspose(): string {
    return $this->showNumericTranspose;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Numeric Transpose.
   * --------------------------------------------------------------------------
   *
   * Set class variable: showNumericTranspose.
   *
   * @param string $g - yes/no.
   *
   * @since 3.6.0
   */
  public function setNumericTranspose(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->showNumericTranspose = 1;
    } else {
      $this->showNumericTranspose = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Fixed Interaction.
   * --------------------------------------------------------------------------
   *
   * Get class variable: fixedInteraction.
   *
   * @return string -yes/no.
   * @since 3.6.0
   */
  public function getFixedInteraction(): string {
    return $this->fixedInteraction;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Fixed Interaction.
   * --------------------------------------------------------------------------
   *
   * Set class variable: fixedInteraction.
   *
   * @param string $g - yes/no.
   *
   * @since 3.6.0
   */
  public function setFixedInteraction(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->fixedInteraction = 1;
    } else {
      $this->fixedInteraction = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Hide Year.
   * --------------------------------------------------------------------------
   *
   * Get class variable: hideYear.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getHideYear(): string {
    return $this->hideYear;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Hide Year.
   * --------------------------------------------------------------------------
   *
   * Set class variable: hideYear.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setHideYear(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->hideYear = 1;
    } else {
      $this->hideYear = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Interactive.
   * --------------------------------------------------------------------------
   *
   * Get class variable: interactive.
   *
   * @return string - yes/no.
   * @since 1.0.0
   */
  public function getInteractive(): string {
    return $this->interactive;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Interactive.
   * --------------------------------------------------------------------------
   *
   * Set class variable: interactive.
   *
   * @param string $g - yes/no.
   *
   * @since 1.0.0
   */
  public function setInteractive(string $i): void {
    $this->interactive = $i;
  }

  /**
   * --------------------------------------------------------------------------
   * Get Show Chord Sheet.
   * --------------------------------------------------------------------------
   *
   * Get class variable: showChordSheet.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getShowChordSheet(): string {
    return $this->showChordSheet;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Show Chord Sheet.
   * --------------------------------------------------------------------------
   *
   * Set class variable: showChordSheet.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setShowChordSheet(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->showChordSheet = 1;
    } else {
      $this->showChordSheet = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Show Chord Sheet On Top.
   * --------------------------------------------------------------------------
   *
   * Get class variable: showChordSheetOnTop.
   *
   * @return string -yes/no.
   * @since 3.5.0
   */
  public function getShowChordSheetOnTop(): string {
    return $this->showChordSheetOnTop;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Show Chord Sheet On Top.
   * --------------------------------------------------------------------------
   *
   * Set class variable: showChordSheetOnTop.
   *
   * @param string $g - yes/no.
   *
   * @since 3.5.0
   */
  public function setShowChordSheetOnTop(string $g): void {
    if (in_array(strtolower($g), array("yes", "1", "true"))) {
      $this->showChordSheetOnTop = 1;
    } else {
      $this->showChordSheetOnTop = 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Get Transpose.
   * --------------------------------------------------------------------------
   *
   * Get class variable: transpose.
   *
   * @return integer
   * @since 1.0.0
   */
  public function getTranspose(): int {
    return $this->transpose;
  }

  /**
   * --------------------------------------------------------------------------
   * Set Transpose.
   * --------------------------------------------------------------------------
   *
   * Sets class variable: transpose.
   *
   * @param integer $t - Positive or negative number of steps for transponation.
   *
   * @since 1.0.0
   */
  public function setTranspose(int $t): void {
    $this->transpose = $t;
  }

  /**
   * --------------------------------------------------------------------------
   * Find Guitar Chord Post.
   * --------------------------------------------------------------------------
   *
   * Find a guitar chord type post by shortname.
   *
   * @param string $shortname - Shortname of the guitar post to look for.
   *
   * @return integer - Post ID or NULL
   */
  private function find_guitar_chord_post(string $shortname): int {
    $query_args = array(
      'posts_per_page' => 1,
      'post_type' => 'guitar_chord',
      'meta_key' => 'guitar_chord_name',
      'meta_value' => $shortname
    );
    $query_result = new WP_Query($query_args);

    if (array_key_exists(0, $query_result->posts)) {
      return $query_result->posts[0]->ID;
    } else {
      return 0;
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Load Guitar Chord.
   * --------------------------------------------------------------------------
   *
   * Load a guitar chord based on post ID.
   *
   * @param integer $postid - Guitar chord post ID.
   *
   * @return void
   */
  private function load_guitar_chord(int $postid): void {
    $my_chord = array();
    $post = get_post($postid);

    /**
     * Retrieve meta data from chord post
     */
    $guitar_chord_name = get_post_meta($postid, 'guitar_chord_name', true);
    if (get_post_meta($postid, 'guitar_chord_barres', true)) {
      $guitar_chord_barres = get_post_meta($postid, 'guitar_chord_barres', true);
    } else {
      $guitar_chord_barres = "";
    }
    $guitar_chord_fingers = get_post_meta($postid, 'guitar_chord_fingers', true);
    $guitar_chord_frets = get_post_meta($postid, 'guitar_chord_frets', true);
    $guitar_chord_strings = get_post_meta($postid, 'guitar_chord_strings', true);
    $guitar_chord_position = get_post_meta($postid, 'guitar_chord_position', true);
    $guitar_chord_tuning = get_post_meta($postid, 'guitar_chord_tuning', true);

    /**
     * Set chord properties
     */
    $this->C->setProperty('title', $guitar_chord_name);
    $this->C->setBarres($guitar_chord_barres);
    $this->C->setProperty('fingers', $this->C->replace_brackets($guitar_chord_fingers));
    $this->C->setProperty('frets', $guitar_chord_frets);
    $this->C->setProperty('position', $guitar_chord_position);
    $this->C->setProperty('strings', $guitar_chord_strings);
    $this->C->setTuning($guitar_chord_tuning);
  }

  /**
   * Create a modal guitar chord dialog.
   *
   * @param integer $postid - ID of the chord post
   * @param string  $chord  - Vexchord HTML
   *
   * @return string - Modal chord dialog HTML
   */
  //    private function create_modal_chord(int $postid, string $chord): string {
  //        $modal_chord = '<!-- <button id="cpress-chord-' . $postid . '-modal-button">Click here to trigger the modal!</button> -->
  //        <div id="cpress-chord-' . $postid . '-modal" class="cpress-modal">
  //            <div class="cpress-modal-content">
  //                <span id="cpress-chord-' . $postid . '-modal-close" class="cpress-modal-close">&times;</span>
  //                ' . $chord . '
  //            </div>
  //        </div>
  //        <script>
  //        var cpress_' . $postid . '_modal = document.getElementById("cpress-chord-' . $postid . '-modal");
  //        var cpress_' . $postid . '_btn = document.getElementById("cpress-chord-' . $postid . '-modal-button");
  //        var cpress_' . $postid . '_close = document.getElementById("cpress-chord-' . $postid . '-modal-close");
  //
  //        cpress_' . $postid . '_btn.onclick = function() {
  //            cpress_' . $postid . '_modal.style.display = "block";
  //        }
  //
  //        cpress_' . $postid . '_close.onclick = function() {
  //            cpress_' . $postid . '_modal.style.display = "none";
  //        }
  //
  //        window.onclick = function(event) {
  //            if (event.target == cpress_' . $postid . '_modal) {
  //                cpress_' . $postid . '_modal.style.display = "none";
  //            }
  //        }
  //        </script>';
  //
  //        return $modal_chord;
  //    }

  /**
   * --------------------------------------------------------------------------
   * Random String.
   * --------------------------------------------------------------------------
   *
   * Generate random string.
   *
   * @param boolean $alphaonly - Whether to only use alpha charaters
   * @param integer $length    - Length of random string to generate
   *
   * @return false
   */
  private function random_string(bool $alphaonly = false, int $length = 15): string {
    if ($alphaonly) {
      return substr(str_shuffle(str_repeat($x = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    } else {
      return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
  }

  /**
   * --------------------------------------------------------------------------
   * Dump and Die.
   * --------------------------------------------------------------------------
   *
   * @param array $a   Array to print out pretty
   * @param bool  $die Flag to die after dump or not
   *
   * @return string
   * @since 3.2.0
   */
  private function dnd(array $a, bool $die = true): string {
    $dump = highlight_string("<?php\n\$data =\n" . var_export($a, true) . ";\n?>");
    if ($die) {
      die($dump);
    } else {
      return $dump;
    }
  }
}
