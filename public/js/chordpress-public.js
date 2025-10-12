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
        x = 11;
      }
    } else if (direction === 'reset') {
      x = 0;
    }
  }

  /**
   * Hide all hidable chords
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

  /**
   * Regenerate chord sheet for the new transpose level
   */
  regenerateChordSheet(cpressId, x);
}

/**
 * ----------------------------------------------------------------------------
 * Regenerate Chord Sheet.
 * ----------------------------------------------------------------------------
 *
 * This function regenerates the chord sheet with chords for the specified
 * transpose level to avoid display issues with transposed chord diagrams.
 *
 * @since 3.9.9
 */
function regenerateChordSheet(cpressId, transposeLevel) {
  console.log('Regenerating chord sheet for ID:', cpressId, 'transpose level:', transposeLevel);
  
  // Convert hyphens to underscores for variable name compatibility
  const safeId = cpressId.replace(/-/g, '_');
  const chordDataVar = 'chordSheetData_' + safeId;
  
  const chordSheetElement = document.getElementById('chordsheet-content-' + cpressId);
  
  if (!chordSheetElement) {
    console.error('Chord sheet element not found for ID:', cpressId);
    return;
  }
  
  if (!window[chordDataVar]) {
    console.error('No chord data found for ID:', cpressId);
    return;
  }
  
  const chordData = window[chordDataVar];
  console.log('Available chord data:', Object.keys(chordData));
  
  // Clear current chord sheet content
  chordSheetElement.innerHTML = '';
  
  // Generate chord diagrams for the specified transpose level
  let chordsDisplayed = 0;
  for (const originalChord in chordData) {
    const chordVariations = chordData[originalChord];
    let chordHtml = '';
    
    if (transposeLevel === 0) {
      // Always show original chords for transpose level 0
      chordHtml = chordVariations[0];
      console.log(`Showing original chord: ${originalChord}`);
    } else if (chordVariations[transposeLevel]) {
      // Only show transposed chord if it exists
      chordHtml = chordVariations[transposeLevel];
      console.log(`Showing transposed chord for ${originalChord} at level ${transposeLevel}`);
    } else {
      // Skip this chord if transposed version doesn't exist
      console.log(`Skipping chord ${originalChord} - no transpose level ${transposeLevel} available`);
      continue;
    }
    
    if (chordHtml) {
      // Create a unique container for each chord
      const chordContainer = document.createElement('div');
      chordContainer.className = 'chord-container-' + originalChord.replace(/[^a-zA-Z0-9]/g, '');
      chordContainer.innerHTML = chordHtml;
      
      // Append the chord diagram to the chord sheet
      chordSheetElement.appendChild(chordContainer);
      chordsDisplayed++;
      
      // Execute any scripts in the chord HTML - do this after DOM insertion
      const scripts = chordContainer.getElementsByTagName('script');
      
      for (let i = 0; i < scripts.length; i++) {
        try {
          const scriptContent = scripts[i].innerHTML;
          if (scriptContent.trim()) {
            // Use Function constructor instead of eval for better security
            const func = new Function(scriptContent);
            func();
          }
        } catch (e) {
          console.error(`Error executing script for chord ${originalChord}:`, e);
        }
      }
    }
  }
  
  console.log(`Chord sheet regenerated with ${chordsDisplayed} diagrams for transpose level: ${transposeLevel}`);
}

/**
 * ----------------------------------------------------------------------------
 * Transposes Chromatic.
 * ----------------------------------------------------------------------------
 *
 * This function hides all chromatic note divs, then shows those for the given
 * transpose value.
 *
 * @since 3.8.3
 */
