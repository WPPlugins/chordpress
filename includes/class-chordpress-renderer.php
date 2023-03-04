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
class ChordPress_Renderer
{
    /**
     * Guitar chord instance.
     *
     * @since    2.1.0
     * @access   private
     * @var      ChordPress_Chord    $C    Guitar chord instance.
     */
    private $C;

    /**
     * Display H/B notation flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $displayHBNotation    Display H/B notation flag.
     */
    private $displayHBNotation;

    /**
     * Hide album flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideAlbum    Hide album flag.
     */
    private $hideAlbum;

    /**
     * Hide artist flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideArtist    Hide artist flag.
     */
    private $hideArtist;

    /**
     * Hide chords flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideChords    Hide chords flag.
     */
    private $hideChords;

    /**
     * Hide comments flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideComments    Hide comments flag.
     */
    private $hideComments;

    /**
     * Hide composer flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideComposer    Hide composer flag.
     */
    private $hideComposer;

    /**
     * Hide subtitle flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideSubtitle    Hide subtitle flag.
     */
    private $hideSubtitle;

    /**
     * Hide print button.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hidePrint    Hide print button.
     */
    private $hidePrint;

    /**
     * Hide title flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideTitle    Hide title flag.
     */
    private $hideTitle;

    /**
     * Hide transpose button.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideTranspose    Hide transpose button
     */
    private $hideTranspose;

    /**
     * Hide year flag.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean    $hideYear    Hide year flag.
     */
    private $hideYear;

    /**
     * HTML header level for title.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $titleLevel    HTML header level for title.
     */
    private $titleLevel;

    /**
     * HTML header level for subtitle.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $subtitleLevel    HTML header level for subtitle.
     */
    private $subtitleLevel;

    /**
     * CSS styles for meta info.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $metaStyle    CSS styles for meta info.
     */
    private $metaStyle;

    /**
     * CSS text color for chords.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $chordTextcolor    CSS text color for chords.
     */
    private $chordTextcolor;

    /**
     * CSS text size for chords.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $chordTextsize    CSS text size for chords.
     */
    private $chordTextsize;

    /**
     * CSS text color for lyrics.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $lyricsTextcolor    CSS text color for lyrics.
     */
    private $lyricsTextcolor;

    /**
     * CSS text size for lyrics.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $lyricsTextsize    CSS text size for lyrics.
     */
    private $lyricsTextsize;

    /**
     * CSS styles for lyrics.
     *
     * @since    2.3.0
     * @access   private
     * @var      string    $lyricsStyle    CSS styles for lyrics.
     */
    private $lyricsStyle;

    /**
     * CSS styles for comments.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $commentStyle    CSS styles for comments.
     */
    private $commentStyle;

    /**
     * CSS styles for the chords.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $chordStyle    CSS styles for the chorus sections.
     */
    private $chordStyle;

    /**
     * CSS styles for the chorus sections.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $chorusStyle    CSS styles for the chorus sections.
     */
    private $chorusStyle;

    /**
     * CSS styles for the verse sections.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $verseStyle    CSS styles for the verse sections.
     */
    private $verseStyle;

    /**
     * CSS styles for the line sections.
     *
     * @since    3.1.2
     * @access   private
     * @var      string    $lineStyle    CSS styles for the line sections.
     */
    private $lineStyle;

    /**
     * CSS float directive: no/left/right.
     *
     * @since    1.5.0
     * @access   private
     * @var      string    $float    CSS float directive: no/left/right.
     */
    private $float;

    /**
     * Shortcode directive hbNotation: no/yes.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $hbNotation    Shortcode directive hbNotation: no/yes.
     */
    private $hbNotation;

    /**
     * Show Chord Sheet for Vexchords.
     *
     * @since    2.1.0
     * @access   private
     * @var      string    $showChordSheet    Show Chord Sheet: no/yes.
     */
    private $showChordSheet;

    /**
     * Show Chord Sheet on top.
     *
     * @since    2.1.0
     * @access   private
     * @var      string    $showChordSheetOnTop    Show Chord Sheet on top: no/yes.
     */
    private $showChordSheetOnTop;

