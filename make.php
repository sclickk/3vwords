<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Awkwords - word generator</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="content-language" content="en"/>
<link rel="stylesheet" href="assets/common.css" type="text/css" media="all"/>
<link rel="stylesheet" href="assets/default.css" type="text/css" media="all" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript" src="lib/main.js"></script>
</head>
<body>
<div class="heading">
<h1 class="h_left">Awkwords - word generator</h1>
<div class="h_right">
<a id="help" href="help.html" target="_blank">Help</a>
</div>
<!-- web stats [nv.cz]
<div id="counter">
<script src="http://c1.navrcholu.cz/code?site=85435;t=lb14"
type="text/javascript"></script><noscript><div><a
href="http://navrcholu.cz/"><img
src="http://c1.navrcholu.cz/hit?site=85435;t=lb14;ref=;jss=0"
width="14" height="14" alt="NAVRCHOLU.cz"
style="border:none" /></a></div></noscript>
</div>
-->
v1.1 <!-- :: Awkwords version :: -->
<div class="clear">&nbsp;</div>
</div>


<!--<div class="right_links"><img src="help_ico.gif" style="vertical-align: middle" />
<span class="jslink" onclick="window.open('help.html', 'Awkwords_help', 'scrollbars=yes, toolbar=no, location=no, directories=no, status=no, menubar=no, width=650, height=350')">How to use</span>
</div>-->
<?php
if(isset($_FILES['file'])) { // load settings from the file
  $f = FOpen($_FILES['file']['tmp_name'], "r");
  $n = 0;
  while($r = FGetS($f, 255)) {
    if(substr($r, 0, 3)=="nle") $nle = true;
    if(substr($r, 0, 9)=="filterdup") $filterdup = true;
    if(EReg("^n:.*", $r)) $numw = (int) substr($r, 2, -1);
    if(EReg("^r:.*", $r)) $pattern = substr($r, 2, -1);
    if(EReg("^[[:upper:]]?:.*", $r)) {
      $pos = StrPos($r, ":"); 
      if($pos==0) $scn[$n] = ""; else $scn[$n] = $r[0]; 
      $scc[$n] = substr($r, $pos+1, -1); $n++;
    }
  }
  FClose($f);
}
?>
<form action="index.php" method="post">
<div id="scsec"><b>shortcuts:</b>  <?php
if(isset($_POST['scn'])) $scn = $_POST['scn'];
if(isset($_POST['scc'])) $scc = $_POST['scc'];
if(!isset($scn) && !isset($scc)) {
  $scn[0] = "V"; $scc[0] = "a/i/u";
  $scn[1] = "C"; $scc[1] = "p/t/k/s/m/n";
  $scn[2] = "T"; $scc[2] = "p/t/k";
  $scn[3] = "F"; $scc[3] = "s";
  $scn[4] = "N"; $scc[4] = "m/n";
  $scrlim = 5;
} else {
  $scrlim = scrlim($scn);
}

/** @var $uppercase_letters The uppercase leters of the Latin alphabet. */
$uppercase_letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

for ($n = 0; $n <= 25; $n++) {
  echo "\n\n<div class=\"sc_row\" id=\"sc_row$n\""; 
  echo ($n > $scrlim ? " style=\"display: none\"" : "");
  echo "><select style=\"width:4em\" name=\"scn[$n]\" id=\"sc_select$n\""; 
  echo ($n == $scrlim ? " onchange=\"addscrow($n)\"" : "");
  echo ">";
  
  // The blank option
  echo "<option value=\"\"";
  echo ($scn[$n] == "" ? " selected" : "");
  echo ">-</option>\n";

  // Options of alphabet letters.
  for ($i = 0; $i < strlen($uppercase_letters); $i++) {
    $c = $uppercase_letters[$i];
    echo "<option value=\"$c\"";
    echo ($scn[$n] == $c ? " selected" : "");
    echo ">$c</option>\n"; 
  }
  echo "</select>";

  // The input for the pattern.
  echo "<input name=\"scc[]\" type=\"text\" value=\"" . htmlspecialchars($scc[$n])
  ."\" size=\"64\" id=\"sc_input$n\"></div>"; 
}
?> </div>
<div id="psec">
<?php

if (!isset($pattern)) {
  if (!isset($_POST['pattern'])) {
    $pattern = "CV(CV)(N)";
  } else {
    $pattern = $_POST['pattern'];
  }
}

