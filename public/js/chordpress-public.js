/**
 * Public Javascript
 *
 * @link       https://www.lewe.com
 * @since      1.0.0
 *
 * @package    ChordPress
 * @subpackage ChordPress/public/js
 */

/**
 * Hides an HTML parent element via CSS.
 *
 * This function hides the parent element of the given one by setting its
 * display style property to 'none'.
 *
 * @since       1.0.0
 *
 * @param object    $el   HTML object.
 */
function dismissParent(el) {

   el.parentNode.style.display='none';

};

/**
 * Transposes chords up
 *
 * This function hides all chord divs, then shows those for the given transpose
 * value.
 *
 * @since       1.3.0
 */
function transposeChords() {

   /**
    * Get value from Transpose listbox
    */
   var x = document.getElementById("selTranspose").value;

   /**
    * Hide all hidable chords
    */
   var divsToHide = document.getElementsByClassName("chord-hidable");

   for (var i = 0; i < divsToHide.length; i++) {

      divsToHide[i].style.display = "none";

   }

   /**
    * Now show those for the selected transpose value
    */
   var divsToShow = document.getElementsByClassName("key-"+x);

   for (var i = 0; i < divsToShow.length; i++) {

      divsToShow[i].style.display = "";

   }

}

/**
 * Print cpress div
 *
 * This function picks the cpress id element, creates a new html page from it
 * and launches an HTML print event.
 *
 * @since       1.4.0
 *
 * @param string    elem    Chordpress element ID
 */
function printCpress(elem) {

   var mywindow = window.open('', 'PRINT', 'height=400,width=600');
   mywindow.document.write('<html><head><title>' + document.title  + '</title>');
   mywindow.document.write('</head><body style=\"font-family:sans-serif;margin: 1.6cm;\">');
   mywindow.document.write('<style>@page { margin: 0; } div.cpress_interaction { display: none; }</style>');
   mywindow.document.write(document.getElementById(elem).innerHTML);
   mywindow.document.write('</body></html>');
   mywindow.document.close(); // necessary for IE >= 10
   mywindow.focus(); // necessary for IE >= 10*/
   mywindow.print();
   mywindow.close();
   return true;
   
}

/**
 * Modal Dialog
 *
 * This code block handles the guitar chord modal dialogs
 *
 * @since       1.4.0
 */
// var cpress_modal = document.querySelector(".cpress-modal");
// var cpress_trigger = document.querySelector(".cpress-trigger");
// var cpress_closeButton = document.querySelector(".cpress-close-button");

// function cpressToggleModal() {

//    cpress_modal.classList.toggle("cpress-show-modal");

// }

// function cpressWindowOnClick(event) {

//    if (event.target === cpress_modal) { cpressToggleModal(); }

// }

// cpress_trigger.addEventListener("click", cpressToggleModal);
// cpress_closeButton.addEventListener("click", cpressToggleModal);
// window.addEventListener("click", cpressWindowOnClick);