    /**
     * Shortcode directive interactive: no/yes.
     *
     * @since    1.5.0
     * @access   private
     * @var      string    $interactive    Shortcode directive interactive: no/yes.
     */
    private $interactive;

    /**
     * Shortcode directive transpose: 0-11 (semitones).
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $transpose    Shortcode directive transpose: 0-11 (semitones).
     */
    private $transpose;

    /**
     * Array of supported ChordPro directives.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $arrDirectives    Array of supported ChordPro directives.
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
     * Array of all chords in this song.
     *
     * @since    2.1.0
     * @access   private
     * @var      array    $allChords    Array of all chords in this song.
     */
    private $allChords = array();

    /**
     * Array of unique chords in this song.
     *
     * @since    2.1.0
     * @access   private
     * @var      array    $uniqueChords    Array of unique chords in this song.
     */
    private $uniqueChords = array();

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
        'A' =>  array('A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#'),
        'Am' =>  array('Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m'),
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


    //--------------------------------------------------------------------------
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
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
        if (!$this->hidePrint = get_option('chordpress_checkbox_hide_print')) $this->hidePrint = 0;
        if (!$this->hideSubtitle = get_option('chordpress_checkbox_hide_subtitle')) $this->hideSubtitle = 0;
        if (!$this->hideTitle = get_option('chordpress_checkbox_hide_title')) $this->hideTitle = 0;
        if (!$this->hideTranspose = get_option('chordpress_checkbox_hide_transpose')) $this->hideTranspose = 0;
        if (!$this->hideYear = get_option('chordpress_checkbox_hide_year')) $this->hideYear = 0;
        if (!$this->displayHBNotation = get_option('chordpress_checkbox_hb_notation')) $this->displayHBNotation = 0;
        if (!$this->showChordSheet = get_option('chordpress_checkbox_show_chord_sheet')) $this->showChordSheet = 0;
        if (!$this->showChordSheetOnTop = get_option('chordpress_checkbox_show_chord_sheet_on_top')) $this->showChordSheetOnTop = 0;

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

