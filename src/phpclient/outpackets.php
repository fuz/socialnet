<?

// Include Fuz's PacketFetcher:

require('packetfetcher.php');
require('parser.php');

class Sender {

        public $packet_parser;
        
	public function __construct() {
		$this->packet_parser = new PacketParser();
		// thanks!
	}

	public function sendPacketFile($packetFile)
	{
	        $multiPackets = $this->packet_parser->parse('outpackets/' . $packetFile);

	        foreach($multiPackets as $packet) {
                     var_dump($packet);
	        }
	}
}

// Processing all packets in the folder 'outpackets'

$packet_fetcher = new PacketFetcher('outpackets');
$packets = $packet_fetcher->getPacketFiles();

// Initializing the 'Sender' object

$sender = new Sender();

// Going through each packet

foreach($packets as $packetFile) {
     $sender->sendPacketFile($packetFile);
}

?>