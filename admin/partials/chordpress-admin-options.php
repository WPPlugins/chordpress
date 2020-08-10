<?php

/**
 * Admin options
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.lewe.com
 * @since      1.0.0
 *
 * @package    ChordPress
 * @subpackage ChordPress/admin/partials
 */

/**
 * If this file is called directly, abort.
 */
if ( ! defined( 'WPINC' ) ) {
   die;
}

/**
 * Get plugin information.
 */
$P = new ChordPress_Plugin();
$ptitle = $P->get_plugin_title();
$pname = $P->get_plugin_name();
$pprefix = $P->get_plugin_prefix();
$puri = $P->get_plugin_uri();
$pdonuri = $P->get_plugin_donation_uri();

// $P->pretty_dump($_REQUEST);

/**
 * Options array
 */
$options = array (

   $pname.'_checkbox_uninstall_delete',
   $pname.'_checkbox_hide_album',
   $pname.'_checkbox_hide_artist',
   $pname.'_checkbox_hide_chords',
   $pname.'_checkbox_hide_comments',
   $pname.'_checkbox_hide_composer',
   $pname.'_checkbox_hide_subtitle',
   $pname.'_checkbox_hide_title',
   $pname.'_checkbox_hide_year',
   $pname.'_checkbox_hb_notation',
   $pname.'_select_title_level',
   $pname.'_select_subtitle_level',
   $pname.'_text_meta_style',
   $pname.'_text_chord_textcolor',
   $pname.'_text_chord_textsize',
   $pname.'_text_lyrics_textcolor',
   $pname.'_text_lyrics_textsize',
   $pname.'_text_comment_style',
   $pname.'_text_chorus_style',
   $pname.'_text_verse_style'

);

/**
 * ,--------------,
 * | Save Changes |
 * '--------------'
 */
if (isset($_REQUEST['submit'])) {

   $show_alert = false;

   foreach ($options as $option) {

      if (isset($_REQUEST[$option])) {

         if (get_option($option) != $_REQUEST[$option]) {
   
            $show_alert = true;
   
            if (update_option($option, $_REQUEST[$option])) {
   
               $alert_style = 'success';
   
            } else {
   
               $alert_style = 'danger';
   
            }
   
         } else {
   
            $show_alert = true;
   
         }
   
      } else {

         /**
          * Unchecked checkboxes will not be in the $_REQUEST array.
          * Make sure we set them to 0.
          */
         if (strpos($option,'checkbox')) {

            update_option($option, 0);

         }

      }
   
   }
   
}

/**
 * Save response message
 */
if ($show_alert) {

   if ($alert_style == 'success') {

      $alert_title = esc_html(__('Success','chordpress'));
      $alert_text = esc_html(__('The changes were saved successfully.','chordpress'));

   } else {

      $alert_title = esc_html(__('Error','chordpress'));
      $alert_text = esc_html(__('An error occurred while trying to save your settings.','chordpress'));

   }

}

?>