    //--------------------------------------------------------------------------
    /**
     * Get the ChordPro directives and store the supported ones in an array.
     *
     * @since    1.0.0
     * @param    object    $text    Text inside shortcode wrapper.
     */
    public function getDirectives($text)
    {
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

    //--------------------------------------------------------------------------
    /**
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
     * @since    1.0.0
     * @param    string    $text    Text inside shortcode wrapper.
     * @return   string             Formatted HTML of the ChordPro text.
     */
    public function displayText($text)
    {
        /**
         * Start of ChordPress wrapper
         */
        $returnText = "\n\n
        <!-- ChordPress SongSheet Start -->
        <div id=\"%cpressID%\" class=\"cpress\">\n";

        /**
         * Style sheet
         */
        $returnText .=
            '<style>
                div.cpress { float: ' . $this->float . '; }
                div.cpress_line { ' . $this->lineStyle . '; }
                div.cpress_line_section { display: inline; float: left; }
                div.cpress_line_section .chord .chordshort { ' . $this->chordStyle . '; }
                div.cpress_line_section .lyric { ' . $this->lyricsStyle . '; }
                div.cpress_chorus { ' . $this->chorusStyle . '; }
                div.cpress_verse { ' . $this->verseStyle . '; }
                div.cpress_clear { clear: both; }
                div.cpress_meta { ' . $this->metaStyle . ' }
                div.cpress_interaction { float: right; }
                div.cpress_interaction select { width: auto; }
                span.cpress_comment { ' . $this->commentStyle . ' }
            </style>';

        /**
         * Interactive transpose / print
         */
        if (strtolower($this->interactive) == "yes") {
            $returnText .= "<div class=\"cpress_interaction\">";
            $transposeForm = "";
            $printForm = "";
            if (!$this->hideTranspose) {
                //
                // Let's see if a 'Key' directive was given. If so, we can add
                // the transposed keys to the list.
                //
                if (strlen($this->arrDirectives['key']) && in_array($this->arrDirectives['key'], $this->arrKeys)) {
                    $i = 0;
                    foreach ($this->arrTransposeValues[$this->arrDirectives['key']] as $val) {
                        $tvalues[] = (string) $i . ' => ' . $val;
                        $i++;
                    }
                } else {
                    $tvalues = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11');
                }

                $transposeForm = __('Transpose up', 'chordpress') . "
                <select id=\"selTranspose-%cpressID%\" onchange=\"transposeChords('%cpressID%');return false;\">
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
                    <option value=\"12\">" . __('No chords', 'chordpress') . "</option>
                </select>";
            }
            if (!$this->hidePrint) {
                $printForm = "<br>
                <button onclick=\"printCpress('%cpressID%');return false;\" style=\"float:right;margin-top:8px;\">" . __('Print', 'chordpress') . "</button>";
            }
            $returnText .= $transposeForm . $printForm . "</div>";
        }

        /**
         * Print header if directives exist
         */
        $randId = $this->random_string();
        if (strlen($this->arrDirectives['title']) && !$this->hideTitle) {
            $cpressID = str_replace(' ', '-', strtolower(trim($this->arrDirectives['title']))) . '-' . $randId;
            $returnText = str_replace('%cpressID%', $cpressID, $returnText);
            $returnText .= '<' . $this->titleLevel . '>' . $this->arrDirectives['title'] . '</' . $this->titleLevel . '>';
        } else {
            $cpressID = 'ccpress-id-' . $randId;
            $returnText = str_replace('%cpressID%', $cpressID, $returnText);
        }

        if (strlen($this->arrDirectives['subtitle']) && !$this->hideSubtitle) {
            $returnText .= '<' . $this->subtitleLevel . '>' . $this->arrDirectives['subtitle'] . '</' . $this->subtitleLevel . '>';
        }

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
            $returnText .= '<div class="cpress_meta">';
            if (strlen($this->arrDirectives['composer']) && !$this->hideComposer) $returnText .= esc_html(__('Composer', 'chordpress')) . ': ' . $this->arrDirectives['composer'] . '<br>';
            if (strlen($this->arrDirectives['artist']) && !$this->hideArtist)     $returnText .= esc_html(__('Artist', 'chordpress')) . ': ' . $this->arrDirectives['artist'] . '<br>';
            if (strlen($this->arrDirectives['year']) && !$this->hideYear)         $returnText .= esc_html(__('Year', 'chordpress')) . ': ' . $this->arrDirectives['year'] . '<br>';
            if (strlen($this->arrDirectives['album']) && !$this->hideAlbum)       $returnText .= esc_html(__('Album', 'chordpress')) . ': ' . $this->arrDirectives['album'] . '<br>';

            if (strlen($this->arrDirectives['key'])) {
                $returnText .= esc_html(__('Key (original)', 'chordpress')) . ': ' . $this->arrDirectives['key'] . '<br>';
                $returnText .= esc_html(__('Key (transposed)', 'chordpress')) . ': <span class="chord-hidable-%cpressID% key-%cpressID%-0">' . $this->arrDirectives['key'] . '</span>';
                for ($t = 1; $t <= 11; $t++) {
                    $returnText .= '<span class="chord-hidable-%cpressID% key-%cpressID%-' . $t . '" style="display:none">' . $this->arrTransposeValues[$this->arrDirectives['key']][$t] . '</span>';
                }
                $returnText .= '<br>';
            }

            if (strlen($this->arrDirectives['time']))                             $returnText .= esc_html(__('Time', 'chordpress')) . ': ' . $this->arrDirectives['time'] . '<br>';
            if (strlen($this->arrDirectives['tempo']))                            $returnText .= esc_html(__('Tempo', 'chordpress')) . ': ' . $this->arrDirectives['tempo'] . '<br>';
            if (strlen($this->arrDirectives['capo']))                             $returnText .= esc_html(__('Capo', 'chordpress')) . ': ' . $this->arrDirectives['capo'] . '<br>';
            $returnText .= '</div>';
        }

        /**
         * Go through each line and process it
         */
        foreach ($text as $line) {
            $returnText .= str_replace('%cpressID%', $cpressID, $this->formatAndDisplayLine($line));
            // $returnText .= $this->formatAndDisplayLine($line);
        }

        /**
         * End of ChordPress song
         */
        $returnText .= "</div> <!-- ChordPress SongSheet End -->\n";

        /**
         * Build chord sheet (if option set to true)
         */
        if ($this->showChordSheet) {
            $chordSheetText  = "\n<!-- ChordPress ChordSheet Start -->\n";
            $chordSheetText .= "<div><hr></div>\n";
            $chordSheetText .= "<p><i>" . esc_html(__('These known chords are used in this song', 'chordpress')) . ".</i></p>";

            $this->uniqueChords = array_unique($this->allChords);

            foreach ($this->uniqueChords as $uc) {
                if ($postid = $this->find_guitar_chord_post($uc)) {
                    $this->load_guitar_chord($postid);
                    $chordSheetText .= $this->C->createSvgChord(true);
                }
            }

            $chordSheetText .= "<div style=\"clear:both;\"></div>\n<!-- ChordPress ChordSheet End -->\n\n";
            $returnText .= $chordSheetText;
        }
        // $this->C->dnd($returnText);
        return $returnText . "\n\n";
    }

