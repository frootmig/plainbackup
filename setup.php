<?php
$req_okay = true;

if (function_exists('ssh2_connect')) {
	echo "SSH2 PECL OK\n";
} else {
	$req_okay = false;
	echo "SSH2 PECL needs to be installed:
apt-get install libssh2-php
";
}

if (class_exists('SQLite3')) {
	echo "SQLite3 PHP extension OK\n";
} else {
	$req_okay = false;
	echo "PHP5 SQLite3 needs to be installed:
apt-get install php5-sqlite
";
}

if (!$req_okay) {
	echo "Requirements not okay\n";
	exit(1);
}

echo "Setup implementation is not ready yet, can just check what's missing\n";

?>
