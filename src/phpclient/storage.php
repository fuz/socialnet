<?php

define('DATABASE_FILE', 'data.sqli');

require_once('static.php');
require_once('packetsaver.php');

/**
Global storage for all users.
*/

class Storage {
	public $db;

	public function getDatabase() {
		return BASE . '\\' . DATABASE_FILE;
	}

	public function __construct($installqueries) {
		// default storage constructor
		$this->connect($installqueries);
	}
 
	public function connect($installqueries) {
		try {
			$path = $this->getDatabase();
			$exists = file_exists($path);	

			// if the file doesn't actually exist, it makes it anyway!
			$this->db = new PDO('sqlite:' . $path);
			
			$db = $this->db;
			
			if (!$exists) {
				echo "Creating databases...<br>";
				$db->beginTransaction();

				foreach($installqueries as $installquery) {
					$query = $this->getQuery($installquery, array(), INSTALL_DIR);
					$error = $query->execute();

				}
				$db->commit();
			}
	
		} catch (Exception $e) {
			die($e);
		}
	}

	public function getQuery($name, $subst = array(), $base = QUERIES_DIR, $settings = array()) {
		$sql = $this->getQueryText($name, $base);
		foreach($subst as $search => $replacement) {
			$sql = str_replace($search, $replacement, $sql);
		}

		$prepared = $this->db->prepare($sql, $settings);
		
		return $prepared;
	}

	/**
	Fetch the query from the query dir.
	*/
	public function getQueryText($name, $base = QUERIES_DIR) {
		return file_get_contents($base . $name . SQL_EXT);
	}

	public function insert($update) {
		echo "In Storage->insert","<br>";
		$this->db->beginTransaction();
		$update->insert($this);
		$this->db->commit();
	}

	public function get($fetch) {
		return $fetch->get($this);
	}
}

class KnownProfile {
	public $profile;
	public $static;

	public function __construct($profile = NULL) {
		$this->profile = $profile;
	}
	
	public function insert($db) {
		echo "Adding profile to knownprofiles","<br>";
		$q = $db->getQuery(QUERIES_ADD_KNOWN_PROFILE);
		
		$data = array(
			':guid' => $this->profile->guid,
			':displayname' => $this->profile->displayname
		);
		$q->execute($data);
		
	}

	public function get($db) {
		$q = $db->getQuery(QUERIES_GET_ALL_KNOWN_PROFILES);
		$q->execute();
		return $q->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
	}
}

/**
Handles insertation of a packet into USERDATA.
*/
class IncomingPacket {
	public $packet;
	public function __construct($packet) {
		$this->packet = $packet;
	}

	public function insert(UserStorage $db) {
		$p = $this->packet;
		echo "Adding packet to packet table","<br>";
		$subs = array(':destination' => $p->destination);
		$q = $db->getQuery(QUERIES_ADD_PACKET, $subs);

		$ps = new PacketSaver();
		$data = array(
			':sentfrom' => $p->from,
			':sentto' => $db->guid,
			':sentdate' => $p->sentdate,
			':guid' => $p->guid,
			':raw' => $ps->toText($p)
			
		);
		$q->execute($data);
	}
}

class GetInpackets {
	public $from;
	public function __construct($from) {
		$this->from = $from;
	}

	public function get($db) {
		$subs = array(':destination' => $this->from);
		$q = $db->getQuery(QUERIES_GET_INPACKETS, $subs);
		$q->execute();
		return $q->fetchAll();
	}
}

class IncomingEncryptedPacket {
	public $data = array();
	
	public function __construct($packet = "") {
		$this->data['packet'] = $packet;
		
	}

	public function update(UserStorage $db) {
		$q = $db->getQuery(QUERIES_UPDATE_WAITING);
		$q->execute($this->data);
	}

	public function insert(UserStorage $db) {
		$q = $db->getQuery(QUERIES_ADD_WAITING);
		$q->execute($this->data);
	}

	public function getWaitingPackets($db) {
		$q = $db->getQuery(QUERIES_CHECK_WAITING);
		$q->execute();
		$results = $q->fetchAll();
		$size = count($results);

		if ($size == 0) {
			return FALSE;
		}
		return $results;
	}
}

/**
Data in the social network from the perspective of an individual user
is persisted to a database.
*/

class UserStorage extends Storage {
	// the user this storage belongs to
	public $guid;

	public function __construct($guid, $installqueries) {
		$this->guid = $guid;
		parent::connect($installqueries);
	}

	public function getDatabase() {
		return getcwd() . '\\' . USERDATA_DIR . $this->guid . DATABASE_EXT;
	}

}




?>