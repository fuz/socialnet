<?php

include('templating.php');
error_reporting(E_ALL);

$t = new Templating();
$title = "Social Network Template";

$pagename = $t->showTemplate(TEMPLATE_FEED);

include($t->showTemplate('layout'));



?>