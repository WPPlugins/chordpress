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
if (!defined('WPINC')) {
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
$options = $P->get_options();

/**
 * ,--------------,
 * | Save Changes |
 * '--------------'
 */
if (isset($_REQUEST['submit'])) {

   $show_alert = true;
   $alert_style = 'success';

   foreach ($options as $option) {

      if (isset($_REQUEST[$option])) {

         if (get_option($option) != $_REQUEST[$option]) {

            if (!update_option($option, $_REQUEST[$option])) {

               $alert_style = 'danger';
            }
         }
      } else {

         /**
          * Unchecked checkboxes will not be in the $_REQUEST array.
          * Make sure we set them to 0.
          */
         if (strpos($option, 'checkbox')) {

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

      $alert_title = esc_html(__('Success', 'chordpress'));
      $alert_text = esc_html(__('The changes were saved successfully.', 'chordpress'));
   } else {

      $alert_title = esc_html(__('Error', 'chordpress'));
      $alert_text = esc_html(__('An error occurred while trying to save your settings.', 'chordpress'));
   }
}

?>

<div class="wrap">

   <div style="background:#003A6B !important;width:100% !important;height:80px !important;">
      <img src="<?php echo plugins_url('../images/banner-247x80.png', __FILE__); ?>" alt="" style="text-align:left;">
   </div>
   <div style="clear:both;">
      <h1><?php echo esc_html(__($ptitle . ' Settings', 'chordpress')); ?></h1>
      <p>
         <a href="<?php echo $puri; ?>" target="_blank" class="<?php echo $pprefix; ?>-btn <?php echo $pprefix; ?>-btn-info <?php echo $pprefix; ?>-btn-xs"><?php echo esc_html(__('Documentation', 'chordpress')); ?></a>
         <?php if (!CHORDPRESS_LICENSE_REQUIRED) { ?><a href="<?php echo $pdonuri; ?>" target="_blank" class="<?php echo $pprefix; ?>-btn <?php echo $pprefix; ?>-btn-primary <?php echo $pprefix; ?>-btn-xs"><?php echo esc_html(__('Donate', 'chordpress')); ?></a><?php } ?>
      </p>
   </div>

   <?php
   if ($show_alert) echo $P->alert($alert_style, $alert_title, 'h4', $alert_text, true, '98%');
   settings_errors();
   $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'tab_output';
   ?>

   <div style="clear:both;">

      <!-- Tabs -->
      <h2 class="nav-tab-wrapper">
         <a href="?page=<?php echo __FILE__; ?>&tab=tab_output" class="nav-tab <?php echo $active_tab == 'tab_output' ? 'nav-tab-active cpress-tab-active' : 'cpress-tab-inactive'; ?>"><?php echo esc_html(__('Output Options', 'chordpress')); ?></a>
         <a href="?page=<?php echo __FILE__; ?>&tab=tab_formatting" class="nav-tab <?php echo $active_tab == 'tab_formatting' ? 'nav-tab-active cpress-tab-active' : 'cpress-tab-inactive'; ?>"><?php echo esc_html(__('Formatting Options', 'chordpress')); ?></a>
         <a href="?page=<?php echo __FILE__; ?>&tab=tab_chord" class="nav-tab <?php echo $active_tab == 'tab_chord' ? 'nav-tab-active cpress-tab-active' : 'cpress-tab-inactive'; ?>"><?php echo esc_html(__('VexChords Options', 'chordpress')); ?></a>
         <a href="?page=<?php echo __FILE__; ?>&tab=tab_jtab" class="nav-tab <?php echo $active_tab == 'tab_jtab' ? 'nav-tab-active cpress-tab-active' : 'cpress-tab-inactive'; ?>"><?php echo esc_html(__('jTab Options', 'chordpress')); ?></a>
         <a href="?page=<?php echo __FILE__; ?>&tab=tab_plugin" class="nav-tab <?php echo $active_tab == 'tab_plugin' ? 'nav-tab-active ' . $pprefix . '-tab-active' : 'cpress-tab-inactive'; ?>"><?php echo esc_html(__('Plugin Options', 'chordpress')); ?></a>
      </h2>

      <form name="form" action="<?php echo admin_url('admin.php?page=' . $pname . '%2Fadmin%2Fpartials%2Fchordpress-admin-options.php') ?>" method="post">

         <?php settings_fields($pname . '_options_group'); ?>

         <?php
         //  ,--------------,
         // _| Tab: Output  |_
         //
         if ($active_tab == 'tab_output') $display = "display:block;";
         else $display = "display:none;";
         ?>
         <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
            <tbody>
               <tr>
                  <td scope="row" colspan="2">
                     <div class="<?php echo $pprefix; ?>-callout <?php echo $pprefix; ?>-callout-info">
                        <?php echo esc_html(__('Use this tab to select what sections and directives will be part of the rendered ChordPro text output.', 'chordpress')); ?><br>
                     </div>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Artist', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_hide_artist' name='<?php echo $pname; ?>_checkbox_hide_artist' <?php checked(get_option($pname . '_checkbox_hide_artist'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the', 'chordpress')); ?> <a href="https://www.chordpro.org/chordpro/Directives-artist.html" target="_blank"><?php echo esc_html(__('artist directive', 'chordpress')); ?></a> <?php echo esc_html(__('will not be displayed', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Chords', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_hide_chords' name='<?php echo $pname; ?>_checkbox_hide_chords' <?php checked(get_option($pname . '_checkbox_hide_chords'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, only the lyrics will be printed (no chords above them)', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Comments', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_hide_comments' name='<?php echo $pname; ?>_checkbox_hide_comments' <?php checked(get_option($pname . '_checkbox_hide_comments'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the', 'chordpress')); ?> <a href="https://www.chordpro.org/chordpro/Directives-comment.html" target="_blank"><?php echo esc_html(__('comment directive', 'chordpress')); ?></a> <?php echo esc_html(__('will not be displayed', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Composer', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_hide_composer' name='<?php echo $pname; ?>_checkbox_hide_composer' <?php checked(get_option($pname . '_checkbox_hide_composer'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the', 'chordpress')); ?> <a href="https://www.chordpro.org/chordpro/Directives-composer.html" target="_blank"><?php echo esc_html(__('composer directive', 'chordpress')); ?></a> <?php echo esc_html(__('will not be displayed', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Subtitle', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_hide_subtitle' name='<?php echo $pname; ?>_checkbox_hide_subtitle' <?php checked(get_option($pname . '_checkbox_hide_subtitle'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the', 'chordpress')); ?> <a href="https://www.chordpro.org/chordpro/Directives-subtitle.html" target="_blank"><?php echo esc_html(__('subtitle directive', 'chordpress')); ?></a> <?php echo esc_html(__('will not be displayed', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Title', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_hide_title' name='<?php echo $pname; ?>_checkbox_hide_title' <?php checked(get_option($pname . '_checkbox_hide_title'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the', 'chordpress')); ?> <a href="https://www.chordpro.org/chordpro/Directives-title.html" target="_blank"><?php echo esc_html(__('title directive', 'chordpress')); ?></a> <?php echo esc_html(__('will not be displayed', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Hide Year', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_hide_year' name='<?php echo $pname; ?>_checkbox_hide_year' <?php checked(get_option($pname . '_checkbox_hide_year'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, the', 'chordpress')); ?> <a href="https://www.chordpro.org/chordpro/Directives-year.html" target="_blank"><?php echo esc_html(__('year directive', 'chordpress')); ?></a> <?php echo esc_html(__('will not be displayed', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('H/B Notation', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_hb_notation' name='<?php echo $pname; ?>_checkbox_hb_notation' <?php checked(get_option($pname . '_checkbox_hb_notation'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, chords are displayed in H/B notation: H is used instead of B, B is used insetad of Bb. This notation is used in some European countries. Read more about it', 'chordpress')); ?> <a href="https://sayandsound.lewe.com/note-h/" target="_blank"><?php echo esc_html(__('here', 'chordpress')); ?></a>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Show Chord Sheet (Vexchord)', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_show_chord_sheet' name='<?php echo $pname; ?>_checkbox_show_chord_sheet' <?php checked(get_option($pname . '_checkbox_show_chord_sheet'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, a chord sheet is displayed at the bottom or top of the rendered song, showing ', 'chordpress')); ?> <a href="https://github.com/0xfe/vexchords" target="_blank"><?php echo esc_html(__('VexChords', 'chordpress')); ?></a> <?php echo esc_html(__(' diagrams of each chord used in the song. This requires that you create a ', 'chordpress')); ?> <a href="edit.php?post_type=guitar_chord" target="_blank"><?php echo esc_html(__('guitar chord post', 'chordpress')); ?></a> <?php echo esc_html(__('for each of them', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Show Chord Sheet (jTab)', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_show_jtab_sheet' name='<?php echo $pname; ?>_checkbox_show_jtab_sheet' <?php checked(get_option($pname . '_checkbox_show_jtab_sheet'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('With this option selected, a chord sheet is displayed at the bottom or top of the rendered song, showing diagrams of each known ', 'chordpress')); ?> <a href="https://jtab.tardate.com/" target="_blank"><?php echo esc_html(__('jTab', 'chordpress')); ?></a> <?php echo esc_html(__(' chord used in the song.', 'chordpress')); ?>.</p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><?php echo esc_html(__('Show Chord Sheets on top', 'chordpress')); ?></th>
                  <td>
                     <input type='checkbox' id='<?php echo $pname; ?>_checkbox_show_chord_sheet_on_top' name='<?php echo $pname; ?>_checkbox_show_chord_sheet_on_top' <?php checked(get_option($pname . '_checkbox_show_chord_sheet_on_top'), 1); ?> value='1'>
                     <p class="description"><?php echo esc_html(__('If one fo the Chord Sheet options is selected, this option will display it on top before the song instead of at the bottom after it.', 'chordpress')); ?></p>
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
         if ($active_tab == 'tab_formatting') $display = "display:block;";
         else $display = "display:none;";
         ?>
         <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
            <tbody>
               <tr>
                  <td scope="row" colspan="2">
                     <div class="<?php echo $pprefix; ?>-callout <?php echo $pprefix; ?>-callout-info">
                        <?php echo esc_html(__('Use this tab to select styles and formats of the rendered ChordPro text.', 'chordpress')); ?><br>
                     </div>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_select_title_level"><?php echo esc_html(__('Title Level', 'chordpress')); ?></label></th>
                  <td>
                     <?php
                     $defaultValue = 'h1';
                     $selection = get_option($pname . '_select_title_level');
                     if (!strlen($selection)) $selection = $defaultValue;
                     ?>
                     <select id="<?php echo $pname; ?>_select_title_level" name="<?php echo $pname; ?>_select_title_level">
                        <option value='h1' <?php echo ($selection == 'h1') ? "selected" : ""; ?>>Header 1</option>
                        <option value='h2' <?php echo ($selection == 'h2') ? "selected" : ""; ?>>Header 2</option>
                        <option value='h3' <?php echo ($selection == 'h3') ? "selected" : ""; ?>>Header 3</option>
                        <option value='h4' <?php echo ($selection == 'h4') ? "selected" : ""; ?>>Header 4</option>
                        <option value='h5' <?php echo ($selection == 'h5') ? "selected" : ""; ?>>Header 5</option>
                        <option value='h6' <?php echo ($selection == 'h6') ? "selected" : ""; ?>>Header 6</option>
                     </select>
                     <p class="description"><?php echo esc_html(__('Select the header level for the title directive.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_select_subtitle_level"><?php echo esc_html(__('Subtitle Level', 'chordpress')); ?></label></th>
                  <td>
                     <?php
                     $defaultValue = 'h2';
                     $selection = get_option($pname . '_select_subtitle_level');
                     if (!strlen($selection)) $selection = $defaultValue;
                     ?>
                     <select id="<?php echo $pname; ?>_select_subtitle_level" name="<?php echo $pname; ?>_select_subtitle_level">
                        <option value='h1' <?php echo ($selection == 'h1') ? "selected" : ""; ?>>Header 1</option>
                        <option value='h2' <?php echo ($selection == 'h2') ? "selected" : ""; ?>>Header 2</option>
                        <option value='h3' <?php echo ($selection == 'h3') ? "selected" : ""; ?>>Header 3</option>
                        <option value='h4' <?php echo ($selection == 'h4') ? "selected" : ""; ?>>Header 4</option>
                        <option value='h5' <?php echo ($selection == 'h5') ? "selected" : ""; ?>>Header 5</option>
                        <option value='h6' <?php echo ($selection == 'h6') ? "selected" : ""; ?>>Header 6</option>
                     </select>
                     <p class="description"><?php echo esc_html(__('Select the header level for the subtitle directive.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_meta_style"><?php echo esc_html(__('Meta Information Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_meta_style" name="<?php echo $pname; ?>_text_meta_style" value="<?php echo get_option($pname . '_text_meta_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter CSS for the meta header block, e.g.: font-style: italic;', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_style"><?php echo esc_html(__('Chord Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_style" name="<?php echo $pname; ?>_text_chord_style" value="<?php echo get_option($pname . '_text_chord_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter CSS for the chords here or leave empty to use the page default.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_lyrics_style"><?php echo esc_html(__('Lyrics Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_lyrics_style" name="<?php echo $pname; ?>_text_lyrics_style" value="<?php echo get_option($pname . '_text_lyrics_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter CSS for the lyrics here or leave empty to use the page default.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_comment_style"><?php echo esc_html(__('Comment Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_comment_style" name="<?php echo $pname; ?>_text_comment_style" value="<?php echo get_option($pname . '_text_comment_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter CSS for comments, e.g.: background-color: #606060; font-style: italic; padding: 4px;', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chorus_style"><?php echo esc_html(__('Chorus Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chorus_style" name="<?php echo $pname; ?>_text_chorus_style" value="<?php echo get_option($pname . '_text_chorus_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter CSS for the', 'chordpress')); ?> <a href="https://www.chordpro.org/chordpro/Directives-env_chorus.html" target="_blank"><?php echo esc_html(__('chorus sections', 'chordpress')); ?>.</a></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_verse_style"><?php echo esc_html(__('Verse Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_verse_style" name="<?php echo $pname; ?>_text_verse_style" value="<?php echo get_option($pnmae . '_text_verse_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter CSS for the', 'chordpress')); ?> <a href="https://www.chordpro.org/chordpro/Directives-env_verse.html" target="_blank"><?php echo esc_html(__('verse sections', 'chordpress')); ?>.</a></p>
                  </td>
               </tr>
               <tr>
                  <th sope="row" colspan="2"><?php submit_button(); ?></th>
               </tr>
            </tbody>
         </table>

         <?php
         //  ,------------------------,
         // _| Tab: VexChords Options |_
         //
         if ($active_tab == 'tab_chord') $display = "display:block;";
         else $display = "display:none;";
         ?>
         <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
            <tbody>
               <tr>
                  <td scope="row" colspan="2">
                     <div class="<?php echo $pprefix; ?>-callout <?php echo $pprefix; ?>-callout-info">
                        <?php echo esc_html(__('Use this tab to specify global styles and formats for the ChordPress guitar chord diagrams. They can be overwritten by shortcode parameters.', 'chordpress')); ?><br>
                     </div>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_bgcolor"><?php echo esc_html(__('Background Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_bgcolor" name="<?php echo $pname; ?>_text_chord_bgcolor" value="<?php echo get_option($pname . '_text_chord_bgcolor'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the chord diagrams background color incl. a leading hash, e.g. #E0E0E0. Leave emtpy for the default of #FFFFFF.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_canvas_height"><?php echo esc_html(__('Canvas Height', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_canvas_height" name="<?php echo $pname; ?>_text_chord_canvas_height" value="<?php echo get_option($pname . '_text_chord_canvas_height'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the height of the chord diagrams canvas in pixel. Leave emtpy for the default of 180.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_canvas_width"><?php echo esc_html(__('Canvas Width', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_canvas_width" name="<?php echo $pname; ?>_text_chord_canvas_width" value="<?php echo get_option($pname . '_text_chord_canvas_width'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the width of the chord diagrams canvas in pixel. Leave emtpy for the default of 220.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_circles_radius"><?php echo esc_html(__('Circle Radius', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_circles_radius" name="<?php echo $pname; ?>_text_chord_circles_radius" value="<?php echo get_option($pname . '_text_chord_circles_radius'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the circle radius of the chord diagrams canvas in pixel (where to press the string). Leave emtpy for the default of 5.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_default_color"><?php echo esc_html(__('Default Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_default_color" name="<?php echo $pname; ?>_text_chord_default_color" value="<?php echo get_option($pname . '_text_chord_default_color'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the chord diagrams default color incl. a leading hash, e.g. #000099. Leave emtpy for teh default of #666666.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_div_style"><?php echo esc_html(__('DIV Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_div_style" name="<?php echo $pname; ?>_text_chord_div_style" value="<?php echo get_option($pname . '_text_chord_div_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS styles for the div tag of chord diagrams. Leave emtpy for the default of text-align:center;.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_font_family"><?php echo esc_html(__('Font Family', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_font_family" name="<?php echo $pname; ?>_text_chord_font_family" value="<?php echo get_option($pname . '_text_chord_font_family'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS font family for the chord diagrams, e.g. sans-serif. Leave emtpy for the default.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_font_size"><?php echo esc_html(__('Font Size', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_font_size" name="<?php echo $pname; ?>_text_chord_font_size" value="<?php echo get_option($pname . '_text_chord_font_size'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS font size for the chord diagrams. Leave emtpy for the default.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_font_style"><?php echo esc_html(__('Font Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_font_style" name="<?php echo $pname; ?>_text_chord_font_style" value="<?php echo get_option($pname . '_text_chord_font_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS font style for the chord diagrams. Leave emtpy for the default.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_font_weight"><?php echo esc_html(__('Font Weight', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_font_weight" name="<?php echo $pname; ?>_text_chord_font_weight" value="<?php echo get_option($pname . '_text_chord_font_weight'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS font weight for the chord diagrams. Leave emtpy for the default.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_fret_color"><?php echo esc_html(__('Fret Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_fret_color" name="<?php echo $pname; ?>_text_chord_fret_color" value="<?php echo get_option($pname . '_text_chord_fret_color'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the chord diagrams fret color incl. a leading hash, e.g. #990000. Leave emtpy for the default of #666666.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_fret_width"><?php echo esc_html(__('Fret Width', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_fret_width" name="<?php echo $pname; ?>_text_chord_fret_width" value="<?php echo get_option($pname . '_text_chord_fret_width'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the fret width of the chord diagrams canvas in pixel. Leave emtpy for the default of 1.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_label_color"><?php echo esc_html(__('Label Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_label_color" name="<?php echo $pname; ?>_text_chord_label_color" value="<?php echo get_option($pname . '_text_chord_label_color'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the chord diagrams label color incl. a leading hash, e.g. #990000. Leave emtpy for the default of #666666.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_label_weight"><?php echo esc_html(__('Label Weight', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_label_weight" name="<?php echo $pname; ?>_text_chord_label_weight" value="<?php echo get_option($pname . '_text_chord_label_weight'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS label weight for the chord diagrams. Leave emtpy for the default.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_name_style"><?php echo esc_html(__('Name Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_name_style" name="<?php echo $pname; ?>_text_chord_name_style" value="<?php echo get_option($pname . '_text_chord_name_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS styles for the name tag of chord diagrams. Leave emtpy for the default of "font-size:1.2em;text-align:center;".', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_num_strings"><?php echo esc_html(__('Number of Strings', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_num_strings" name="<?php echo $pname; ?>_text_chord_num_strings" value="<?php echo get_option($pname . '_text_chord_num_strings'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the number of strings of the instrument (4 = Bass, 6 = Guitar). Leave emtpy for the default of 6.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_string_color"><?php echo esc_html(__('String Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_string_color" name="<?php echo $pname; ?>_text_chord_string_color" value="<?php echo get_option($pname . '_text_chord_string_color'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the chord diagrams string color incl. a leading hash, e.g. #990000. Leave emtpy for the default of #666666.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_string_width"><?php echo esc_html(__('String Width', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_string_width" name="<?php echo $pname; ?>_text_chord_string_width" value="<?php echo get_option($pname . '_text_chord_string_width'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the fret width of the strings in the chord diagrams. Leave emtpy for the default of 1.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_stroke_color"><?php echo esc_html(__('Stroke Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_stroke_color" name="<?php echo $pname; ?>_text_chord_stroke_color" value="<?php echo get_option($pname . '_text_chord_stroke_color'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the chord diagrams stroke color (finger and fret positions) incl. a leading hash, e.g. #990000. Leave emtpy for the default of #666666.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_text_color"><?php echo esc_html(__('Text Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_text_color" name="<?php echo $pname; ?>_text_chord_text_color" value="<?php echo get_option($pname . '_text_chord_text_color'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the hex code of the chord diagrams text color incl. a leading hash, e.g. #990000. Leave emtpy for the default of #666666.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_chord_title_style"><?php echo esc_html(__('Title Style', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_chord_title_style" name="<?php echo $pname; ?>_text_chord_title_style" value="<?php echo get_option($pname . '_text_chord_title_style'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the CSS styles for the title above the chord diagrams. Leave emtpy for the default of "font-size:1.2em;text-align:center;".', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th sope="row" colspan="2"><?php submit_button(); ?></th>
               </tr>
            </tbody>
         </table>

         <?php
         //  ,-------------------,
         // _| Tab: jTab Options |_
         //
         if ($active_tab == 'tab_jtab') $display = "display:block;";
         else $display = "display:none;";
         ?>
         <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
            <tbody>
               <tr>
                  <td scope="row" colspan="2">
                     <div class="<?php echo $pprefix; ?>-callout <?php echo $pprefix; ?>-callout-info">
                        <?php echo esc_html(__('Use this tab to set jTab chord diagram options.', 'chordpress')); ?><br>
                     </div>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_number_jtab_chords_per_row"><?php echo esc_html(__('Chords per row', 'chordpress')); ?></label></th>
                  <td>
                     <input type="number" min="1" max="7" id="<?php echo $pname; ?>_number_jtab_chords_per_row" name="<?php echo $pname; ?>_number_jtab_chords_per_row" value="<?php echo get_option($pname . '_number_jtab_chords_per_row'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter the number of jTab chords per row. Enter a numeric value between 1 and 7. The deafult is 6.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_jtab_color"><?php echo esc_html(__('Text Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_jtab_color" name="<?php echo $pname; ?>_text_jtab_color" value="<?php echo get_option($pname . '_text_jtab_color'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter CSS color value for the text color of jTab chord diagrams, e.g.: #606060 . Enter "inherit" or leave empty for the theme default.', 'chordpress')); ?></p>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_text_jtab_bgcolor"><?php echo esc_html(__('Background Color', 'chordpress')); ?></label></th>
                  <td>
                     <input type="text" id="<?php echo $pname; ?>_text_jtab_bgcolor" name="<?php echo $pname; ?>_text_jtab_bgcolor" value="<?php echo get_option($pname . '_text_jtab_bgcolor'); ?>" />
                     <p class="description"><?php echo esc_html(__('Enter CSS color value for the background color of jTab chord diagrams, e.g.: #606060 . Enter "inherit" or leave empty for the theme default.', 'chordpress')); ?></p>
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
         if ($active_tab == 'tab_plugin') $display = "display:block;";
         else $display = "display:none;";
         ?>
         <table class="form-table" style="margin-left:16px;<?php echo $display; ?>">
            <tbody>
               <tr>
                  <td scope="row" colspan="2">
                     <div class="<?php echo $pprefix; ?>-callout <?php echo $pprefix; ?>-callout-info">
                        <?php echo esc_html(__('Select general options for this plugin.', 'chordpress')); ?><br>
                     </div>
                  </td>
               </tr>
               <tr>
                  <th scope="row"><label for="<?php echo $pname; ?>_checkbox_uninstall_delete"><?php echo esc_html(__('Remove data on uninstall', 'chordpress')); ?></label></th>
                  <td>
                     <p><input type="checkbox" id="<?php echo $pname; ?>_checkbox_uninstall_delete" name="<?php echo $pname; ?>_checkbox_uninstall_delete" <?php checked(get_option($pname . '_checkbox_uninstall_delete'), 1); ?> value='1'>&nbsp;<?php echo esc_html(__('Yes', 'chordpress')); ?></p>
                     <p class="description"><?php echo esc_html(__('Remove plugin options from the database when the plugin is deleted (does not apply when the plugin is just deactivated).', 'chordpress')); ?></p>
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