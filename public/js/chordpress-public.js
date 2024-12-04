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
 * ----------------------------------------------------------------------------
 * Dismiss Parent.
 * ----------------------------------------------------------------------------
 *
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

  el.parentNode.style.display = 'none';

};

/**
 * ----------------------------------------------------------------------------
 * Transposes Chords.
 * ----------------------------------------------------------------------------
 *
 * This function hides all chord divs, then shows those for the given transpose
 * value.
 *
 * @since 1.3.0
 */
function transposeChords(cpressId, formControlId, direction, baseKey) {

  const arrTransposeValues = {
    'A': ['A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#'],
    'Am': ['Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m'],
    'A#': ['A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A'],
    'Bb': ['Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A'],
    'A#m': ['A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am'],
    'Bbm': ['Bbm', 'Bm', 'Cm', 'Dbm', 'Dm', 'Ebm', 'Em', 'Fm', 'Gbm', 'Gm', 'Abm', 'Am'],
    'B': ['B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#'],
    'Bm': ['Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m'],
    'C': ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'],
    'Cm': ['Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm'],
    'C#': ['C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C'],
    'Db': ['Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B', 'C'],
    'C#m': ['C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm'],
    'Dbm': ['Dbm', 'Dm', 'Ebm', 'Em', 'Fm', 'Gbm', 'Gm', 'Abm', 'Am', 'Bbm', 'Bm', 'Cm'],
    'D': ['D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#'],
    'Dm': ['Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m'],
    'D#': ['D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D'],
    'Eb': ['Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B', 'C', 'Db', 'D'],
    'D#m': ['D#m', 'Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm'],
    'Ebm': ['Ebm', 'Em', 'Fm', 'Gbm', 'Gm', 'Abm', 'Am', 'Bbm', 'Bm', 'Cm', 'Dbm', 'Dm'],
    'E': ['E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#'],
    'Em': ['Em', 'Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m'],
    'F': ['F', 'F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E'],
    'Fm': ['Fm', 'F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em'],
    'F#': ['F#', 'G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F'],
    'Gb': ['Gb', 'G', 'Ab', 'A', 'Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F'],
    'F#m': ['F#m', 'Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm'],
    'Gbm': ['Gbm', 'Gm', 'Abm', 'Am', 'Bbm', 'Bm', 'Cm', 'Dbm', 'Dm', 'Ebm', 'Em', 'Fm'],
    'G': ['G', 'G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#'],
    'Gm': ['Gm', 'G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m'],
    'G#': ['G#', 'A', 'A#', 'B', 'C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G'],
    'Ab': ['Ab', 'A', 'Bb', 'B', 'C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G'],
    'G#m': ['G#m', 'Am', 'A#m', 'Bm', 'Cm', 'C#m', 'Dm', 'D#m', 'Em', 'Fm', 'F#m', 'Gm'],
    'Abm': ['Abm', 'Am', 'Bbm', 'Bm', 'Cm', 'Dbm', 'Dm', 'Ebm', 'Em', 'Fm', 'Gbm', 'Gm']
  };

  let transposeElementId = "selTranspose-" + cpressId;
  if (formControlId) {
    transposeElementId = formControlId + cpressId;
  }
  // console.log(">>> " + transposeElementId);

  /**
   * Get value from transpose form element
   */
  var x = document.getElementById(transposeElementId).value;
  if (direction) {
    if (direction === 'up') {
      if (x < 11) {
        x++;
      } else {
        x = 0;
      }
    } else if (direction === 'down') {
      if (x > 0) {
        x--;
      } else {
        x = 0;
      }
    } else if (direction === 'reset') {
      x = 0;
    }
  }

  /**
   * Hide all hideable chords
   */
  var divsToHide = document.getElementsByClassName("chord-hidable-" + cpressId);
  for (var i = 0; i < divsToHide.length; i++) {
    divsToHide[i].style.display = "none";
  }

  /**
   * Now show those for the selected transpose value
   */
  var divsToShow = document.getElementsByClassName("key-" + cpressId + '-' + x);
  for (var i = 0; i < divsToShow.length; i++) {
    divsToShow[i].style.display = "";
  }

  /**
   * Change value and text of the transpose button
   */
  if (formControlId === 'btnTranspose-') {
    var btn = document.getElementById(transposeElementId);
    btn.value = x;
    if (baseKey !== '') {
      btn.innerText = x + ' (' + arrTransposeValues[baseKey][x] + ')';
    } else {
      btn.innerText = x;
    }
  }
}

/**
 * ----------------------------------------------------------------------------
 * Change Font Size.
 * ----------------------------------------------------------------------------
 *
 * Increases/decreases the font size of an element. Usage:
 * - changeFontSize('myElement', 2);
 * - changeFontSize('myElement', -2);
 *
 * @since 5.6.0
 */
function changeFontSize(elementId, amount) {
  const element = document.getElementById(elementId);
  const sizeButton = document.getElementById('btnFontSize-' + elementId);
  let currentFontSize = parseFloat(element.style.fontSize);
  if (isNaN(currentFontSize)) {
    currentFontSize = 100; // Default font size
  }
  if (amount === 0) {
    element.style.fontSize = '100%';
    sizeButton.innerText = '100%';
    return;
  }
  const newFontSize = currentFontSize + amount;
  if (newFontSize >= 0) {
    element.style.fontSize = newFontSize + '%';
    sizeButton.innerText = newFontSize + '%';
  }
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
