<?php
    require("src/skin/Face.php");
    require("src/MojangAPI.php");
    require("src/MinecraftServer.php");
    
    $server = new \EvG\MinecraftServer("104.237.0.21", 19132, 2);
    $server->connect();
    
    if($server->connected()) {
        //echo "Connected!";
       // $server->ping();
        //var_dump($server->isOnline());
    }
    var_dump($server->getChallenge());
    var_dump($server->getInfo());
    
    var_dump($server->ping());
    var_dump($server->getPlayersDeep($server->getPlayers()));
?>