/**
 * ChordPress TinyMCE Buttons
 *
 * Custom Javascript functions for the ChordPress plugin.
 *
 * @author George Lewe
 * @link https://www.lewe.com
 *
 * @package ChordPress
 * @subpackage Scripts
 * @since 2.0.0
 */
(function() {
   tinymce.PluginManager.add( 'cprbtn', function( editor, url ) {
      //
      // Submenu Array
      //
      var menu_array = [];

      //
      // Chordpress
      //
		menu_array.push({
         text:"[chordpress]",
         value:'[chordpress float="none" format="yes" transpose"0" hbnotation="no"]%content%[/chordpress]',
         onclick:function() {
            var selected_text = editor.selection.getContent({'format': 'html'});
            return_text = this.value().replace("%content%", selected_text);
            editor.execCommand('mceReplaceContent', false, return_text);
         }
      });

      //
      // Separator
      //
		menu_array.push({
         text:"---",
         value:'',
         onclick:function() {}
      });

      //
      // Documentation
      //
		menu_array.push({
         text:"Documentation...",
         value:'',
         onclick:function() {window.open('https://support.lewe.com/docs/chordpro-for-wordpress-manual/', '_blank');}
      });

      //
      // Add the submenu button
      //
      editor.addButton( 'cprbtn', {
         title: 'ChordPress',
         type: 'menubutton',
         image: url + '/icon-20x20.png',
         menu: menu_array
      });

      //
      // Single menu button
      //
      // editor.addButton('bsvbtn', {
      //    title: 'Bootstrap Visuals - Click button to add a Shortcoe',
      //    cmd: 'bsvbtn',
      //    image: url + '/bsvbtn.png',
      // });

      // editor.addCommand('bsvbtn', function() {
      //    var selected_text = editor.selection.getContent({
      //       'format': 'html'
      //    });
      //    var open_column = '[bs-alert]' + selected_text + '[/bsv-alert]';
      //    var close_column = '';
      //    var return_text = '';
      //    return_text = open_column + close_column;
      //    editor.execCommand('mceReplaceContent', false, return_text);
      //    return;
      // });

   });
})();