<?php

/**
 *
 * Helperfunctions
 */

/**
 * stolen from  promaty at gmail dot com ¶
 * via https://www.php.net/manual/en/function.copy.php
 *   
 * */
// removes files and non-empty directories
function rrmdir($dir) {
  if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file)
      if ($file != "." && $file != "..")
        rrmdir("$dir/$file");
    rmdir($dir);
  }
  else if (file_exists($dir))
    unlink($dir);
}

// copies files and non-empty directories
function rcopy($src, $dst) {
  if (file_exists($dst))
    rrmdir($dst);
  if (is_dir($src)) {
    mkdir($dst, 0755, true);
    $files = scandir($src);
    foreach ($files as $file)
      if ($file != "." && $file != "..")
        rcopy("$src/$file", "$dst/$file");
  }
  else if (file_exists($src))
    copy($src, $dst);
  //haha.
  return true;
}
