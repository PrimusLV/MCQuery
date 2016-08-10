# MCQuery

Made for @EvalGamingPE check this in use here: http://evalgaming.xyz/status

### Example
```php
$server = new \EvG\MinecraftServer("evalgaming.xyz:19132");
$server->connect();
$server->getChallenge(); // Get challenge key before sending any other data to target server
$info = $server->getInfo();
var_dump($info);
```
example output
```
array (size=13)
  'address' => string '104.237.0.21:19132' (length=18)
  'status' => int 1
  'hostname' => string 'EvalGaming Core is loading...' (length=29)
  'gametype' => string 'SMP' (length=3)
  'game_id' => string 'MINECRAFTPE' (length=11)
  'version' => string 'v0.15.4 alpha' (length=13)
  'server_engine' => string 'Genisys ' (length=8)
  'plugins' => string 'Genisys ' (length=8)
  'map' => string 'lobby' (length=5)
  'numplayers' => string '0' (length=1)
  'maxplayers' => string '500' (length=3)
  'whitelist' => string 'off' (length=3)
  'players' => 
    array (size=1)
      Notch => array (size=3)
        profile => array (size=2)
          name => string 'Notch' (length=5)
          id => string '01kma781-najuh1sa-1871baas-198291h4a-112893y1' (length=54)
        skin => string 'TOO MUCH TO SHOW HERE' (string=0)
```
