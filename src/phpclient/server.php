<?php

define('SOCIALNET_LOADED', TRUE);
require_once('static.php');
require_once('storage.php');
require_once('accountlayer.php');
require_once('core.php');
/**
A basic HTTP-PUT server for receiving PACKETS.
Saves them to the inpackets folder.
*/

if ($_SERVER["REQUEST_METHOD"] != "POST") {
	echo "Use post";
?>

<form enctype="multipart/form-data" action="server.php" method="POST">
	<input name="packets[]" type="file" />
	<input type="submit" value="Send File" />
</form>

<?
	exit;
}

if (!isset($_FILES["packets"]) || !isset($_FILES["packets"]["name"][0] )) {
	echo "You must send a at least one file named packets[]";
} else {

// Initialize the server storage layer
$serverdata = new Storage($serverinstall);
// Initialize Account Layer
$udb = new Accounts($serverdata, $userinstall);

$core = new Core($serverdata, $udb, INPACKETS_DIR, OUTPACKETS_DIR);


// A list of files that have been received.
$received = array();
	// $key is numerical index
	foreach($_FILES["packets"]["error"] as $key => $error) {
		if ($error == UPLOAD_ERR_OK) {
			$tmp = $_FILES["packets"]["tmp_name"][$key];
			$name = $_FILES["packets"]["name"][$key];
			$received[$name] = $tmp;
			
		}
	
	}

$core->handleUploadedFiles($received);

}

?>