function transposeChromatic(cpressId, formControlId, direction, baseNote) {

  const arrTransposeValues = {
    '1': ['1', '1s', '-1', '-1s', '2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4'],
    '1s': ['1s', '-1', '-1s', '2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4'],
    '-1': ['-1', '-1s', '2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s'],
    '-1s': ['-1s', '2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s', '-5'],
    '2': ['2', '-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s'],
    '-2': ['-2', '-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6'],
    '-2s': ['-2s', '3', '3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6'],
    '3': ['3', '3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s'],
    '3s': ['3s', '-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s', '7'],
    '-3': ['-3', '-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s'],
    '-3s': ['-3s', '-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7'],
    '-4': ['-4', '4', '4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s'],
    '4': ['4', '4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8'],
    '4s': ['4s', '-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8'],
    '-5': ['-5', '-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8', '9'],
    '-5s': ['-5s', '6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8', '9', '9s'],
    '6': ['6', '-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8', '9', '9s', '-9'],
    '-6': ['-6', '-6s', '7', '7s', '-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s'],
    '-6s': ['-6s', '7', '7s', '-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s', '10'],
    '7': ['7', '7s', '-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s', '10', '-10'],
    '7s': ['7s', '-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s', '10', '-10', '-10s'],
    '-7': ['-7', '-7s', '-8', '8', '9', '9s', '-9', '-9s', '10', '-10', '-10s', '11'],
    '-7s': ['-7s', '-8', '8', '9', '9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s'],
    '-8': ['-8', '8', '9', '9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11'],
    '8': ['8', '9', '9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s'],
    '9': ['9', '9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12'],
    '9s': ['9s', '-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12'],
    '-9': ['-9', '-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s'],
    '-9s': ['-9s', '10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s', ' '],
    '10': ['10', '-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s', ' ', ' '],
    '-10': ['-10', '-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s', ' ', ' ', ' '],
    '-10s': ['-10s', '11', '11s', '-11', '-11s', '-12', '12', '12s', ' ', ' ', ' ', ' '],
    '11': ['11', '11s', '-11', '-11s', '-12', '12', '12s', ' ', ' ', ' ', ' ', ' '],
    '11s': ['11s', '-11', '-11s', '-12', '12', '12s', ' ', ' ', ' ', ' ', ' ', ' '],
    '-11': ['-11', '-11s', '-12', '12', '12s', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
    '-11s': ['-11s', '-12', '12', '12s', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
    '-12': ['-12', '12', '12s', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
    '12': ['12', '12s', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
    '12s': ['12s', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
  };

  let transposeElementId = "selTranspose-" + cpressId;
  if (formControlId) {
    transposeElementId = formControlId + cpressId;
  }
  // console.log(">>> transposeElementId: " + transposeElementId);

  /**
   * Get value from transpose form element
   */
  var x = document.getElementById(transposeElementId).value;
  // console.log(">>> direction: " + direction);
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
        x = 11;
      }
    } else if (direction === 'reset') {
      x = 0;
    }
  }

  /**
   * Hide all hidable chords
   */
  var divsToHide = document.getElementsByClassName("chord-hidable-" + cpressId);
  // console.log(">>> divsToHide" + divsToHide);
  for (var i = 0; i < divsToHide.length; i++) {
    divsToHide[i].style.display = "none";
  }

  /**
   * Now show those for the selected transpose value
   */
  var divsToShow = document.getElementsByClassName("key-" + cpressId + '-chromatic-' + x);
  if (x === 0) {
    divsToShow = document.getElementsByClassName("key-" + cpressId + '-chromatic');
  }
  // console.log(">>> divsToShow[0]" + divsToShow[0]);

  for (var j = 0; j < divsToShow.length; j++) {
    divsToShow[j].style.display = "";
  }

  /**
   * Change value and text of the transpose button
   */
  if (formControlId === 'btnTranspose-') {
    var btn = document.getElementById(transposeElementId);
    btn.value = x;
    if (baseNote !== '') {
      btn.innerText = x + ' (' + arrTransposeValues[baseNote][x] + ')';
    } else {
      btn.innerText = x;
    }
  }
}

/**
 * ----------------------------------------------------------------------------
 * Transposes Harmonica.
 * ----------------------------------------------------------------------------
 *
 * This function hides all chord divs, then shows those for the given harmonica
 * type.
 *
 * @since 3.8.0
 */
function transposeHarmonica(cpressId, formControlId, harmonicaType) {

  /**
   * Hide/show the transpose button if harmonica type id no chromatic.
   */
  const transposeButton = document.getElementById('btnTranspose-chromatic');
  if (harmonicaType === 'chromatic') {
    transposeButton.style.display = "";
  } else {
    transposeButton.style.display = "none";
  }

  /**
   * Hide all hidable chords
   */
  var divsToHide = document.getElementsByClassName("chord-hidable-" + cpressId);
  for (var i = 0; i < divsToHide.length; i++) {
    divsToHide[i].style.display = "none";
  }

  /**
   * Now show those for the selected harmonica type
   */
  var divsToShow = document.getElementsByClassName("key-" + cpressId + '-' + harmonicaType);
  for (var j = 0; j < divsToShow.length; j++) {
    divsToShow[j].style.display = "";
  }
}

/**
 * ----------------------------------------------------------------------------
 * Change Font Size.
 * ----------------------------------------------------------------------------
 *
 * Changes the font size of a specified HTML element and adjusts the font size
 * of all heading tags (`h1` to `h5`) within that element proportionally.
 *
 * This function allows increasing, decreasing, or resetting the font size of
 * an element. It also ensures that heading tags inside the element scale
 * proportionally based on their original font size.
 *
 * @param {string} elementId - The ID of the HTML element whose font size will be changed.
 * @param {number} amount - The amount to adjust the font size by:
 *                          - Positive values increase the font size.
 *                          - Negative values decrease the font size.
 *                          - A value of 0 resets the font size to 100%.
 *
 * @since 3.6.0
 */
function changeFontSize(elementId, amount) {
  // Get the target element by its ID
  const element = document.getElementById(elementId);

  // Get the button associated with the font size control
  const sizeButton = document.getElementById('btnFontSize-' + elementId);

  // Parse the current font size of the element, defaulting to 100% if not set
  let currentFontSize = parseFloat(element.style.fontSize);
  if (isNaN(currentFontSize)) {
    currentFontSize = 100; // Default font size
  }

  // Calculate the new font size
  let newFontSize;
  if (amount === 0) {
    newFontSize = 100; // Reset to default size
  } else {
    newFontSize = currentFontSize + amount;
    if (newFontSize < 0) return; // Prevent negative font sizes
  }

  // Apply the new font size to the element
  element.style.setProperty('font-size', newFontSize + '%', 'important');

  // Calculate the scaling factor relative to the default size (100%)
  const scale = newFontSize / 100;

  // Adjust the font size of all heading tags (`h1` to `h5`) within the element
  ['h1', 'h2', 'h3', 'h4', 'h5'].forEach(tag => {
    const headings = element.getElementsByTagName(tag);
    for (let i = 0; i < headings.length; i++) {
      // Store the original font size in a data attribute if not already stored
      if (!headings[i].dataset.baseFontSize) {
        headings[i].dataset.baseFontSize = window.getComputedStyle(headings[i]).fontSize;
      }

      // Calculate the new font size based on the original size and scaling factor
      const baseSize = parseFloat(headings[i].dataset.baseFontSize);
      const newSize = baseSize * scale;

      // Apply the new font size to the heading
      headings[i].style.setProperty('font-size', newSize + 'px', 'important');
    }
  });

  // Scale specified classes
  ['cpress_verse', 'cpress_chorus', 'cpress_line', 'cpress_comment', 'lyric'].forEach(className => {
    const elems = element.getElementsByClassName(className);
    for (let i = 0; i < elems.length; i++) {
      if (!elems[i].dataset.baseFontSize) {
        elems[i].dataset.baseFontSize = window.getComputedStyle(elems[i]).fontSize;
      }
      const baseSize = parseFloat(elems[i].dataset.baseFontSize);
      const newSize = baseSize * scale;
      elems[i].style.setProperty('font-size', newSize + 'px', 'important');
    }
  });

  // Update the button text to reflect the new font size
  sizeButton.innerText = newFontSize + '%';
}

/**
 * ----------------------------------------------------------------------------
 * Print Element.
 * ----------------------------------------------------------------------------
 * Prints the content of a specified HTML element in a new browser window.
 *
 * This function retrieves the inner HTML of the specified element by its ID,
 * opens a new browser window, and writes the content into it. The new window
 * is styled with basic CSS and given a title, which can be customized via the
 * `printTitle` parameter. The function also ensures compatibility with older
 * versions of Internet Explorer by closing and focusing the print window.
 *
 * @param {string} elementId - The ID of the HTML element whose content will be printed.
 * @param {string} [printTitle='ChordPress Song'] - The title of the print window. Defaults to 'ChordPress Song'.
 *
 * @since 3.9.2
 */
function printElement(elementId, printTitle = 'ChordPress Song', cpressStyles = '') {
  const content = document.getElementById(elementId).innerHTML;
  const printWindow = window.open('', '', 'height=600,width=800');
  printWindow.document.write(`
    <html>
      <head>
        <title>${printTitle}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
        </style>
        ${cpressStyles}
      </head>
      <body>
        ${content}
      </body>
    </html>
  `);
  printWindow.document.close(); // Necessary for IE >= 10
  printWindow.focus();          // Necessary for IE >= 10
  printWindow.print();
  printWindow.close();
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
