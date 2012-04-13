<?php


require_once('standards.php');
require_once('core.php');

login_required();


$core = new Core($serverdata, $udb, INPACKETS_DIR, OUTPACKETS_DIR);
$core->processFolder();

?>