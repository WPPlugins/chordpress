/**
 * ChhordPress Javascript
 *
 * Custom Javascript functions for the ChordPress plugin.
 *
 * @author George Lewe
 * @link https://www.lewe.com
 *
 * @package ChordPress
 * @subpackage Scripts
 * @since 1.0.0
 */

/**
 * Hides an HTML parent element via CSS.
 *
 * This function hides the parent element of the given one by settingg its
 * display style property to 'none'.
 *
 * @since 1.0.0
 *
 * @param object $el HTML object.
 */
function hideParent(el)
{
   el.parentNode.style.display='none';
};

/**
 * Transposes chords up
 *
 * This function hides all chord divs, then shows those for the given transpose
 * value.
 *
 * @since 1.3.0
 *
 * @param string t Transpose value
 */
function transposeChords()
{
   //
   // Get value from Transpose listbox
   //
   var x = document.getElementById("selTranspose").value;
   //
   // Hide all hidable chords
   //
   var divsToHide = document.getElementsByClassName("chord-hidable");
   for(var i = 0; i < divsToHide.length; i++){
      divsToHide[i].style.display = "none";
   }
   //
   // Now show those for the selected transpose value
   //
   var divsToShow = document.getElementsByClassName("key-"+x);
   for(var i = 0; i < divsToShow.length; i++){
      divsToShow[i].style.display = "";
   }
}