<?php
if ($argc < 3) exit("Usage: ".$argv[0]." PATH HASH");
define('BASE', $argv[1]);
define('HASH', $argv[2]);

function git_print($hash) {
  return shell_exec('git cat-file -p '.$hash.' 2>/dev/null');
}

function git_type($hash) {
  return trim(shell_exec('git cat-file -t '.$hash.' 2>/dev/null'));
}

function git_restore($hash, $fn) {
  switch (git_type($hash)) {
    case 'tree':
      system('mkdir -p '.$fn);
      $ls = explode("\n", trim(git_print($hash)));
      foreach ($ls as $e) {
        $tmp = explode("\t", $e);
        git_restore(explode(" ", $tmp[0])[2], $fn.'/'.$tmp[1]);
      }    
      break;
    case 'blob':
      file_put_contents($fn, git_print($hash));
      break;
    default:
      return;
  }
  echo 'Restored: '.$fn."\n";
}

chdir(BASE);
$type = git_type(HASH);
if ($type != 'commit') exit("Invalid commit hash\n");

$commit = git_print(HASH);
$tree_hash = substr($commit, strpos($commit, 'tree ') + 5, 40);

git_restore($tree_hash, '.');