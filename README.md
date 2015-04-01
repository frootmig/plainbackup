# plainbackup

Plainbackup is a script for server backups written in PHP, that makes use of standard Linux tools.

## Another script for server backups?

Yes. :-) I tested some scripts, but they did not fit my needs.

## Goals

* Modular design should make it easier to exchange specific parts of the script (e.g. file upload, encyption parts, etc.)
* Allow incremental backups
* Secure upload
* Secure encrypted storage
* Setup tool, no need to write a configuration file
