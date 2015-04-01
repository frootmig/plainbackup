<?php
// Include configuration
require_once('config.php');
// Include helper functions
require_once('lib/functions.php');
// Get us the current date
$today = date('Ymd');
// Initialize SQLite
$db = new SQLite3(DB_FILENAME);
// Create the table that holds our runtime information (more than one increment per day is supported by this table)
$db->exec("CREATE TABLE IF NOT EXISTS runinfo(
   date TEXT UNIQUE NOT NULL,
   value INTEGER NOT NULL
)");
// Check if there has already been a run today
$sel_stmt = $db->prepare('SELECT value FROM runinfo WHERE date = :date');
if (!$sel_stmt) {
	die("Could not prepare select date runinfo\n");
}
$sel_stmt->bindValue(':date', $today);
$result = $sel_stmt->execute();
if (!$result) {
	die("Could not select value FROM runinfo\n");
}
$count = 1;
if ($persitent = $result->fetchArray(SQLITE3_NUM)) {
	$count = $persitent[0]+1;
}
$stmt = $db->prepare("INSERT OR REPLACE INTO runinfo (date, value) VALUES (:date, :value) ");
if (!$stmt) {
	die("Could not prepare runinfo\n");
}
$stmt->bindValue(':date', $today);
$stmt->bindValue(':value', $count);
$stmt->execute();

$hostname = gethostname();
echo "plainbackup running on $hostname on ".date(DATE_RFC822)."\n";

$archive_prefix = LOCAL_TMP_PREFIX."$today-$count";

$files = array();

$files = walk_dirs($files, $backup_dirs);

$db-> exec("CREATE TABLE IF NOT EXISTS file_to_hash(
   file TEXT UNIQUE NOT NULL,
   hash TEXT NOT NULL
)");

$filelist = fopen(BACKUP_LIST, "w") or die("Unable to open file!");

$new_files = 0;
$changed_files = 0;
$unchanged_files = 0;

foreach ($files as $f) {
	$hash = md5_file($f);
	$sel_stmt = $db->prepare('SELECT hash FROM file_to_hash WHERE file = :filename');
	if (!$sel_stmt) {
		die("Could not prepare hash select for $f\n");
	}
	$sel_stmt->bindValue(':filename', $f);
	$result = $sel_stmt->execute();
	if (!$result) {
		die("Could not select hash for $f\n");
	}
	if ($persitent = $result->fetchArray(SQLITE3_NUM)) {
		if ($persitent[0] == $hash) {
			//echo "$f not changed\n";
			$unchanged_files++;
			continue;
		} else {
			$changed_files++;
		}
	} else {
		$new_files++;
	}
	$stmt = $db->prepare("INSERT OR REPLACE INTO file_to_hash (file, hash) VALUES (:filename, :hashvalue) ");
	if (!$stmt) {
		die("Could not prepare file_to_hash\n");
	}
	$stmt->bindValue(':filename', $f);
	$stmt->bindValue(':hashvalue', $hash);
	$result = $stmt->execute();
	fwrite($filelist, "$f\n");
}
$targz = "$archive_prefix.tar.gz";
echo "Creating $targz\n";
exec('tar --ignore-failed-read --numeric-owner -czpf '.escapeshellarg($targz).' --files-from='.escapeshellarg(BACKUP_LIST), $output, $return_var);

if ($return_var == 0) {
	echo "Files packed\n";
} else {
	die("tar command failed: $return_var\n");
}

$targzgpg = "$targz.gpg";
exec('gpg --passphrase-file secret.txt --symmetric --cipher-algo AES256 -o '.escapeshellarg($targzgpg).' '.escapeshellarg($targz));

echo "Connecting to ".REMOTE_HOST."\n";
$connection = ssh2_connect(REMOTE_HOST, 22);
if (!$connection) {
	die("Could not connect to ".REMOTE_HOST);
}
echo "Authenticate as user ".REMOTE_USER."\n";
if (!ssh2_auth_password($connection, REMOTE_USER, REMOTE_PASSWORD)) {
	die("Could not authenticate as user ".REMOTE_USER);
}
$sftp = ssh2_sftp($connection);
$remote_path = fix_path_end(REMOTE_BASE_PATH).$hostname;
echo "Ensure remote $remote_path exists\n";
ssh2_sftp_mkdir($sftp, $remote_path, 0770, true);
$remote_file = $remote_path.'/'.$targzgpg;
if (!ssh2_scp_send($connection, $targzgpg, $remote_file)) {
	die("Could not upload $targzgpg\n");
}

echo "Statistic:

New files: $new_files
Changed files: $changed_files
Not modifed files: $unchanged_files
";

?>
