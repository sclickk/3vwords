<?php

/**
 * Returns the index of the row just after the last filled row.
 */
function scrlim(&$scn)
{
  for ($n = 0; $n <= 25; $n++) {
    if (isset($scn[$n])) {
      $scrlim = $n + 1;
    }
  }
  return $scrlim;
}


$rendering_aborted = 0;
$exceeded_weight_limit = 0;

/**
 * Extracts filters from the syntax:
 */
/*function get_filters() {
  echo FRAGMENTS, FILTERS;
}
get_filters();*/

/**
 * Parses a list of slash-delimited options.
 * $c==0 (default) -- return one randomly selected of the options
 * $c>0            -- return all the options in an array
 */
function choose($string, $c = 0) {
  global $exceeded_weight_limit;
  $i = 0;
  $ti = 0;
  $options[0] = ""; // allocate the first option
  for ($p = 0; $p < strlen($string); $p++) { 
    $level = 0;
    $weight_str = "";
    // process the option's characters
    for($p; $p < strlen($string)
         && !($level == 0 && $string[$p] == '/'); $p++) {
      if($string[$p] == '"') { // escaped characters
        $options[$i] .= $string[$p];
        $p++;
        for ($p; $p < strlen($string); $p++) {
          $options[$i] .= $string[$p];
          if ($string[$p] == '"') {
            break;
          }
        }
      } elseif ($string[$p] == '*') { // weight specification
        $p++;
        for($p; $string[$p] >= '0'
             && $string[$p] <= '9'
             && $p < strlen($string); $p++) {
          $weight_str .= $string[$p];
        }
        $p--;
      } else {
        $options[$i] .= $string[$p];
        if ($string[$p] == '[' || $string[$p] == '(') $level++;
        if ($string[$p] == ']' || $string[$p] == ')') $level--;
      }
    }
    if ($weight_str == "") {
      $weight_str = "1";
    }

    $weight = (int) $weight_str;

    if ($weight < 1) {
      $weight = 1;
    }

    if ($weight > 128 && !$exceeded_weight_limit) {
      $exceeded_weight_limit = $weight;
      $weight = 128;
    }

    $stop = $ti + $weight;

    for ($ti; $ti < $stop; $ti++) {
      $target[$ti] = &$options[$i]; // insert references into the target for choosing
    }

    if ($string[$p] == '/') {  // there is a next option -> allocate it
      $options[$i + 1] = "";
      $i++;
    }
  }

  //print_r($options);
  //print_r($target);
  if ($c > 0) {
    return $options;
  } else {
    return $target[mt_rand(0, $ti - 1)];
  }
}

/**
 * Divides $string containing a pattern into fragments it has on its top level.
 * The fragments (substrings) are returned in an array.
 */