<div class="wrap">

   <div style="background:#003A6B !important;width:100% !important;height:80px !important;">
      <img src="<?php echo plugins_url( '../images/banner-247x80.png', __FILE__ );?>" alt="" style="text-align:left;">
   </div>
   <div style="clear:both;">
      <h1><?php echo esc_html(__($ptitle.' Settings','chordpress'));?></h1>
      <p>
         <a href="<?php echo $puri;?>" target="_blank" class="<?php echo $pprefix;?>-btn <?php echo $pprefix;?>-btn-info <?php echo $pprefix;?>-btn-xs"><?php echo esc_html(__('Documentation','chordpress'));?></a>
         <?php if (!CHORDPRESS_LICENSE_REQUIRED) { ?><a href="<?php echo $pdonuri;?>" target="_blank" class="<?php echo $pprefix;?>-btn <?php echo $pprefix;?>-btn-primary <?php echo $pprefix;?>-btn-xs"><?php echo esc_html(__('Donate','chordpress'));?></a><?php } ?>
      </p>
   </div>

   <?php
      if ($show_alert) echo $P->alert($alert_style,$alert_title,'h4',$alert_text,true,'98%');
      settings_errors();
      $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'tab_output';
   ?>

   <div style="clear:both;">

      <!-- Tabs -->
      <h2 class="nav-tab-wrapper">
         <a href="?page=<?php echo __FILE__;?>&tab=tab_output" class="nav-tab <?php echo $active_tab == 'tab_output' ? 'nav-tab-active cpress-tab-active' : ''; ?>"><?php echo esc_html(__('Output Options','chordpress'));?></a>
         <a href="?page=<?php echo __FILE__;?>&tab=tab_formatting" class="nav-tab <?php echo $active_tab == 'tab_formatting' ? 'nav-tab-active cpress-tab-active' : ''; ?>"><?php echo esc_html(__('Formatting Options','chordpress'));?></a>
         <a href="?page=<?php echo __FILE__;?>&tab=tab_plugin" class="nav-tab <?php echo $active_tab == 'tab_plugin' ? 'nav-tab-active '.$pprefix.'-tab-active' : ''; ?>"><?php echo esc_html(__('Plugin Options','chordpress'));?></a>
      </h2>

      <form name="form" action="<?php echo admin_url('admin.php?page='.$pname.'%2Fadmin%2Fpartials%2Fchordpress-admin-options.php')?>" method="post">

         <?php settings_fields( $pname.'_options_group' ); ?>

         <?php 
         //  ,--------------,
         // _| Tab: Output  |_
         //
         if ($active_tab=='tab_output') $display = "display:block;"; else $display = "display:none;";
         ?>
         <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
            <tbody>
               <tr>
                  <td scope="row" colspan="2">
                     <div class="<?php echo $pprefix;?>-callout <?php echo $pprefix;?>-callout-info">
                        <?php echo esc_html(__('Use this tab to select what sections and directives will be part of the ChordPress output.','chordpress'));?><br>
                     </div>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Album','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hide_album' name='<?php echo $pname;?>_checkbox_hide_album' <?php checked( get_option($pname.'_checkbox_hide_album'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-album.html" target="_blank"><?php echo esc_html(__('album directive','chordpress'));?></a> <?php echo esc_html(__('will not be displayed','chordpress'));?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Artist','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hide_artist' name='<?php echo $pname;?>_checkbox_hide_artist' <?php checked( get_option($pname.'_checkbox_hide_artist'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-artist.html" target="_blank"><?php echo esc_html(__('artist directive','chordpress'));?></a> <?php echo esc_html(__('will not be displayed','chordpress'));?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Chords','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hide_chords' name='<?php echo $pname;?>_checkbox_hide_chords' <?php checked( get_option($pname.'_checkbox_hide_chords'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, only the lyrics will be printed (no chords above them)','chordpress'));?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Comments','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hide_comments' name='<?php echo $pname;?>_checkbox_hide_comments' <?php checked( get_option($pname.'_checkbox_hide_comments'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-comment.html" target="_blank"><?php echo esc_html(__('comment directive','chordpress'));?></a> <?php echo esc_html(__('will not be displayed','chordpress'));?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Composer','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hide_composer' name='<?php echo $pname;?>_checkbox_hide_composer' <?php checked( get_option($pname.'_checkbox_hide_composer'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-composer.html" target="_blank"><?php echo esc_html(__('composer directive','chordpress'));?></a> <?php echo esc_html(__('will not be displayed','chordpress'));?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Subtitle','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hide_subtitle' name='<?php echo $pname;?>_checkbox_hide_subtitle' <?php checked( get_option($pname.'_checkbox_hide_subtitle'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-subtitle.html" target="_blank"><?php echo esc_html(__('subtitle directive','chordpress'));?></a> <?php echo esc_html(__('will not be displayed','chordpress'));?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Title','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hide_title' name='<?php echo $pname;?>_checkbox_hide_title' <?php checked( get_option($pname.'_checkbox_hide_title'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-title.html" target="_blank"><?php echo esc_html(__('title directive','chordpress'));?></a> <?php echo esc_html(__('will not be displayed','chordpress'));?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Year','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hide_year' name='<?php echo $pname;?>_checkbox_hide_year' <?php checked( get_option($pname.'_checkbox_hide_year'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-year.html" target="_blank"><?php echo esc_html(__('year directive','chordpress'));?></a> <?php echo esc_html(__('will not be displayed','chordpress'));?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('H/B Notation','chordpress'));?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname;?>_checkbox_hb_notation' name='<?php echo $pname;?>_checkbox_hb_notation' <?php checked( get_option($pname.'_checkbox_hb_notation'), 1 ); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, chords are displayed in H/B notation: H is used instead of B, B is used insetad of Bb. This notation is used in some European countries. Read more about it','chordpress'));?> <a href="https://sayandsound.lewe.com/note-h/" target="_blank"><?php echo esc_html(__('here','chordpress'));?></a>.</p>
                  </td>
               </tr>
               <tr>
                  <th sope="row" colspan="2"><?php submit_button(); ?></th>
               </tr>
            </tbody>
         </table>

         <?php 
         //  ,-----------------,
         // _| Tab: Formatting |_
         //
         if ($active_tab=='tab_formatting') $display = "display:block;"; else $display = "display:none;"; 
         ?>
         <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
            <tbody>
               <tr>
                  <td scope="row" colspan="2">
                     <div class="<?php echo $pprefix;?>-callout <?php echo $pprefix;?>-callout-info">
                        <?php echo esc_html(__('Use this tab to select styles and formats of the ChordPress output.','chordpress'));?><br>
                     </div>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_select_title_level"><?php echo esc_html(__('Title Level','chordpress'));?></label></th>
                  <td>
                     <?php 
                        $defaultValue = 'h1';
                        $selection = get_option($pname.'_select_title_level');
                        if (!strlen($selection)) $selection=$defaultValue;
                     ?>
                     <select id="<?php echo $pname;?>_select_title_level" name="<?php echo $pname;?>_select_title_level">
                        <option value='h1' <?php echo ($selection=='h1')?"selected":""; ?>>Header 1</option>
                        <option value='h2' <?php echo ($selection=='h2')?"selected":""; ?>>Header 2</option>
                        <option value='h3' <?php echo ($selection=='h3')?"selected":""; ?>>Header 3</option>
                        <option value='h4' <?php echo ($selection=='h4')?"selected":""; ?>>Header 4</option>
                        <option value='h5' <?php echo ($selection=='h5')?"selected":""; ?>>Header 5</option>
                        <option value='h6' <?php echo ($selection=='h6')?"selected":""; ?>>Header 6</option>
                     </select>
                     <p class="description"><?php echo esc_html(__('Select the header level for the title directive.','chordpress'));?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_select_subtitle_level"><?php echo esc_html(__('Subtitle Level','chordpress'));?></label></th>
                  <td>
                     <?php 
                        $defaultValue = 'h2';
                        $selection = get_option($pname.'_select_subtitle_level');
                        if (!strlen($selection)) $selection=$defaultValue;
                     ?>
                     <select id="<?php echo $pname;?>_select_subtitle_level" name="<?php echo $pname;?>_select_subtitle_level">
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
                  <th scope="row"><label for="<?php echo $pname;?>_text_meta_style"><?php echo esc_html(__('Meta Information Style','chordpress'));?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname;?>_text_meta_style" name="<?php echo $pname;?>_text_meta_style" value="<?php echo get_option($pname.'_text_meta_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter additional CSS for the meta header block, e.g.: font-style: italic;','chordpress'));?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_text_chord_textcolor"><?php echo esc_html(__('Chord Text Color','chordpress'));?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname;?>_text_chord_textcolor" name="<?php echo $pname;?>_text_chord_textcolor" value="<?php echo get_option($pname.'_text_chord_textcolor'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the chord text color incl. a leading hash, e.g. #990000. Leave emtpy or enter "inherit" to use your theme default.','chordpress'));?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_text_chord_textsize"><?php echo esc_html(__('Chord Text Size','chordpress'));?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname;?>_text_chord_textsize" name="<?php echo $pname;?>_text_chord_textsize" value="<?php echo get_option($pname.'_text_chord_textsize'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the text size of the chords in pixel, em or percent, e.g. 150%. Leave emtpy or enter "inherit" to use your theme default.','chordpress'));?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_text_lyrics_textcolor"><?php echo esc_html(__('Lyrics Text Color','chordpress'));?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname;?>_text_lyrics_textcolor" name="<?php echo $pname;?>_text_lyrics_textcolor" value="<?php echo get_option($pname.'_text_lyrics_textcolor'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the lyrics text color incl. a leading hash, e.g. #990000. Leave emtpy or enter "inherit" to use your theme default.','chordpress'));?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_text_lyrics_textsize"><?php echo esc_html(__('Lyrics Text Size','chordpress'));?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname;?>_text_lyrics_textsize" name="<?php echo $pname;?>_text_lyrics_textsize" value="<?php echo get_option($pname.'_text_lyrics_textsize'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the text size of the lyrics in pixel, em or percent, e.g. 150%. Leave emtpy or enter "inherit" to use your theme default.','chordpress'));?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_text_comment_style"><?php echo esc_html(__('Comment Style','chordpress'));?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname;?>_text_comment_style" name="<?php echo $pname;?>_text_comment_style" value="<?php echo get_option($pname.'_text_comment_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS for comments, e.g.: background-color: #606060; font-style: italic; padding: 4px;','chordpress'));?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_text_chorus_style"><?php echo esc_html(__('Chorus Style','chordpress'));?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname;?>_text_chorus_style" name="<?php echo $pname;?>_text_chorus_style" value="<?php echo get_option($pname.'_text_chorus_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS for the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-env_chorus.html" target="_blank"><?php echo esc_html(__('chorus sections','chordpress'));?>.</a></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_text_verse_style"><?php echo esc_html(__('Verse Style','chordpress'));?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname;?>_text_verse_style" name="<?php echo $pname;?>_text_verse_style" value="<?php echo get_option($pnmae.'_text_verse_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS for the','chordpress'));?> <a href="https://www.chordpro.org/chordpro/Directives-env_verse.html" target="_blank"><?php echo esc_html(__('verse sections','chordpress'));?>.</a></p>
                  </td>
               </tr>
               <tr>
                  <th sope="row" colspan="2"><?php submit_button(); ?></th>
               </tr>
            </tbody>
         </table>

         <?php 
         //  ,---------------------,
         // _| Tab: Plugin Options |_
         //
         if ($active_tab=='tab_plugin') $display = "display:block;"; else $display = "display:none;"; 
         ?>
         <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
            <tbody>
               <tr>
                  <td scope="row" colspan="2">
                     <div class="<?php echo $pprefix;?>-callout <?php echo $pprefix;?>-callout-info">
                        <?php echo esc_html(__('Select general options for this plugin.','chordpress'));?><br>
                     </div>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname;?>_checkbox_uninstall_delete"><?php echo esc_html(__('Remove data on uninstall','chordpress'));?></label></th>
                  <td>
                     <p><input type="checkbox" id="<?php echo $pname;?>_checkbox_uninstall_delete" name="<?php echo $pname;?>_checkbox_uninstall_delete" <?php checked( get_option($pname.'_checkbox_uninstall_delete'), 1 ); ?> value='1'>&nbsp;<?php echo esc_html(__('Yes','chordpress'));?></p>
                     <p class="description"><?php echo esc_html(__('Remove plugin options from the database when the plugin is deleted (does not apply when the plugin is just deactivated).','chordpress'));?></p>
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
