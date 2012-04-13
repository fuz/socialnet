<?php
require_once('standards.php');
login_required();

if (!needed($_GET, array('reply', 'page', 'returnto')) ) {
	redirect_to(SECTION_FEED);
}



$reply = $_GET['reply'];
$page = $_GET['page'];
$return = $_GET['returnto'];

if ( !Sections::isValid($page) || !Sections::isValid($return) ) {
	redirect_to(SECTION_FEED);
}

var_dump($reply);
var_dump($page);
var_dump($return);

redirect_then($page, $return, array('reply' => $reply));

?>