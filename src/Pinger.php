<?php
class Pinger {
	const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
	const PING_PACKET = "\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";
	private $socket;
	public function __construct($timeout = 1000) {
		$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		$sec = floor($timeout/1000);
		$usec = ($timeout - ($sec * 1000))*1000;
		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $sec, "usec" => $usec));
	}
	private static function readLInt($str) {
		if (PHP_INT_SIZE === 8) {
			return unpack("V", $str)[1] << 32 >> 32;
		} else {
			return unpack("V", $str)[1];
		}
	}
	public function ping($ip, $port) {
		$time = microtime(true);
		socket_sendto($this->socket, self::PING_PACKET, strlen(self::PING_PACKET), 0, $ip, $port);
		socket_recvfrom($this->socket, $buffer, 1024, 0, $ip, $port);
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
	public function __destruct() {
		socket_close($this->socket);
	}
}