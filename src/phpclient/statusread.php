<?php
require_once('messagebuilder.php');

$recipients = array();
$known = $udb->getKnownNames();

if (isset($_POST[FORMS_STATUS])) {

	if (isset($_POST[FORMS_RECIPIENTS])) {
		$recipients = $_POST[FORMS_RECIPIENTS];
	}
	
	$contents = MessageBuilder::createMessage($known, $_POST[FORMS_STATUS]);
	
	echo "<br>",implode($contents),"</br>";
	$packet = new StatusUpdate(new Message($contents));

	var_dump($recipients);
	foreach ($recipients as $recipient) {
		// if the recipient the user sent is valid
		if (array_key_exists($recipient, $known)) {
			$guid = $known[$recipient][0];
			// add recipient to packet recipient list
			array_push($packet->recipients, $guid);
		}
	}
	$guid = $auth->getUser()->guid;
	$packet->from = $guid;
	$saver = new PacketSaver();
	$saver->save($packet);
	echo '<br/><textarea cols="50" rows="20">',htmlspecialchars($packet->toXML()->ownerDocument->saveHTML()),'</textarea><br/>';
	
}
?>