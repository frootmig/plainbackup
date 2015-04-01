<?php
/* This function will ensure, that a path ends always with a slash. */
function fix_path_end($path) {
	if ($path != '') {
		if (substr($path, -1) != '/') {
			return "$path/";
		}
	}
	return $path;
}

/* This function is used to walk a directory tree. */
function walk_dirs($files, $backup_dirs, $path_prefix = '') {
	foreach ($backup_dirs as $bd) {
		if ($bd == '.' || $bd == '..') {
			continue;
		}
		$path_prefix = fix_path_end($path_prefix);
		$bd = "$path_prefix$bd";
		if (is_file($bd)) {
			array_push($files, $bd);
			continue;
		}
		if (!is_dir($bd)) {
			continue;
		}
		$sres = scandir($bd);
		if ($sres === false) {
			echo "Cannot scan $sres\n";
		}
		$files = walk_dirs($files, $sres, $bd);
	}
	return $files;
}

?>
