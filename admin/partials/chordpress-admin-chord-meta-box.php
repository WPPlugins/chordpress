<?php

/**
 * Chord content type meta box
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

function display_guitar_chord_meta_box($guitar_chord) {

   $C = new ChordPress_Chord();
   $chord = array();

   /**
    * Retrieve current values
    */
   $guitar_chord_shortname = esc_html(get_post_meta($guitar_chord->ID, 'guitar_chord_shortname', true));
   if (!$guitar_chord_position = intval(get_post_meta($guitar_chord->ID, 'guitar_chord_position', true))) $guitar_chord_position = $C->getProperty('position');;
   if (!$guitar_chord_numstrings = intval(get_post_meta($guitar_chord->ID, 'guitar_chord_numstrings', true))) $guitar_chord_numstrings = $C->getProperty('numStrings');
   if (!strlen($guitar_chord_tuning = esc_html(get_post_meta($guitar_chord->ID, 'guitar_chord_tuning', true)))) $guitar_chord_tuning = "E,A,D,G,B,E";
   $guitar_chord_single_frets = get_post_meta($guitar_chord->ID, 'guitar_chord_single_frets', true);
   $guitar_chord_single_labels = get_post_meta($guitar_chord->ID, 'guitar_chord_single_labels', true);
   $guitar_chord_barre_frets = esc_html(get_post_meta($guitar_chord->ID, 'guitar_chord_barre_frets', true));

   /**
    * Build the sample diagram
    */
   $C->setProperty('title', $guitar_chord->post_title);
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

      for ($i = 1; $i <= 6; $i++) {
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

   $leftwidth = "20%";

?>

   <table>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Short Name', 'chordpress')); ?></td>
         <td>
            <input type="text" name="guitar_chord_shortname" value="<?php echo $guitar_chord_shortname; ?>" />
            <p class="description"><?php echo esc_html(__('Enter the shortname of the chord here, e.g. Em.', 'chordpress')); ?></p>
         </td>
      </tr>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Base Fret', 'chordpress')); ?></td>
         <td>
            <select style="width: 100px" name="guitar_chord_position">
               <?php for ($pos = 0; $pos <= 12; $pos++) { ?>
                  <option value="<?php echo $pos; ?>" <?php echo selected($pos, $guitar_chord_position); ?>><?php echo $pos; ?>. fret</option>
               <?php } ?>
            </select>
            <p class="description"><?php echo esc_html(__('At what fret shall the diagram start to draw?', 'chordpress')); ?></p>
         </td>
      </tr>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Number of Strings', 'chordpress')); ?></td>
         <td>
            <select style="width: 100px" name="guitar_chord_numstrings">
               <option value="4" <?php echo selected('4', $guitar_chord_numstrings); ?>>4</option>
               <option value="6" <?php echo selected('6', $guitar_chord_numstrings); ?>>6</option>
            </select>
            <p class="description"><?php echo esc_html(__('Select the number of strings, 4 for a bass guitar or 6 for a regular guitar. Default is 6.', 'chordpress')); ?></p>
         </td>
      </tr>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Tuning', 'chordpress')); ?></td>
         <td>
            <input type="text" maxlength="11" name="guitar_chord_tuning" value="<?php echo $guitar_chord_tuning; ?>" />
            <p class="description"><?php echo esc_html(__('Enter the base tuning of each string, separated by a comma, starting at the lowest one, e.g. E,A,D,G,B,E.', 'chordpress')); ?></p>
         </td>
      </tr>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Single Frets', 'chordpress')); ?></td>
         <td>
            <input type="text" maxlength="23" name="guitar_chord_single_frets" value="<?php echo $guitar_chord_single_frets; ?>" />
            <p class="description"><?php echo esc_html(__('Enter the single frets of each string to press for this chord, separated by a comma, starting at the lowest string, e.g. 0,2,2,1,0,0 for E Major on a default tuned guitar. Enter an \'x\' in single quotes for a muted string.', 'chordpress')); ?></p>
         </td>
      </tr>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Single Labels', 'chordpress')); ?></td>
         <td>
            <input type="text" maxlength="17" name="guitar_chord_single_labels" value="<?php echo $guitar_chord_single_labels; ?>" />
            <p class="description"><?php echo esc_html(__('You can enter labels here for each single fret (e.g. the note or finger number), separated by a comma, starting at the lowest string.', 'chordpress')); ?><br><?php echo esc_html(__('Enter nothing for a string you don\'t want a label for but add the corresponding comma, e.g. ,B,E,G#,, for E Major on a default tuned guitar.', 'chordpress')); ?></p>
         </td>
      </tr>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Barre Frets', 'chordpress')); ?></td>
         <td>
            <input type="text" maxlength="6" name="guitar_chord_barre_frets" value="<?php echo $guitar_chord_barre_frets; ?>" />
            <p class="description"><?php echo esc_html(__('Enter the barre frets for this chord in the form of "fromString,toString,fret", starting at the lowest string, e.g. 3,6,3 to barre the highest four strings in the third fret.', 'chordpress')); ?></p>
         </td>
      </tr>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Preview', 'chordpress')); ?></td>
         <td>
            <?php echo $C->addVexchord(); ?>
            <p class="description"><?php echo esc_html(__('The above diagram uses the', 'chordpress')); ?> <a href="admin.php?page=chordpress/admin/partials/chordpress-admin-options.php&tab=tab_chord" target="_blank"><?php echo esc_html(__('global guitar chord styles', 'chordpress')); ?>.</p>
         </td>
      </tr>

      <tr>
         <td style="width: <?php echo $leftwidth; ?>;"><?php echo esc_html(__('Shortcode', 'chordpress')); ?></td>
         <td>
            <code>[chordpress-chord chord="<?php echo $guitar_chord->ID ?>"]</code>
            <p class="description"><?php echo esc_html(__('Use this shortcode to display this chord.', 'chordpress')); ?></p>
         </td>
      </tr>

   </table>
<?php

}
