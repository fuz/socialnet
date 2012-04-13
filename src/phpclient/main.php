<?php
require_once('standards.php');
login_required();
require_once('parser.php');
require_once('templating.php');
require_once('refresh.php');

$t = new Templating();

$feedtype = INPACKETS_FEED;
$currentsection = SECTION_FEED;


require_once('loadfeed.php');
require_once('statusread.php');


$mainpage = array(
	$t->showTemplate(TEMPLATES_STATUSUPDATE),
	$t->showTemplate(TEMPLATES_FEED)
);

include($t->showTemplate(TEMPLATES_LAYOUT));

?>