<?php
/* Which local directory should be used for creating the backup files. */
define('LOCAL_TMP_PREFIX', '');
/* Hostname for the (scp/sftp upload). Currently no other methods are supported. */
define('REMOTE_HOST', '');
/* Remote user for upload. */
define('REMOTE_USER', '');
/* Remote password that has to be used for the upload. */
define('REMOTE_PASSWORD', '');
/* The remote base path used for upload. */
define('REMOTE_BASE_PATH', '/');
/** Which directories should be backed up? */
$backup_dirs = array(
'/'
);
$exclude_dirs = array(
'/tmp/',
'/proc/',
'/sys/',
'/var/tmp/',
'/var/run/'
);
/* In this SQLite database the hashes are store to identify changes for incemental backups. */
define(DB_FILENAME, 'backup.db');
/* This filename is passed to tar command and will contain current increment files. */
define(BACKUP_LIST, 'backup.lst');
?>
