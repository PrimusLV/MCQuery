<?php
require("src/Pinger.php");
$pinger = new Pinger();

if(isset($_GET['host'])) {
    var_dump($pinger->ping($_GET['host'], isset($_GET['port']) ? $_GET["port"] : 19132));
}
?>