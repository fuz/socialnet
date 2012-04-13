<?php

// todo: too complex, needs re-writing
// might be more efficient on large files

class MessageBuilder {

/**
Convert an array in the format of:
array(
	$name => guid
)
and a status message containing $name into an array of format:

array(text, UserRef(), text)
*/
	public static function createMessage($known, $text) {
		
		$status = htmlspecialchars($text);
		$out = $status;
		$foundnames = array();
		$chars = strlen($status);
		$search_status = $status;

		foreach($known as $name => $guids) {
			$guid = $guids[0];
			$prev = 0;
			// while a name is present
			while ( (($pos = stripos($search_status, $name, $prev))) !== FALSE ) {
				$size = strlen($name);
				$end = $pos + $size;
				$nextchar = substr($search_status, $end, 1);
				$prev = $end;
				echo "next:<br>[",$nextchar,"]<br>";
				if ($end+1 <= $chars && preg_match('/[^\s\!\?\,\.]/', $nextchar) === 1) {
					continue;
				}
				array_push($foundnames, $pos);
		
				// echo "Found ",$pos,"-",$end,"<br>";
				array_push($foundnames, $end);
		
				$search_status = substr_replace($search_status, str_repeat('_', $size), $pos, $size);
				echo "Search: ",$search_status,"<br>";
			}
		}

		array_multisort($foundnames, SORT_ASC);
		$contents = array();
		$prev = 0;
		/*
The array contains pairs of string positions where people's names are, for
example:
	0-15, 23-27, 30-34
	which would be represented like array(0,15,23,27,30,34)
We then take two items at a time get the text between these positions
but also the text between the previous pair such as:
	15-23, 27-30
This gets the text that is not someone's name.

For example in the following
message:
	"Hello there Jim, how are you"
Where "Jim" is a name, there would only be one item in this array:
	12-14
but since we get the junk too, we would automatically also get 0-12 and 15-27
		*/
		while (count($foundnames) >= 2) {
			$a = array_shift($foundnames);
			$b = array_shift($foundnames);
			$text = substr($status, $a, $b-$a);
			$junk = substr($status, $prev, $a-$prev);
	
			echo "Non: ",$junk,"<br>";
			array_push($contents, $junk);
			// $guid = $known->getUserId($text);
			echo "The text is: [",$text,"] ";
			$guid = $known[$text][0];
			var_dump($known);
			array_push($contents, new UserRef($guid, $text));
			echo $text,"<br>";
			$prev = $b;
		}

		// fetch the straggling characters
		// this only applies if the text does not end with a name 
		array_push($contents, substr($status, $prev, $chars-$prev));
		return $contents;
	}
}

?>