function fragments($string, &$scn, &$scc){
  $i = 0; $filti = 0;
  for($p=0; $p < strlen($string); $p++) {
    if($string[$p]>='A' && $string[$p]<='Z') { // a shortcut letter
      $scrlim = scrlim($scn);
      $f[$i][0] = "";
      for ($n = 0; $n < $scrlim; $n++) {
        if ($scn[$n] == $string[$p]) {
          $f[$i][0] = '['.$scc[$n].']';
        }
      }
      $i++;
      $filti = 0;
    } elseif($string[$p]=='^') { // a filter for currently open fragment
      $p++; $length = 0; $esc = false; // note: escaping works inside filters
      while($esc
         || ($string[$p + $length] != '[' && $string[$p + $length] != '('
          && !($string[$p + $length] >= 'A'
            && $string[$p + $length] <= 'Z')
          && $string[$p + $length] != '^'
          && ($p + $length) < strlen($string))) {
        if ($string[$p + $length] == '"') {
          $esc = 1 - $esc;
        }
        $length++;
      }
      if($length > 0) {
        $filter = fragments(substr($string, $p, $length), $p, $length);
        //echo "<br>$p $length substring: ".substr($string, $p, $length)."<br>";
        //echo "<br>$p $length filter: ".$filter[0][0]."<br>";
        //echo "::$i<br>";
        $f[$i-1][1+$filti] = $filter[0][0];
        $filti++;
        $p = $p + $length;
        if ($string[$p] == '^') {
          $p--;
        }
      }
    } else {
      // Brackets
      if($string[$p] == '[' || $string[$p] == '(') {
        $level = -1;
        do {
          if($string[$p] == '[' || $string[$p] == '(') $level++;
          if($string[$p] == ']' || $string[$p] == ')') $level--;
          $f[$i][0] .= $string[$p]; $p++;
        } while($level >= 0 && $p < strlen($string));
        $p--;
        $i++;
        $filti = 0;
      } else { // read characters
        for($p;
         $string[$p] != '[' && $string[$p] != '('
         && !($string[$p] >= 'A' && $string[$p] <= 'Z')
         && $string[$p] != '^' && $p < strlen($string);
         $p++) {
          // Escaping
          if ($string[$p] == '"') {
            $p++;
            if ($string[$p] == '"') {
              $f[$i][0] .= '"'; // "" -> insert single " in the fragment
            }
            // Read escaped characters
            for($p; $string[$p] != '"' && $p < strlen($string); $p++) {
              switch($string[$p]) {
                case '[': $f[$i][0] .= chr(1); break;  /* dummy characters for fragment-initial brackets */
                case '(': $f[$i][0] .= chr(2); break;  /* to get around their detection in render()      */
                default:  $f[$i][0] .= $string[$p];
              }
            }
          } else if ($string[$p] != ' ') {
            $f[$i][0] .= $string[$p]; // note: spaces don't interrupt the fragment
          }
        }
        $p--;
        if(isset($f[$i][0])) {
          $i++;        /* [space] in [fragment1][space][fragment2] would leave its fragment unset. */
          $filti = 0;  /* This test lets [fragment2] go right after [fragment1].                   */
        }
      }
    }
  }
  //echo "fragmented string: $string --> "; print_r($f); echo "<br>";
  return $f;
}

/**
 * Recursively goes through the given pattern $string.
 * $c==0 (default) -- return the number all possible renderings
 * otherwise       -- return a randomly generated string
 */
function render($string, &$scn, &$scc, $c = 0)
{
  global $rendering_aborted;
  if ($c > 0) { // count all possible renderings
    $options = choose($string, 1);
    $no = 0;
    for ($p = 0; isset($options[$p]); $p++) {
      $f = fragments($options[$p], $scn, $scc); $nf = 1;
      for($i=0; isset($f[$i][0]); $i++) {
        switch($f[$i][0][0]) {
          case '[': $nf *= render(substr($f[$i][0], 1, -1), $scn, $scc, $c); break;
          case '(': $nf *= render(substr($f[$i][0], 1, -1), $scn, $scc, $c) + 1;
        }
      }
      $no += $nf;
    }
    return $c*$no;
  } else { // render
    $f = fragments(choose($string), $scn, $scc);
    for($i=0; isset($f[$i][0]); $i++) {
      $fragr = "";
      switch($f[$i][0][0]) {
        case '[':
          $fragr = render(substr($f[$i][0], 1, -1), $scn, $scc);
          break;
        case '(':
          if(mt_rand(0, 1) == 1) {
            $fragr = render(substr($f[$i][0], 1, -1), $scn, $scc);
          }
          break;
        default:
          $fragr = $f[$i][0];
      }

      for($filti = 0; isset($f[$i][$filti + 1]); $filti++) { // filters of the fragment
        if ($fragr == $f[$i][$filti + 1]) {
          $rendering_aborted = 1;
        }
      }

      if ($rendering_aborted) {
        return false;
      }

      $r .= $fragr;
    }
    $uncover_brackets = chr(1) . chr(2);
    return strtr($r, $uncover_brackets, "[(");
  }
}

/****************************************/

switch($_POST["submit"]) {
  case "Save...": include "./save.php"; break;
  default:        include "./make.php";
}