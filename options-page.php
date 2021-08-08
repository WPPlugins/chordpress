<?php 
/**
 * ChorPress Options Page
 *
 * @author George Lewe
 * @link https://www.lewe.com
 *
 * @package Chordpress
 * @subpackage Options
 * @since 1.5.0
 */

 /** ---------------------------------------------------------------------------
 * Initialize Settings
 */    
function chordpress_settings_init()
{
   wp_register_style('chordpress', plugins_url('/css/chordpress.css',__FILE__ ));

   $cpressOptions = array (
      'chordpress_checkbox_hide_album',
      'chordpress_checkbox_hide_artist',
      'chordpress_checkbox_hide_chords',
      'chordpress_checkbox_hide_comments',
      'chordpress_checkbox_hide_composer',
      'chordpress_checkbox_hide_subtitle',
      'chordpress_checkbox_hide_title',
      'chordpress_checkbox_hide_year',
      'chordpress_checkbox_hb_notation',
      'chordpress_select_title_level',
      'chordpress_select_subtitle_level',
      'chordpress_text_meta_style',
      'chordpress_text_chord_textcolor',
      'chordpress_text_chord_textsize',
      'chordpress_text_lyrics_textcolor',
      'chordpress_text_lyrics_textsize',
      'chordpress_text_comment_style',
      'chordpress_text_chorus_style',
      'chordpress_text_verse_style',
   );

   foreach ($cpressOptions as $cpressOption)
   {
      add_option( $cpressOption, '');
      register_setting( 'chordpress_options_group', $cpressOption, 'chordpress_callback' );
   }
}
add_action( 'admin_init', 'chordpress_settings_init' );

/** ---------------------------------------------------------------------------
 * Add Settings menu entry
 */    
function chordpress_add_admin_menu()
{ 
   add_options_page( 
      'ChordPress Settings',     // The title to be displayed in the browser window for this page.
      'ChordPress',              // The text to be displayed for this menu item
      'manage_options',          // Which type of users can see this menu item
      'chordpress',              // The unique ID - that is, the slug - for this menu item
      'chordpress_options_page'  // The name of the function to call when rendering this menu's page
   );
}
add_action( 'admin_menu', 'chordpress_add_admin_menu' );

/** ---------------------------------------------------------------------------
 * Load Settings CSS and JS
 */    
function chordpress_add_admin_script()
{
   global $pagenow;
   if ($pagenow != 'options-general.php') {
      return;
   }
   wp_register_style( 'cpress-css', plugins_url( 'assets/css/chordpress.css', __FILE__ ));
   wp_enqueue_style( 'cpress-css' );
   // wp_register_script( 'cpress-js', plugin_dir_url(__FILE__).'css/jiraconnector.js', array('jquery-core'), false, true );
   // wp_enqueue_script( 'cpress-js' );
}
add_action( 'admin_enqueue_scripts', 'chordpress_add_admin_script' );

/** ---------------------------------------------------------------------------
 * Build the Options page
 */    
