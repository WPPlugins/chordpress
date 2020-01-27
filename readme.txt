=== ChordPress - ChordPro Text Formatter ===
Contributors: glewe,rlisle
Donate link: https://www.paypal.me/GeorgeLewe
Tags: chordpro,chord,chords,lyrics,music,notes,sheet
Requires at least: 4.0
Tested up to: 5.3.2
Stable tag: trunk
Requires PHP: 5.2.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.html
 
ChordPress for WordPress pretty-prints ChordPro formatted text files on your pages or posts.

== Description ==
ChordPress pretty-prints ChordPro formatted text files on your pages and posts. ChordPro is a simple text syntax standard used to write lead sheets, the lyrics and chords of a song. ChordPro also comes with a command line tool that pretty prints that text into other formats like PDF.
You can read all about ChordPro here:
[ChordPro](https://www.chordpro.org/chordpro/ChordPro-File-Format-Specification.html "ChordPro file format specification")

The ChordPress plugin allows to put ChordPro text inside its shortcode tags on a page or post and will pretty print it when the web page is displayed.

Simply insert a ChordPro text inbetween the [chordpress] and [/chordpress] tags and it will be rendered based on the default options of the plugin. You can change these default settings on the plugin's option page.

A TinyMCE editor integration is also included, providing an editor menu button to insert the shortcode for you with its default paramaters, e.g. wrapping it around selected text.

The shortags allow specifying custom options that will overwrite the defaults.
For example: [chordpress transpose="1"] will transpose the chords rendered by one semitone.

Check out this example:
[Chordpress Demo](https://sayandsound.lewe.com/heart-of-gold/ "Chordpress Demo")

If not from the WordPress marketplace, get the plugin here: 
[ChordPress Homepage](https://www.lewe.com/chordpress-for-wordpress/ "ChordPress Homepage")

Credits for the beautiful icon go to Ahkâm:
[ChordPress Plugin Icon](https://www.freeiconspng.com/img/17579 "ChordPress Plugin Icon")

= Features =
ChordPress renders the lyrics/chord format of ChordPro.

ChordPress supports the following ChordPro tags:
* {album}
* {artist}
* {composer}
* {subtitle}
* {title}
* {year}
* {meta} for all of the above

ChordPress supports custom CSS for the following ChordPro tags:
* {comment}
* {start_of_chorus}, {end_of_chorus}
* {start_of_verse}, {end_of_verse}

ChordPress supports note/chord translations
* Chord transponation
* [H/B Notation](https://sayandsound.lewe.com/note-h/)

= Usage =
1. Enter a [chordpress] shortcode in your page or post editor
2. Paste your ChordPro text right after it
3. Enter the closing [/chordpress] shortcode

Optional shortcode parameters
* float=  Forces the rendered text block to float left or right.
* hbnotation=  [Input is H/B notation](https://sayandsound.lewe.com/note-h/) (using H for B and Bb for B). Wether it is displayed as such is defined on the Options page.
* interactive=  Enables interactive mode. A listbox will be shown on the right of the title that allows you to transpose the chords on the fly.
* transpose=  Enter a positive or negative number for the amount of semitones to transpose the chords

Use the "ChordPress" sub-menu of your backend Settings to change the default settings.

Read more here: [ChordPress User Manual](https://support.lewe.com/docs/chordpro-for-wordpress-manual/ "ChordPress User Manual")
      
= Support =
Choose your preferred support channel:
1. [Wordpress Support Forum](https://wordpress.org/support/plugin/chordpress/ "Wordpress Support Forum")
2. [Lewe.com Support Site](http://support.lewe.com/ "Lewe.com Support Site")
3. [Lewe.com ChordPress Forum](http://forum.lewe.com/viewforum.php?f=41 "Lewe.com ChordPress Forum")
4. [ChordPress User Manual](https://support.lewe.com/docs/chordpro-for-wordpress-manual/ "ChordPress User Manual")

== Screenshots ==
1. ChordPress Display Options
2. ChordPress Formatting Options
3. ChordPro text in editor
4. Rendered text on webpage

== Frequently Asked Questions ==
= Why are empty ChordPro lines not rendered ? =
Empty lines are stripped from shortcode content and are not passed to the plugin. A good way to work around that is to use the {start_of_verse}/{start_of_chorus} tags and give them a top/bottom margin on the options page.
= What is teh H/B notation ? =
The H/B notation uses H instead of B and B is used insetad of Bb. This notation is used in some European countries. Read more about it here: [H/BN Notation](https://sayandsound.lewe.com/note-h/ "H/B Notation").

== Installation ==
= Backend Installation =
1. Go to Plugin page
2. Click Add New
3. Enter ChordPress in search field
4. Install and Activate

= Manual Installation =
1. Download the plugin ZIP file
2. Unpack the ZIP file locally
3. Upload the 'chordpress' folder to your '/wp-content/plugins/' directory
4. Activate the plugin on the 'Plugins' page of your WordPress backend

== Changelog ==
= 1.3.0 =
* Added interactive mode

= 1.2.0 =
* Fixed duplicate plugin entry bug
* Reworked the plugins settings page
* Added TinyMCE integration

= 1.0.5 =
* Updated documentation and support links
* Compatibility: WordPress 5.3.2

= 1.0.4 =
* Fixed: Bass notes were not rendered correctly, e.g. A/C#
* Compatibility: WordPress 5.1

= 1.0.3 =
* Readme update

= 1.0.2 =
* Added screenshots, icons and banners

= 1.0.1 =
* Fixed hard coded image path

= 1.0.0 =
* Initital development for http://sayandsound.lewe.com

== Upgrade Notice ==