    //--------------------------------------------------------------------------
    /**
     * Process a single of the ChordPro text and return the formatted HTML.
     *
     * @since    1.0.0
     * @param    string    $line    Single line from inside shortcode wrapper.
     * @return   string             Formatted HTML.
     */
    public function formatAndDisplayLine($line)
    {
        $returnText = "";
        $startOfChorus = false;
        $endOfChorus = false;
        $startOfVerse = false;
        $endOfVerse = false;

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
            if (isset($line[0]) && $line[0] == '#') {
                /**
                 * Line starts with "#": It's a comment line => skip
                 */
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
                    $startOfVerse = true;
                } elseif (strpos($directiveName, 'end_of_verse') !== false) {
                    $endOfVerse = true;
                } elseif (strpos($directiveName, 'start_of_chorus') !== false) {
                    $startOfChorus = true;
                } elseif (strpos($directiveName, 'end_of_chorus') !== false) {
                    $endOfChorus = true;
                } elseif (strpos($directiveName, 'comment') !== false && !$this->hideComments) {
                    $arrLyrics[] = '<span class="cpress_comment">' . $directiveValue . '</span>';
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
         * Start of chorus?
         */
        if ($startOfChorus) {
            $returnText .= '<div class="cpress_chorus">';
            $startOfChorus = false;
        }

        /**
         * Start of verse?
         */
        if ($startOfVerse) {
            $returnText .= '<div class="cpress_verse">';
            $startOfVerse = false;
        }

        /**
         * Start of line wrapper (lyrics and chords)
         */
        $returnText .= "<div class=\"cpress_line\">\n   ";

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

                        $returnText .= "<div class=\"chord chord-hidable-%cpressID% key-%cpressID%-0\"><span class=\"chordshort\">";

                        /**
                         * Allow chords starting with a ~, |, / and . as chord placeholders. Just return the string.
                         * Att: ... will end up as &ellip; . Use blanks between each dot.
                         */
                        if (in_array($arrChords[$i][0], ['~', '|', '/', '.'])) {
                            $returnText .= $arrChords[$i];
                        } else {
                            $theChord = $this->formatChord($arrChords[$i]);
                            $this->allChords[] = $theChord;
                            $returnText .= $theChord;
                        }

                        $returnText .= "</span>&nbsp;</div>\n";

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
                             * Allow chords starting with a ~ as chord placeholders. Just return the string.
                             */
                            if (in_array($arrChords[$i][0], ['~', '|', '/', '.'])) {
                                $returnText .= '<div class="chord chord-hidable-%cpressID% key-%cpressID%-' . $t . '" style="display:none"><span class="chordshort">' . $arrChords[$i] . '</span>&nbsp;</div>';
                                $returnText .= "\n";
                            } else {
                                /**
                                 * This is a real chord.
                                 */
                                $theChord = $this->formatChord($arrChords[$i]);
                                $returnText .= '<div class="chord chord-hidable-%cpressID% key-%cpressID%-' . $t . '" style="display:none"><span class="chordshort">' . $theChord . '</span>&nbsp;</div>';
                            }
                        }

                        $this->setTranspose($tempTranspose);

                        /**
                         * Now add the lyrics for this section.
                         */
                        $returnText .= '<div class="lyric">';

                        if (strlen($lyrics) > 0) {
                            $endOf1stWord = strpos($lyrics, ' ');
                            $numSpaces = substr_count($lyrics, ' ');
                            if ($endOf1stWord > 0 && $numSpaces == 1) {
                                $returnText .= substr($lyrics, 0, $endOf1stWord) . '&nbsp;';
                                $lyrics = substr($lyrics, $endOf1stWord);
                                $returnText .= "</div></div>\n<div class=\"cpress_line_section\">\n<div class=\"chord\">&nbsp;</div><div class=\"lyric\">";
                            }
                        }
                    } else {
                        $returnText .= '<div class="chord">&nbsp;</div>';
                        $returnText .= '<div class="lyric">';
                    }

