<?php 
/**
 * ChordPress Options Page
 *
 * @author George Lewe
 * @link https://www.lewe.com
 *
 * @package ChordPress
 * @subpackage Settings
 * @since 1.0.0
 */

add_action( 'admin_menu', 'chordpress_add_admin_menu' );
add_action( 'admin_init', 'chordpress_settings_init' );


/** ---------------------------------------------------------------------------
 * Add Option Page
 */    
function chordpress_add_admin_menu()
{ 
   add_options_page( 
      'ChordPress Settings',      // The title to be displayed in the browser window for this page.
      'ChordPress',               // The text to be displayed for this menu item
      'manage_options',           // Which type of users can see this menu item
      'chordpress',               // The unique ID - that is, the slug - for this menu item
      'chordpress_options_page'   // The name of the function to call when rendering this menu's page
   );
}

/** ---------------------------------------------------------------------------
 * Register options
 */    
function chordpress_settings_init()
{ 
   //
   // Display Settings Section
   //
   add_settings_section(
      'chordpress_display_section',            // ID used to identify this section and with which to register options
      '<h2>Output Options</h2>',               // Title to be displayed on the administration page
      'chordpress_settings_section_callback',  // Callback used to render the description of the section
      'chordpress_display_options'             // Page on which to add this section of options
   );

   add_settings_field( 
      'chordpress_checkbox_hide_album',        // ID used to identify the field throughout the theme
      'Hide Album',                            // The label to the left of the option interface element
      'chordpress_checkbox_hide_album_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',            // The page on which this option will be displayed
      'chordpress_display_section'             // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_checkbox_hide_artist',        // ID used to identify the field throughout the theme
      'Hide Artist',                            // The label to the left of the option interface element
      'chordpress_checkbox_hide_artist_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',             // The page on which this option will be displayed
      'chordpress_display_section'              // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_checkbox_hide_chords',        // ID used to identify the field throughout the theme
      'Hide Chords',                            // The label to the left of the option interface element
      'chordpress_checkbox_hide_chords_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',             // The page on which this option will be displayed
      'chordpress_display_section'              // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_checkbox_hide_comments',        // ID used to identify the field throughout the theme
      'Hide Comments',                            // The label to the left of the option interface element
      'chordpress_checkbox_hide_comments_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',               // The page on which this option will be displayed
      'chordpress_display_section'                // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_checkbox_hide_composer',        // ID used to identify the field throughout the theme
      'Hide Composer',                            // The label to the left of the option interface element
      'chordpress_checkbox_hide_composer_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',               // The page on which this option will be displayed
      'chordpress_display_section'                // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_checkbox_hide_subtitle',        // ID used to identify the field throughout the theme
      'Hide Subtitle',                            // The label to the left of the option interface element
      'chordpress_checkbox_hide_subtitle_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',               // The page on which this option will be displayed
      'chordpress_display_section'                // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_checkbox_hide_title',        // ID used to identify the field throughout the theme
      'Hide Title',                            // The label to the left of the option interface element
      'chordpress_checkbox_hide_title_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',            // The page on which this option will be displayed
      'chordpress_display_section'             // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_checkbox_hide_year',        // ID used to identify the field throughout the theme
      'Hide Year',                            // The label to the left of the option interface element
      'chordpress_checkbox_hide_year_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',           // The page on which this option will be displayed
      'chordpress_display_section'            // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_checkbox_hb_notation',        // ID used to identify the field throughout the theme
      'Use H/B notation',                       // The label to the left of the option interface element
      'chordpress_checkbox_hb_notation_render', // The name of the function responsible for rendering the option interface
      'chordpress_display_options',             // The page on which this option will be displayed
      'chordpress_display_section'              // The name of the section to which this field belongs
   );

   register_setting( 'chordpress_display_options', 'chordpress_settings' );

   //
   // Format Settings
   //
   add_settings_section(
      'chordpress_formatting_section',        // ID used to identify this section and with which to register options
      '<h2>Formatting Settings<h2>',           // Title to be displayed on the administration page
      'chordpress_settings_section_callback', // Callback used to render the description of the section
      'chordpress_formatting_options'         // Page on which to add this section of options
   );

   add_settings_field( 
      'chordpress_select_title_level',        // ID used to identify the field throughout the theme
      'Title Level',                          // The label to the left of the option interface element
      'chordpress_select_title_level_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',        // The page on which this option will be displayed
      'chordpress_formatting_section'         // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_select_subtitle_level',        // ID used to identify the field throughout the theme
      'Subtitle Level',                          // The label to the left of the option interface element
      'chordpress_select_subtitle_level_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',           // The page on which this option will be displayed
      'chordpress_formatting_section'            // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_text_meta_style',        // ID used to identify the field throughout the theme
      'Meta Information Style',            // The label to the left of the option interface element
      'chordpress_text_meta_style_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',     // The page on which this option will be displayed
      'chordpress_formatting_section'      // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_text_chord_textcolor',        // ID used to identify the field throughout the theme
      'Chord Text Color',                       // The label to the left of the option interface element
      'chordpress_text_chord_textcolor_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',          // The page on which this option will be displayed
      'chordpress_formatting_section'           // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_text_chord_textsize',        // ID used to identify the field throughout the theme
      'Chord Text Size',                       // The label to the left of the option interface element
      'chordpress_text_chord_textsize_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',         // The page on which this option will be displayed
      'chordpress_formatting_section'          // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_text_lyrics_textcolor',        // ID used to identify the field throughout the theme
      'Lyrics Text Color',                       // The label to the left of the option interface element
      'chordpress_text_lyrics_textcolor_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',           // The page on which this option will be displayed
      'chordpress_formatting_section'            // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_text_lyrics_textsize',        // ID used to identify the field throughout the theme
      'Lyrics Text Size',                        // The label to the left of the option interface element
      'chordpress_text_lyrics_textsize_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',          // The page on which this option will be displayed
      'chordpress_formatting_section'           // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_text_comment_style',        // ID used to identify the field throughout the theme
      'Comment Style',                        // The label to the left of the option interface element
      'chordpress_text_comment_style_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',        // The page on which this option will be displayed
      'chordpress_formatting_section'         // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_text_chorus_style',        // ID used to identify the field throughout the theme
      'Chorus Style',                        // The label to the left of the option interface element
      'chordpress_text_chorus_style_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',       // The page on which this option will be displayed
      'chordpress_formatting_section'        // The name of the section to which this field belongs
   );

   add_settings_field( 
      'chordpress_text_verse_style',        // ID used to identify the field throughout the theme
      'Verse Style',                        // The label to the left of the option interface element
      'chordpress_text_verse_style_render', // The name of the function responsible for rendering the option interface
      'chordpress_formatting_options',      // The page on which this option will be displayed
      'chordpress_formatting_section'       // The name of the section to which this field belongs
   );

   register_setting( 'chordpress_formatting_options', 'chordpress_settings' );
}

