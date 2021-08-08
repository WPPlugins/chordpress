<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://www.lewe.com
 * @since      2.0.0
 *
 * @package    ChordPress
 * @subpackage Uninstall
 */

/**
 * If uninstall not called from WordPress, then exit.
 *
 * @since    2.0.0
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * The unique name (slug) of this plugin.
 *
 * @since    2.0.0
 * @var      string    $pname    The ID of this plugin.
 */
$pname = 'chordpress';

/**
 * Exit if admin did not select to delete the plugin data upon uninstall/delete.
 *
 * @since    2.0.0
 */
if (!get_option($pname.'_checkbox_uninstall_delete')) return false;

/**
 * Delete plugin options.
 *
 * @since    2.0.0
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
   $pname.'_text_chord_style',
   $pname.'_text_lyrics_style',
   $pname.'_text_comment_style',
   $pname.'_text_chorus_style',
   $pname.'_text_verse_style'

);

foreach ($options as $option) {

   delete_option($option);
   
}