                    if (strlen($lyrics) > 0) $returnText .= $lyrics;
                    $returnText .= "</div>\n"; // end of lyric
                    $returnText .= "</div>\n"; // end of line

                }
            }

            /**
             * If there are more lyric parts than chord parts, add them here
             */
            for ($i = count($arrChords); $i < count($arrLyrics); $i++) {
                $returnText .= $arrLyrics[$i];
            }
        } else {

            /** 
             * Hide chords was selected, so only show the lyrics
             */
            $returnText .= '<div class="cpress_line_section"><div class="lyric">';

            for ($i = 0; $i < count($arrLyrics); $i++) {
                $returnText .= $arrLyrics[$i];
            }

            $returnText .= "</div></div>";
        }

        /**
         * End of line wrapper
         */
        $returnText .= "</div>\n";

        /**
         * End of chorus?
         */
        if ($endOfChorus) {
            $returnText .= "</div>\n";
            $endOfChorus = false;
        }

        /**
         * End of verse?
         */
        if ($endOfVerse) {
            $returnText .= "</div>\n";
            $endOfVerse = false;
        }

        /**
         * Add a clear:both div
         */
        $returnText .= "<div class='cpress_clear'></div>\n";

        return $returnText;
    }

    //--------------------------------------------------------------------------
    /**
     * Remove all <> wrapped text from a string.
     *
     * @since     1.0.0
     * @param     string    $string    String to process.
     * @return    string               Stripped text.
     */
    private function stripTags($string)
    {
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

    //--------------------------------------------------------------------------
    /**
     * Processes a note text and returns the formatted HTML.
     *
     * @since     1.0.0
     * @param     string    $ch    Chord text, e.g. "A" or "B#" or "Db/F" or "IV".
     * @return    string           Uppercase note string.
     */
    public function formatChord($ch)
    {
        $useFlats = false;
        $romanChords = array('I', 'I7', 'II', 'III', 'iii', 'IV', 'V', 'V7', 'VI', 'vi', 'VI7', 'vi7', 'VII');
        $nashvilleChords = array('1', '2', '3', '4', '5', '6', '7');

        if (strlen($ch) == 0) return;

        /**
         * Return roman chords or nashville chords as is
         */
        if (in_array($ch, $romanChords) or in_array($ch, $nashvilleChords)) return $ch;

        /**
         * The first letter should be the key note
         */
        $note = substr($ch, 0, 1);
        $xlatedNote = $note;
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

    //--------------------------------------------------------------------------
    /**
     * Process the remainder of the given chord.
     *
     * @since     1.0.0
     * @param     string    $remainder    Chord text starting at second char.
     * @return    string                  Formatted remainder.
     */
    public function formatRemainder($remainder)
    {
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

    //--------------------------------------------------------------------------
    /**
     * Convert note to number.
     *
     * @since     1.0.0
     * @param     string    $n    Note to convert.
     * @return    integer
     */
    public function noteToNumber($n)
    {
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

        if ($this->hbNotation == 'yes') {
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

    //--------------------------------------------------------------------------
    /**
     * Convert number to note.
     *
     * @since     1.0.0
     * @param     integer    $n           Numeric note value to convert to note string.
     * @param     boolean    $useFlats    Whether to use flat notes or not.
     * @return    string
     */
    public function numberToNote($n, $useFlats = false)
    {
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

    //--------------------------------------------------------------------------
    /**
     * Set class variable: displayHBNotation.
     *
     * @since     1.0.0
     * @param     boolean    $g
     */
    public function setDisplayHBNotation($g)
    {
        $this->displayHBNotation = $g;
    }

    //--------------------------------------------------------------------------
    /**
     * Get class variable: displayHBNotation.
     *
     * @since     1.0.0
     * @return    boolean
     */
    public function getDisplayHBNotation()
    {
        return $this->displayHBNotation;
    }

    //--------------------------------------------------------------------------
    /**
     * Set class variable: float.
     *
     * @since     1.0.0
     * @param     string    $f    left/right/none/inherit.
     */
    public function setFloat($f)
    {
        $this->float = $f;
    }

    //--------------------------------------------------------------------------
    /**
     * Get class variable: float.
     *
     * @since     1.0.0
     * @return    string
     */
    public function getFloat()
    {
        return $this->float;
    }

    //--------------------------------------------------------------------------
    /**
     * Set class variable: hbNotation.
     *
     * @since     1.0.0
     * @param     string    $g    yes/no.
     */
    public function setHBNotation($g)
    {
        $this->hbNotation = $g;
    }

    //--------------------------------------------------------------------------
    /**
     * Get class variable: hbNotation.
     *
     * @since     1.0.0
     * @return    string    yes/no.
     */
    public function getHBNotation()
    {
        return $this->hbNotation;
    }

    //--------------------------------------------------------------------------
    /**
     * Set class variable: interactive.
     *
     * @since     1.0.0
     * @param     string    $g    yes/no.
     */
    public function setInteractive($i)
    {
        $this->interactive = $i;
    }

    //--------------------------------------------------------------------------
    /**
     * Get class variable: interactive.
     *
     * @since     1.0.0
     * @return    string    yes/no.
     */
    public function getInteractive()
    {
        return $this->interactive;
    }

    //--------------------------------------------------------------------------
    /**
     * Sets class variable: transpose.
     *
     * @since     1.0.0
     * @param     integer    $t    Positive or negative number of steps for transponation.
     */
    public function setTranspose($t)
    {
        $this->transpose = $t;
    }

    //--------------------------------------------------------------------------
    /**
     * Get class variable: transpose.
     *
     * @since     1.0.0
     * @return    integer
     */
    public function getTranspose()
    {
        return $this->transpose;
    }

    /**
     * Find a guitar chord type post by shortname.
     * 
     * @param  string    $shortname    Shortname of the guitar post to look for.
     * @return integer                 Post ID or NULL
     */
    private function find_guitar_chord_post($shortname)
    {
        $query_args = array(
            'posts_per_page'   => 1,
            'post_type'        => 'guitar_chord',
            'meta_key'         => 'guitar_chord_name',
            'meta_value'       => $shortname
        );
        $query_result = new WP_Query($query_args);

        if (array_key_exists(0, $query_result->posts)) {
            return $query_result->posts[0]->ID;
        } else {
            return 0;
        }
    }

    /**
     * Load a guitar chord based on post ID.
     * 
     * @param  integer    $postid    Guitar chord post ID.
     * @return void
     */
    private function load_guitar_chord($postid)
    {
        $my_chord = array();
        $post = get_post($postid);

        /**
         * Retrieve meta data from chord post
         */
        $guitar_chord_name     = get_post_meta($postid, 'guitar_chord_name', true);
        $guitar_chord_barres   = get_post_meta($postid, 'guitar_chord_barres', true);
        $guitar_chord_fingers  = get_post_meta($postid, 'guitar_chord_fingers', true);
        $guitar_chord_frets    = get_post_meta($postid, 'guitar_chord_frets', true);
        $guitar_chord_strings  = get_post_meta($postid, 'guitar_chord_strings', true);
        $guitar_chord_position = get_post_meta($postid, 'guitar_chord_position', true);
        $guitar_chord_tuning   = get_post_meta($postid, 'guitar_chord_tuning', true);

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

    //--------------------------------------------------------------------------
    /**
     * Create a modal guitar chord dialog.
     * 
     * @param  integer    $postid    ID of the chord post
     * @param  string     $chord     Vexchord HTML
     * @return string                Modal chord dialog HTML
     */
    private function create_modal_chord($postid, $chord)
    {
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
     * Generate random string.
     * 
     * @param  integer    $length    Length of random string to generate.
     * @return string                Random string
     */
    private function random_string($length = 10)
    {
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
    function pretty_dump($a)
    {
        return highlight_string("<?php\n\$data =\n" . var_export($a, true) . ";\n?>");
    }
}
