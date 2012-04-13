<?php

/**
Generates a status update packet.
*/

require_once('data.php');
require_once('standards.php');
require_once('packetsaver.php');

login_required();
require_once('refresh.php');
require_once('templating.php');
$title = "Update my status";
$t = new Templating();

$out = "";
$feedtype = INPACKETS_FEED;
require_once('loadfeed.php');
require_once('statusread.php');

$mainpage = array(
	$t->showTemplate(TEMPLATES_STATUSUPDATE),
	$t->showTemplate(TEMPLATES_FEED)
);

include($t->showTemplate(TEMPLATES_LAYOUT));
?>


<form method="POST">
	<select size="5" name="<?php echo FORMS_RECIPIENTS; ?>" multiple="multiple">
	<!-- When we have more features I imagine we could have contact groups onclients :-) -->
		<optgroup label="Friends">
		<?php
			foreach($known as $name => $guids) {
				?>
				<option name="<?php echo $name; ?>" value="<?php echo $name; ?>"><?php echo ucwords($name); ?></option>
			<?php		
			}
		?>
		</optgroup>
	</select>

	<textarea cols="40" rows="5" name="<?php echo FORMS_STATUS; ?>"><?php echo $out?></textarea>
	<input type="submit"/>
</form>
	<a href="statusupdate.php"/>Reset</a>