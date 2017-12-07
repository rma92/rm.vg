<?php
$dir = '.';

function dirlist($dir)
{
  $f = scandir($dir);
  foreach($f as $ff) 
  {
    echo $ff, '<br/>';
  }
}

dirlist($dir);
?>
