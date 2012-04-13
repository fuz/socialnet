<?php
class Contacts {
	public $contacts = array();

	public function addUser($name, $guid) {
		$this->contacts[strtolower($name)] = $guid;
	}
	public function getUserId($name) {
		return $this->contacts[strtolower($name)];
	}
}

$known = new Contacts();
$known->addUser("Bob", "54bc7953-929e-4e58-9702-94c3fc313940");
$known->addUser("Alice", "a0c76793-b098-4ddb-b4d3-fc96a7ad9be6");
?>