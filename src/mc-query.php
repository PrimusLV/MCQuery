<?php
    require("skin/Face.php");
    require("MojangAPI.php");
    require("MinecraftServer.php");
    
    @mkdir("../save");
    $servers = [
        "lobby" => [
            "servers" => [
                "104.237.0.21:19132",
                ],
            "display" => "Lobby"
            ]
        ];
    $updateInterval = 60;
    
    foreach($servers as $gamemode => $prop) {
        foreach($prop["servers"] as $k => $server) {
            $server = new \EvG\MinecraftServer($server);
            $servers[$gamemode]["servers"][$k] = $server;
        }
    }
    
    
    $running = true;
    $loops = 0;
    while($running) {
        sleep(1);
        if($loops === 0) {
            info("Running!");
        }
        if($loops % $updateInterval === 0) {
            // Update the servers
            foreach(getAllServers() as $server) {
                if(!$server->connected()) {
                    $server->connect();
                    $server->getChallenge();
                    info("Connected to ".$server->ip.":".$server->port."");
                }
                $server->getInfo();
            }
            save();
            info("Servers updated and saved");
        }
        
        $loops++;    
    }
    
    function getServers($gamemode) {
        global $servers;
        if(isset($servers[$gamemode])) return $servers[$gamemode]["servers"];
        return [];
    }
    
    function getAllServers() {
        global $servers;
        $s = [];
        foreach($servers as $gamemode) {
            foreach($gamemode["servers"] as $server) {
                $s[] = $server;
            } 
        }
        return $s;
    }
    
    function save() {
        global $servers;
        $save = [];
        foreach($servers as $gamemode => $prop) {
            $save[$gamemode] = $prop;
            foreach($prop["servers"] as $key => $server) {
                var_dump($server->getData());
                $save[$gamemode]["servers"][$key] = json_encode($server->getData());
            }
        }
        file_put_contents("../save/servers.json", json_encode($save));
    }
    
    function info($message) {
        echo "[INFO]: ".$message."\n";
    }