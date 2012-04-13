<?php

require_once('core.php');
require_once('engine.php');

// Deliver pending packets
$server = new Server(new Accounts(new Storage($serverinstall), $userinstall));
$server->deliverPackets(OUTPACKETS_DIR);


$core = new Core($serverdata, $udb, INPACKETS_DIR, OUTPACKETS_DIR);

// fetch remote packets
$core->checkForRemotePackets($auth->getUser()->guid);

// decrypt waiting packets
$udb->processWaiting($auth->getUser()->guid, $_SESSION[SESSION_PASSWORD]);

// handle recently arrived packets

$core->processFolder();

?>