?>
<label for="pattern"><b>pattern:</b> </label><input name="pattern" id="pattern" type="text" 
value="<?php echo htmlspecialchars($pattern); ?>" maxlenght="200" size="64"></div>
<div id="gensec">

<label for="numw">Number of words to generate: </label>
<?php 
if (!isset($numw)) {
  if ($_POST['numw'] > 9999) {
    $numw = 9999;
  } else {
    $numw = $_POST['numw'];
  }

  if(!isset($_POST['numw'])) {
    $numw = 100;
  }
}

echo '<input name="numw" id="numw" type="text" size="4" maxlength="4" value="' . $numw . '" />';

// New each line option

echo '<input name="nle" id="nle" class="checkbox" type="checkbox"';

$nle = null;

if (isset($_POST['nle'])) {
  $nle = $_POST['nle'];
}

if ($nle) {
  echo ' checked="checked"';
}

echo '/>';
echo "<label class=\"checkbox\" for=\"nle\">new line each</label>";

echo '<input name="filterdup" id="filterdup" class="checkbox" type="checkbox"'; 
if(isset($_POST['filterdup'])) $filterdup = $_POST['filterdup'];
if(isset($filterdup)) echo ' checked="checked"'; 
echo ' />'; echo "<label class=\"checkbox\" for=\"filterdup\">filter duplicates</label>";

?> 
<input name="submit" type="submit" value="Generate" style="margin-left: 30px" />
</div>
<div id="slsec">
<input name="submit" type="submit" value="Save..." />
<input type="button" id="loadsecinvoker" value="Open >" onclick="loadsec()" />
</div>
<input type="hidden" name="filename" value="<?php echo $_FILES['file']['name']; ?>" />
<div class="clear">&nbsp;</div>
</form>
<form id="loadsec" action="index.php" enctype="multipart/form-data" method="post">
<label for="file">file: </label><input name="file" id="file" type="file" />
<input name="submit" type="submit" value="Open" style="margin-left: 20px" />
</form>

<div id="wordsec">
<?php
$possibilities = render($pattern, $scn, $scc, 1);
if($exceeded_weight_limit) {
  echo "<div class=\"warning\">";
  echo "weight <i>".$exceeded_weight_limit."</i> is too high; reduced to the maximum weight <i>128</i>";
  echo "</div>";
}
?>
<input id="selectbutton" type="button" value="Select all" onclick="select_all()" />
<?php

/**
 * Return the noun inflected for a specific number.
 */
function number_form($noun, $n) {
  if ($n == 1) { // Singular
    return $noun;
  } else { // Plural
    return (substr($noun, -1) == "y"
         ? (substr($noun, 0, -1) . "ies")
         : $noun . "s");
  }
}

if ($pattern != "") {
  echo '<div id="words">';
  $ws = 0; // valid word counter
  $dups = 0; // duplicate counter
  $fabts = 0; // aborted rendering counter
  /** @var $start_time The microtime() at the start of rendering */
  $start_time = array_sum(explode(' ', microtime()));
  for($i = 1; $i <= $numw; $i++) {
    $word = render($pattern, $scn, $scc); // generate a word
    if ($rendering_aborted) {
      $fabts++;
      $rendering_aborted = 0;
    } elseif (!isset($filterdup) || !isset($generated_words[$word])) { 
      $ws++;
      $generated_words[$word] = true; 
      echo HTMLSpecialChars($word);
      echo ($nle ? "<br/>" : " ");
    } else {
      $dups++;
    }
  }
  /** @var $finish_time The microtime() at the end of rendering */
  $finish_time = array_sum(explode(' ', microtime()));
  echo "</div>";
  // Generate stats
  echo "<ul id=\"stats\">";
  echo "<li>".$ws." ".number_form("word", $ws); 
  if($dups || $fabts) {
    echo " (filtered out: ";
    if($dups) echo $dups." ".number_form("duplicate", $dups);
    if($dups && $fabts) echo ", ";
    if($fabts) echo $fabts." by pattern filters";
    echo ")";
  }
  echo "</li>";
  echo "<li>possibilities with this pattern: " . $possibilities . "</li>";
  /** @var $generated_time The time took the page to generate. */
  $generated_time = $finish_time - $start_time;
  echo "<li>generated in " . sprintf("%.3f", $generated_time) . " seconds</li>";
  echo "</ul>";
}
?>
</div>

</body>
</html>
