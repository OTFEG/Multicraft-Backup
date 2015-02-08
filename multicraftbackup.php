#!/usr/bin/php
<?php
$url = "http://localhost/panel/api.php";
$user = "admin";
$key = "5a8a239d470f35d58c37";
$serverdir = '/home/jimmy/multicraft/servers/';
$backupbasedir = '/home/jimmy/backups';
$timezone = 'Pacific/Auckland';
$adminemail = "jimmy1248@gmail.com";
$maxbackups = 5;
$multicraftapi = '/home/jimmy/multicraft/api';

//do not edit bellow here
date_default_timezone_set($timezone);
require $multicraftapi.'/MulticraftAPI.php';

switch ($argc) {
	case 3:
		$maxbackups = $argv[2];
	case 2:
		$serverid = $argv[1];
		break;
	default:
		exit("Please Specify the server id.\n");
		break;
}
$api = new MulticraftAPI($url,$user,$key);
$server = $api->getServer($serverid);
if(!$server["success"]) {
	exit("Failed to get server " . $serverid . ". " . $server["errors"][0] . "\n");
} 
$servername = $server['data']['Server']['dir'];
$date = date('Y-m-d_H-i-s');
$backupdir = $backupbasedir . '/' . $servername;
$archive = $backupdir . '/' . $servername . '-' . $date . ".tar";
if(!file_exists($backupdir)){
	mkdir($backupdir, 0777, true);
	echo "Making $backupdir";
}
//put server into read only mode
$api->sendConsoleCommand($serverid,"save-off");
 echo "Server in read only mode.\n";

//backup server
echo "Backing up $servername.\n";
$time = microtime(true);
try {
	$fp = new PharData($archive);
	$fp->buildFromDirectory($serverdir, '/' . $servername . '/');

	//put server into read-write mode
	$api->sendConsoleCommand($serverid,"save-on");
	echo "Server in read-write mode.\n";

	//compress server
	echo "Compressing server.\n";
	$compressed = $fp->compress(Phar::BZ2); 
	printf("Server backed up to %s.bz2 in %.1f seconds.\n", $archive, microtime(true) - $time);
	echo $compressed . "\n";
} catch (UnexpectedValueException $e) {
	die('Could not open ' . $archive);
} catch (BadMethodCallException $e) {
    die($e -> g);
}

unset($fp);
//remove archive
Phar::unlinkArchive($archive);

$files = glob($backupdir . '/*.tar.bz2');
for ($i = 0; $i < count($files) - $maxbackups; $i++){
	Phar::unlinkArchive($files[$i]);
}
mail($adminemail,'Multicraft Backup '.$servername,"");
function error($error){
	
}
?>
