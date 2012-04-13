<?php

$feeditems = array();
$replying = FALSE;
$replyingto = "";
$returnto = "";

// we return to the default page if there is no set return page
if ( isset($_GET) && isset($_GET['returnto']) && Sections::isValid($_GET['returnto']) ) {
	$returnto = $_GET['returnto'];
} else {
	$returnto = $currentsection;
}

if (isset($_GET) && isset($_GET['reply'])) {
	if (strlen($_GET['reply']) == 36) {
		$replying = TRUE;
		$replyingto = $_GET['reply'];
	}
}

// fetch all inpackets of a certain type
$r = new GetInpackets($feedtype);
$guid = $auth->getUser()->guid;

$userdata = $udb->getUserStorage($guid);

$feed = $r->get($userdata);
$feeditems = array();
$ps = new PacketParser();

$replytemplate = $t->showTemplate(TEMPLATES_FEEDITEM_REPLY);

foreach($feed as $feeditem) {
	$xml = $feeditem['raw'];
	
	$parsed = $ps->parseText($xml);
	$packet = $parsed[0];
	
	// echo '<code>',htmlspecialchars($packet->toXMl()->ownerDocument->saveXML()),'</code><br><br>';
	$packet->compiled_template = $t->showTemplate($packet->feed_template);
	array_push($feeditems, $packet);
}

?>