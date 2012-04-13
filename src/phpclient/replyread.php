<?php
include('standards.php');
login_required();
var_dump($_POST);
if (needed($_POST, array('reply', 'commented', 'comment', 'returnto')) ) {
	require_once('messagebuilder.php');
	
	
	$returnto = $_POST['returnto'];
	echo $returnto;
	
	if (!Sections::isValid($returnto)) {
		redirect_to(SECTION_FEED);
	}

	$comment = $_POST['comment'];
	$replyingto = $_POST['reply'];
	$known = $udb->getKnownNames();

	$contents = MessageBuilder::createMessage($known, $comment);
	$packet = new Comment($replyingto, $contents);
	array_push($packet->recipients, $replyingto);
	
	echo $comment,$replyingto,$returnto;

}
?>