/**
 * Add a pattern to the shortcuts list.
 */
function addscrow(n) {
  var s = $('#sc_select' + n);
  s.on('change', function () {});
  var nrow = $('#sc_row' + (n+1));
  nrow.css('display', 'block');
  var ns = $('#sc_select' + (n+1));
  ns.on('change', function () {
    addscrow(n+1);
  });
}

/**
 * The "Open >" button
 */
function loadsec() {
  $('#loadsec').css('display', 'block');
  $('#loadsecinvoker').css('visibility', 'hidden');
}

function select_all() {
  try {
    selection = window.getSelection();
    selection.removeAllRanges();
    range = document.createRange();
    range.selectNodeContents($('#words')[0]);
    selection.addRange(range);
  } catch(e) { // IE
    range = document.body.createTextRange();
    range.moveToElementText($('#words')[0]);
    range.select();
  }
}