function chordpress_options_page()
{
   ?>
   <div class="wrap">

      <div style="background:#003A6B !important;width:100% !important;height:80px !important;">
         <img src="<?php echo plugins_url( 'images/banner-247x80.png', __FILE__ );?>" alt="" style="text-align:left;">
      </div>
      <div style="clear:both;">
         <h1><?php echo esc_html(__('ChordPress Settings','chordpress'));?></h1>
         <div class="cpress-callout cpress-callout-info"><?php echo esc_html(__('Refer to the following link for details about the ChordPro syntax and directives','chordpress'));?>: <a href="https://www.chordpro.org/chordpro/ChordPro-Directives.html" target="_blank"><?php echo esc_html(__('ChordPro Directives','chordpress'));?></a></div>
         <p>
            <a href="https://support.lewe.com/docs/chordpress-manual/settings/" class="cpress-btn cpress-btn-info cpress-btn-xs" target="_blank"><?php echo esc_html(__('Documentation...','chordpress'));?></a>
            <a href="https://www.paypal.me/GeorgeLewe" class="cpress-btn cpress-btn-primary cpress-btn-xs" target="_blank"><?php echo esc_html(__('Please donate...','chordpress'));?></a>
         </p>
      </div>

      <?php
      ?>

      <?php // settings_errors(); ?>
      <?php $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'output-options'; ?>

      <div style="clear:both;">

         <!-- Tabs -->
         <h2 class="nav-tab-wrapper">
            <a href="?page=chordpress&tab=output-options" class="nav-tab <?php echo $active_tab == 'output-options' ? 'nav-tab-active cpress-tab-active' : ''; ?>"><?php echo esc_html(__('Output Options','chordpress'));?></a>
            <a href="?page=chordpress&tab=formatting-options" class="nav-tab <?php echo $active_tab == 'formatting-options' ? 'nav-tab-active cpress-tab-active' : ''; ?>"><?php echo esc_html(__('Formatting Options','chordpress'));?></a>
         </h2>

         <!-- <form method="post" action="options.php"> -->
         <form name="form" action="options.php" method="post">

            <?php settings_fields( 'chordpress_options_group' ); ?>

            <?php 
            //
            // Tab: Output Options
            //
            if ($active_tab=='output-options') $display = "display:block;"; else $display = "display:none;"; 
            ?>
            <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
               <tbody>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('Hide Album','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hide_album' name='chordpress_checkbox_hide_album' <?php checked( get_option('chordpress_checkbox_hide_album'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-album.html " target="_blank">album directive</a> will not be displayed.</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('Hide Artist','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hide_artist' name='chordpress_checkbox_hide_artist' <?php checked( get_option('chordpress_checkbox_hide_artist'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-artist.html " target="_blank">artist directive</a> will not be displayed.</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('Hide Chords','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hide_chords' name='chordpress_checkbox_hide_chords' <?php checked( get_option('chordpress_checkbox_hide_chords'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, only the lyrics will be printed (no chords above them).</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('Hide Comments','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hide_comments' name='chordpress_checkbox_hide_comments' <?php checked( get_option('chordpress_checkbox_hide_comments'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-comment.html " target="_blank">comment directive</a> will not be displayed.</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('Hide Composer','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hide_composer' name='chordpress_checkbox_hide_composer' <?php checked( get_option('chordpress_checkbox_hide_composer'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-composer.html " target="_blank">composer directive</a> will not be displayed.</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('Hide Subtitle','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hide_subtitle' name='chordpress_checkbox_hide_subtitle' <?php checked( get_option('chordpress_checkbox_hide_subtitle'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-subtitle.html " target="_blank">subtitle directive</a> will not be displayed.</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('Hide Title','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hide_title' name='chordpress_checkbox_hide_title' <?php checked( get_option('chordpress_checkbox_hide_title'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-title.html " target="_blank">title directive</a> will not be displayed.</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('Hide Year','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hide_year' name='chordpress_checkbox_hide_year' <?php checked( get_option('chordpress_checkbox_hide_year'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, the <a href="https://www.chordpro.org/chordpro/Directives-year.html " target="_blank">year directive</a> will not be displayed.</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><?php echo esc_html(__('H/B Notation','chordpress'));?></th>
                     <td>
                        <input type='checkbox' id='chordpress_checkbox_hb_notation' name='chordpress_checkbox_hb_notation' <?php checked( get_option('chordpress_checkbox_hb_notation'), 1 ); ?> value='1'>
                        <p class="description">With this option selected, chords are displayed in H/B notation: H is used instead of B, B is used insetad of Bb. This notation is used in some European countries. Read more about it <a href="https://sayandsound.lewe.com/note-h/" target="_blank">here</a>.</p>
                     </td>
                  </tr>
                  <tr>
                     <th sope="row" colspan="2"><?php submit_button(); ?></th>
                  </tr>
               </tbody>
            </table>

            <?php 
            //
            // Tab: Formatting Options
            //
            if ($active_tab=='formatting-options') $display = "display:block;"; else $display = "display:none;";
            ?>
            <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
               <tbody>
                  <tr>
                     <th scope="row"><label for="chordpress_select_title_level"><?php echo esc_html(__('Title Level','chordpress'));?></label></th>
                     <td>
                        <?php 
                           $defaultValue = 'h1';
                           $selection = get_option('chordpress_select_title_level');
                           if (!strlen($selection)) $selection=$defaultValue;
                        ?>
                        <select id="chordpress_select_title_level" name="chordpress_select_title_level">
                           <option value='h1' <?php echo ($selection=='h1')?"selected":""; ?>>Header 1</option>
                           <option value='h2' <?php echo ($selection=='h2')?"selected":""; ?>>Header 2</option>
                           <option value='h3' <?php echo ($selection=='h3')?"selected":""; ?>>Header 3</option>
                           <option value='h4' <?php echo ($selection=='h4')?"selected":""; ?>>Header 4</option>
                           <option value='h5' <?php echo ($selection=='h5')?"selected":""; ?>>Header 5</option>
                           <option value='h6' <?php echo ($selection=='h6')?"selected":""; ?>>Header 6</option>
                        </select>
                        <p class="description">Select the header level for the title directive.</p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_select_subtitle_level"><?php echo esc_html(__('Subtitle Level','chordpress'));?></label></th>
                     <td>
                        <?php 
                           $defaultValue = 'h2';
                           $selection = get_option('chordpress_select_subtitle_level');
                           if (!strlen($selection)) $selection=$defaultValue;
                        ?>
                        <select id="chordpress_select_subtitle_level" name="chordpress_select_subtitle_level">
                           <option value='h1' <?php echo ($selection=='h1')?"selected":""; ?>>Header 1</option>
                           <option value='h2' <?php echo ($selection=='h2')?"selected":""; ?>>Header 2</option>
                           <option value='h3' <?php echo ($selection=='h3')?"selected":""; ?>>Header 3</option>
                           <option value='h4' <?php echo ($selection=='h4')?"selected":""; ?>>Header 4</option>
                           <option value='h5' <?php echo ($selection=='h5')?"selected":""; ?>>Header 5</option>
                           <option value='h6' <?php echo ($selection=='h6')?"selected":""; ?>>Header 6</option>
                        </select>
                        <p class="description"><?php echo esc_html(__('Select the header level for the subtitle directive.','chordpress'));?></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_text_meta_style"><?php echo esc_html(__('Meta Information Style','chordpress'));?></label></th>
                     <td>
                        <input type="text" id="chordpress_text_meta_style" name="chordpress_text_meta_style" value="<?php echo get_option('chordpress_text_meta_style'); ?>" />
                        <p class="description"><?php echo esc_html(__('Enter additional CSS for the meta header block, e.g.: font-style: italic;','chordpress'));?></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_text_chord_textcolor"><?php echo esc_html(__('Chord Text Color','chordpress'));?></label></th>
                     <td>
                        <input type="text" id="chordpress_text_chord_textcolor" name="chordpress_text_chord_textcolor" value="<?php echo get_option('chordpress_text_chord_textcolor'); ?>" />
                        <p class="description"><?php echo esc_html(__('Enter the hex code of the chord text color incl. a leading hash, e.g. #990000. Leave emtpy or enter "inherit" to use your theme default.','chordpress'));?></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_text_chord_textsize"><?php echo esc_html(__('Chord Text Size','chordpress'));?></label></th>
                     <td>
                        <input type="text" id="chordpress_text_chord_textsize" name="chordpress_text_chord_textsize" value="<?php echo get_option('chordpress_text_chord_textsize'); ?>" />
                        <p class="description"><?php echo esc_html(__('Enter the text size of the chords in pixel, em or percent, e.g. 150%. Leave emtpy or enter "inherit" to use your theme default.','chordpress'));?></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_text_lyrics_textcolor"><?php echo esc_html(__('Lyrics Text Color','chordpress'));?></label></th>
                     <td>
                        <input type="text" id="chordpress_text_lyrics_textcolor" name="chordpress_text_lyrics_textcolor" value="<?php echo get_option('chordpress_text_lyrics_textcolor'); ?>" />
                        <p class="description"><?php echo esc_html(__('Enter the hex code of the lyrics text color incl. a leading hash, e.g. #990000. Leave emtpy or enter "inherit" to use your theme default.','chordpress'));?></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_text_lyrics_textsize"><?php echo esc_html(__('Lyrics Text Size','chordpress'));?></label></th>
                     <td>
                        <input type="text" id="chordpress_text_lyrics_textsize" name="chordpress_text_lyrics_textsize" value="<?php echo get_option('chordpress_text_lyrics_textsize'); ?>" />
                        <p class="description"><?php echo esc_html(__('Enter the text size of the lyrics in pixel, em or percent, e.g. 150%. Leave emtpy or enter "inherit" to use your theme default.','chordpress'));?></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_text_comment_style"><?php echo esc_html(__('Comment Style','chordpress'));?></label></th>
                     <td>
                        <input type="text" id="chordpress_text_comment_style" name="chordpress_text_comment_style" value="<?php echo get_option('chordpress_text_comment_style'); ?>" />
                        <p class="description"><?php echo esc_html(__('Enter the CSS for comments, e.g.: background-color: #606060; font-style: italic; padding: 4px;','chordpress'));?></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_text_chorus_style"><?php echo esc_html(__('Chorus Style','chordpress'));?></label></th>
                     <td>
                        <input type="text" id="chordpress_text_chorus_style" name="chordpress_text_chorus_style" value="<?php echo get_option('chordpress_text_chorus_style'); ?>" />
                        <p class="description">Enter the CSS for the <a href="https://www.chordpro.org/chordpro/Directives-env_chorus.html" target="_blank">chorus sections.</a></p>
                     </td>
                  </tr>
                  <tr>
                     <th scope="row"><label for="chordpress_text_verse_style"><?php echo esc_html(__('Verse Style','chordpress'));?></label></th>
                     <td>
                        <input type="text" id="chordpress_text_verse_style" name="chordpress_text_verse_style" value="<?php echo get_option('chordpress_text_verse_style'); ?>" />
                        <p class="description">Enter the CSS for the <a href="https://www.chordpro.org/chordpro/Directives-env_verse.html" target="_blank">verse sections.</a></p>
                     </td>
                  </tr>
                  <tr>
                     <th sope="row" colspan="2"><?php submit_button(); ?></th>
                  </tr>
               </tbody>
            </table>

         </form>
      </div>
   </div>
   <hr>
   <?php
}
?>