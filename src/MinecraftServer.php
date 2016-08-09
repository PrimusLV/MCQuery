<?php
namespace EvG;

class MinecraftServer {
    
    const CHALLENGE = "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01";
    const PING_PACKET = "\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
    const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
    
    public $ip, $port, $timeout;
    
    private $socket = null;
    
    private $data = [];
    
    private $challenge = "";
    
    private $online = false;
    
    public function __construct( $ip, $port = 19132, $timeout = 3 ) {
        if(strpos($ip, ":") !== false) {
            $this->ip = explode(":", $ip)[0];
            $this->port = explode(":", $ip)[1];
        } else {
            $this->ip = $ip;
            $this->port = $port;
        }
        $this->timeout = $timeout;
    }
    
    public function connect() {
        $s = @fsockopen("udp://".$this->ip, $this->port, $errno, $error, $this->timeout);
        if(!$s) {
            throw new \Exception("connection failed $errno: $error");
        }
        $this->socket = $s;
        
        socket_set_timeout( $s, 0, 500000 );
		stream_set_blocking( $s, true );
		return true;
    }
    
    private static function readLInt($str) {
		if (PHP_INT_SIZE === 8) {
			return unpack("V", $str)[1] << 32 >> 32;
		} else {
			return unpack("V", $str)[1];
		}
	}
    
    public function ping() {
		$time = microtime(true);
		fwrite($this->socket, self::PING_PACKET, strlen(self::PING_PACKET));
		$buffer = fread($this->socket, 1024);
		$ping = microtime(true) - $time;
		if (strlen($buffer) < 35 or ord($buffer[0]) !== 0x1c or substr($buffer, 17, 16) !== self::MAGIC) {
			return false;
		}
		$serverId = self::readLInt(substr($buffer, 9, 8));
		$data = substr($buffer, 35);
		if (preg_match('/^MCPE;(.*);(\d+);(\d+\.\d+\.\d+);(\d+);(\d+)$/', $data, $matches)) {
			array_shift($matches);
			list($rawName, $protocol, $version, $players, $maxPlayers) = $matches;
			$colorName = str_replace('\;', ";", $rawName);
			$name = preg_replace('/§[0123456789abcdefklmnor]/', '', $colorName);
			return compact("name", "colorName", "protocol", "version", "players", "maxPlayers", "serverId", "ping");
		} elseif (substr($data, 0, 11) === "MCCPP;Demo;") {
			// older protocols
			$rawName = substr($data, 11);
			$colorName = str_replace('\;', ";", $rawName);
			$name = preg_replace('/§[0123456789abcdefklmnor]/', '', $colorName);
			return compact("name", "colorName", "serverId", "ping");
		}
		return false;
    }
    
    public function isOnline() {
        return $this->online;
    }
    
    public function getSocket() { return $this->socket; }
    
    public function getChallenge() { 
        $challenge = $this->write(self::CHALLENGE, 1400);
        if(!$challenge) return;
        $this->challenge = substr( preg_replace( "/[^0-9\-]/si", "", $challenge ), 1 );
        return $this->challenge;
    }
    
    public function getInfo() {
        $query = sprintf(
        "\xFE\xFD\x00\x10\x20\x30\x40%c%c%c%c\xFF\xFF\xFF\x01",
        ( $this->challenge >> 24 ),
        ( $this->challenge >> 16 ),
        ( $this->challenge >> 8 ),
        ( $this->challenge >> 0 )
        );
        $response = $this->write($query);
        $return = array();
        if(strlen($response)) {
            $return["status"] = 1;
            $tmp = explode("\x00\x01player_\x00\x00", $response);
            if(($pos = strpos($response, "\x00\x01")) !== false) {
                $response = substr($response, 0, $pos);
            }
            $response = substr($response,16);
            $response = explode("\0",$response);
            array_pop($response);array_pop($response);array_pop($response);array_pop($response);
            $type = 0;
            foreach ($response as $key)
            {
                if ($type == 0) $val = $key;
                if ($type == 1) $return[$val] = $key;
                $type == 0 ? $type = 1 : $type = 0;
            }
            if(isset($tmp[1])) {
                $ps = explode("\x00", $tmp[1]);
                $players = [];
                foreach($ps as $p) {
                    if($p == "") break;
                    $players[] = $p;
                }
                $return["players"] = $this->getPlayersDeep($players);
            } else {
                $return["players"] = [];   
            }
        } else {
            $return["status"] = 0;
            $return["error"] = "Server is offline or unreachable";
        }
        $this->data = $return;
        return $return;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getPlayers() {
        return $this->data["players"];
    }
    
    public function getPlayersDeep(array $players) {
        $return = [];
        foreach($players as $player) {
            $return[$player] = ["skin" => false, "nameHistory" => [], "profile" => false];
            $profile = \MojangAPI::getProfile($player);
            $return[$player]["profile"] = $profile;
            if(!$profile) continue;
            $skin = \MojangAPI::getSkin($profile["id"]);
            $return[$player]["skin"] = $skin;
            $return[$player]["nameHistory"] = \MojangAPI::getNameHistory($player);
        }
        return $return;
    } 
    
    public function connected() {
        return $this->socket !== NULL;
    }
    
    public function write($command, $length = 4096) {
        if(!$this->connected()) return;
        
		if( !@fwrite( $this->socket, $command) )
		{
			throw new \Exception( "Failed to write on socket." );
		}
        
        $data = fread($this->socket, $length);
        
        if($data === false) {
            throw new \Exception("failed to read from socket");
        }
        
        return $data;
    }
}