/** ---------------------------------------------------------------------------
 * Render section: Display Settings
 */    
function chordpress_section_display_render() { 
   ?>
   <!-- DISPLAY SECTION -->
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Hide Album
 */    
function chordpress_checkbox_hide_album_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input type='checkbox' name='chordpress_settings[chordpress_checkbox_hide_album]' <?php checked( $options['chordpress_checkbox_hide_album'], 1 ); ?> value='1'>
   <span class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-album.html " target="_blank">album directive</a> will not be displayed.</span></label>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Hide Artist
 */    
function chordpress_checkbox_hide_artist_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input class="cpr-form-control" type='checkbox' name='chordpress_settings[chordpress_checkbox_hide_artist]' <?php checked( $options['chordpress_checkbox_hide_artist'], 1 ); ?> value='1'>
   <span class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-artist.html " target="_blank">artist directive</a> will not be displayed.</span></label>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Hide Chords
 */    
function chordpress_checkbox_hide_chords_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input type='checkbox' name='chordpress_settings[chordpress_checkbox_hide_chords]' <?php checked( $options['chordpress_checkbox_hide_chords'], 1 ); ?> value='1'>
   <span class="description">With this option selected, only the lyrics will be printed (no chords above them).</span></label>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Hide Comments
 */    
function chordpress_checkbox_hide_comments_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input type='checkbox' name='chordpress_settings[chordpress_checkbox_hide_comments]' <?php checked( $options['chordpress_checkbox_hide_comments'], 1 ); ?> value='1'>
   <span class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-comment.html " target="_blank">comment directive</a> will not be displayed.</span></label>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Hide Composer
 */    
function chordpress_checkbox_hide_composer_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input type='checkbox' name='chordpress_settings[chordpress_checkbox_hide_composer]' <?php checked( $options['chordpress_checkbox_hide_composer'], 1 ); ?> value='1'>
   <span class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-composer.html " target="_blank">composer directive</a> will not be displayed.</span></label>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Hide Subttle
 */    
function chordpress_checkbox_hide_subtitle_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input type='checkbox' name='chordpress_settings[chordpress_checkbox_hide_subtitle]' <?php checked( $options['chordpress_checkbox_hide_subtitle'], 1 ); ?> value='1'>
   <span class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-subtitle.html " target="_blank">subtitle directive</a> will not be displayed.</span></label>
    <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Hide Title
 */    
function chordpress_checkbox_hide_title_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input type='checkbox' name='chordpress_settings[chordpress_checkbox_hide_title]' <?php checked( $options['chordpress_checkbox_hide_title'], 1 ); ?> value='1'>
   <span class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-title.html " target="_blank">title directive</a> will not be displayed.</span></label>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Hide Year
 */    
function chordpress_checkbox_hide_year_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input type='checkbox' name='chordpress_settings[chordpress_checkbox_hide_year]' <?php checked( $options['chordpress_checkbox_hide_year'], 1 ); ?> value='1'>
   <span class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-year.html " target="_blank">year directive</a> will not be displayed.</span></label>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: H/B Notation
 */    
function chordpress_checkbox_hb_notation_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <label><input type='checkbox' name='chordpress_settings[chordpress_checkbox_hb_notation]' <?php checked( $options['chordpress_checkbox_hb_notation'], 1 ); ?> value='1'>
   <span class="description">With this option selected, chords are displayed in H/B notation: H is used instead of B, B is used insetad of Bb</span></label>
   <p class="description">This notation is used in some European countries. Read more about it <a href="https://sayandsound.lewe.com/note-h/" target="_blank">here</a>.</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render section: Formatting Settings
 */    
function chordpress_section_formatting_render() {
   ?>
   </div>
   <!-- FORMATTING SECTION -->
   <?php
 }

/** ---------------------------------------------------------------------------
 * Render option: Title Header Level
 */    
function chordpress_select_title_level_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <select name='chordpress_settings[chordpress_select_title_level]'>
      <option value='h1' <?php selected( $options['chordpress_select_title_level'], 'h1' ); ?>>Header 1</option>
      <option value='h2' <?php selected( $options['chordpress_select_title_level'], 'h2' ); ?>>Header 2</option>
      <option value='h3' <?php selected( $options['chordpress_select_title_level'], 'h3' ); ?>>Header 3</option>
      <option value='h4' <?php selected( $options['chordpress_select_title_level'], 'h4' ); ?>>Header 4</option>
      <option value='h5' <?php selected( $options['chordpress_select_title_level'], 'h5' ); ?>>Header 5</option>
      <option value='h6' <?php selected( $options['chordpress_select_title_level'], 'h6' ); ?>>Header 6</option>
   </select>
   <p class="description">Select the header level for the title directive.</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Subtitle Header Level
 */    
function chordpress_select_subtitle_level_render()
{ 
   $options = get_option( 'chordpress_settings' );
   if(!$options['chordpress_select_subtitle_level']) $options['chordpress_select_subtitle_level'] = 'h2';
   ?>
   <select name='chordpress_settings[chordpress_select_subtitle_level]'>
      <option value='h1' <?php selected( $options['chordpress_select_subtitle_level'], 'h1' ); ?>>Header 1</option>
      <option value='h2' <?php selected( $options['chordpress_select_subtitle_level'], 'h2' ); ?>>Header 2</option>
      <option value='h3' <?php selected( $options['chordpress_select_subtitle_level'], 'h3' ); ?>>Header 3</option>
      <option value='h4' <?php selected( $options['chordpress_select_subtitle_level'], 'h4' ); ?>>Header 4</option>
      <option value='h5' <?php selected( $options['chordpress_select_subtitle_level'], 'h5' ); ?>>Header 5</option>
      <option value='h6' <?php selected( $options['chordpress_select_subtitle_level'], 'h6' ); ?>>Header 6</option>
   </select>
   <p class="description">Select the header level for the subtitle directive.</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Subtitle Header Level
 */    
function chordpress_select_meta_level_render()
{ 
   $options = get_option( 'chordpress_settings' );
   if(!$options['chordpress_select_meta_level']) $options['chordpress_select_meta_level'] = 'h3';
   ?>
   <select name='chordpress_settings[chordpress_select_meta_level]'>
      <option value='h1' <?php selected( $options['chordpress_select_meta_level'], 'h1' ); ?>>Header 1</option>
      <option value='h2' <?php selected( $options['chordpress_select_meta_level'], 'h2' ); ?>>Header 2</option>
      <option value='h3' <?php selected( $options['chordpress_select_meta_level'], 'h3' ); ?>>Header 3</option>
      <option value='h4' <?php selected( $options['chordpress_select_meta_level'], 'h4' ); ?>>Header 4</option>
      <option value='h5' <?php selected( $options['chordpress_select_meta_level'], 'h5' ); ?>>Header 5</option>
      <option value='h6' <?php selected( $options['chordpress_select_meta_level'], 'h6' ); ?>>Header 6</option>
   </select>
   <p class="description">Select the header level for the meta information underneath the subtitle (Album, Artist, Composer, Year).</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Comment Style
 */    
function chordpress_text_meta_style_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <input type='text' name='chordpress_settings[chordpress_text_meta_style]' value='<?php echo $options['chordpress_text_meta_style']; ?>' size='40'>
   <p class="description">Enter additional CSS for the meta header block, e.g.: font-style: italic;</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Chord Text Color
 */    
function chordpress_text_chord_textcolor_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <input type='text' name='chordpress_settings[chordpress_text_chord_textcolor]' value='<?php echo $options['chordpress_text_chord_textcolor']; ?>'>
   <p class="description">Enter the hex code of the chord text color incl. a leading hash, e.g. #990000. Leave emtpy or enter "inherit" to use your theme default.</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Chord Text Size
 */    
function chordpress_text_chord_textsize_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <input type='text' name='chordpress_settings[chordpress_text_chord_textsize]' value='<?php echo $options['chordpress_text_chord_textsize']; ?>'>
   <p class="description">Enter the text size of the chords in pixel, em or percent, e.g. 150%. Leave emtpy or enter "inherit" to use your theme default.</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Lyrics Text Color
 */    
function chordpress_text_lyrics_textcolor_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <input type='text' name='chordpress_settings[chordpress_text_lyrics_textcolor]' value='<?php echo $options['chordpress_text_lyrics_textcolor']; ?>'>
   <p class="description">Enter the hex code of the lyrics text color incl. a leading hash, e.g. #990000. Leave emtpy or enter "inherit" to use your theme default.</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Lyrics Text Size
 */    
function chordpress_text_lyrics_textsize_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <input type='text' name='chordpress_settings[chordpress_text_lyrics_textsize]' value='<?php echo $options['chordpress_text_lyrics_textsize']; ?>'>
   <p class="description">Enter the text size of the lyrics in pixel, em or percent, e.g. 150%. Leave emtpy or enter "inherit" to use your theme default.</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Comment Style
 */    
function chordpress_text_comment_style_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <input type='text' name='chordpress_settings[chordpress_text_comment_style]' value='<?php echo $options['chordpress_text_comment_style']; ?>' size='40'>
   <p class="description">Enter the CSS for comments, e.g.: background-color: #606060; font-style: italic; padding: 4px;</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Chorus Style
 */    
function chordpress_text_chorus_style_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <input type='text' name='chordpress_settings[chordpress_text_chorus_style]' value='<?php echo $options['chordpress_text_chorus_style']; ?>' size='40'>
   <p class="description">Enter the CSS for each <a href="https://www.chordpro.org/chordpro/Directives-env_chorus.html" target="_blank">chorus section.</p>
   <?php
}

/** ---------------------------------------------------------------------------
 * Render option: Verse Style
 */    
function chordpress_text_verse_style_render()
{ 
   $options = get_option( 'chordpress_settings' );
   ?>
   <input type='text' name='chordpress_settings[chordpress_text_verse_style]' value='<?php echo $options['chordpress_text_verse_style']; ?>' size='40'>
   <p class="description">Enter the CSS for each <a href="https://www.chordpro.org/chordpro/Directives-env_verse.html" target="_blank">verse section.</p>
   </div>
   <?php
}

/** ---------------------------------------------------------------------------
 * Show Options page description
 */    
function chordpress_settings_section_callback()
{ 
   // echo __( 
   //    '<p>Refer to the following link for details about the ChordPro syntax and directives: <a href="https://www.chordpro.org/chordpro/ChordPro-Directives.html" target="_blank">ChordPro Directives</a></p>
   //    <p>Please consider a <a href="https://www.paypal.me/GeorgeLewe" target="_blank">Donation</a> if you like the plugin.</p>',
   //    'wordpress'
   // );
}

/** ---------------------------------------------------------------------------
 * Build the Options page
 */    
function chordpress_options_page()
{ 
   ?>
   <img src="<?php echo plugins_url( '/images/icon-96x96.png', __FILE__ ); ?>" alt="" align="left" style="padding: 0 8px 8px 0;"/>
   <h1>ChordPress Settings</h1>
   <p>Refer to the following link for details about the ChordPro syntax and directives: <a href="https://www.chordpro.org/chordpro/ChordPro-Directives.html" target="_blank">ChordPro Directives</a></p>
   <p>Follow this link for the <a href="https://support.lewe.com/docs/chordpro-for-wordpress-manual/settings/" target="_blank">ChordPress Settings Documentation</a>.</p>
   <p>Please consider a <a href="https://www.paypal.me/GeorgeLewe" target="_blank">Donation</a> if you like the plugin.</p>
   <?php
   $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'display_options';
   ?>
   <h2 class="nav-tab-wrapper">
      <a href="?page=chordpress&tab=display_options" class="nav-tab <?php echo $active_tab == 'display_options' ? 'nav-tab-active' : ''; ?>"><span style="margin-right:8px;" class="dashicons dashicons-admin-tools"></span>Options</a>
      <a href="?page=chordpress&tab=formatting_options" class="nav-tab <?php echo $active_tab == 'formatting_options' ? 'nav-tab-active' : ''; ?>"><span style="margin-right:8px;" class="dashicons dashicons-admin-customizer"></span>Formatting</a>
   </h2> 
    <form action='options.php' method='post'>
      <?php
      if( $active_tab == 'display_options' ) {
         settings_fields( 'chordpress_display_options' );
         do_settings_sections( 'chordpress_display_options' );
      } else {
         settings_fields( 'chordpress_formatting_options' );
         do_settings_sections( 'chordpress_formatting_options' );
      }
      submit_button();
      ?>
   </form>
   <?php
}
?>