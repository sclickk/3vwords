<?php

$filename = $_POST['filename'];
if ($filename == '') {
  $filename = 'awkwords-settings.awk';
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$scn = $_POST['scn']; $scc = $_POST['scc'];
$scrlim = scrlim($scn);

// Shortcuts
for ($n = 0; $n < $scrlim; $n++) {
  echo $scn[$n] . ':' . $scc[$n] . "\n";
}

// Pattern
echo 'r:' . $_POST['pattern'] . "\n";
// Number of words to generate
echo 'n:' . $_POST['numw'] . "\n";
// New line each
echo ($_POST['nle'] ? "nle\n" : '');
// Filter duplicates
echo ($_POST['filterdup'] ? "filterdup\n" : '');