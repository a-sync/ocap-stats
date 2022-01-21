# ocap-stats

## What is this?
[OCAP](https://github.com/OCAP2/OCAP) data spread out on tables.  


## Demo sites 
[fnf-stats.devs.space](https://fnf-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP2](http://aar.fridaynightfight.org) data from [FNF](https://www.fridaynightfight.org)  
[ofcra-stats.devs.space](https://ofcra-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP](https://game.ofcra.org/ocap) data from [OFCRA](https://ofcrav2.org)  
[3cb-stats.devs.space](https://3cb-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP2](https://ocap.3commandobrigade.com) data from [3CB](https://www.3commandobrigade.com)  
[242ns-stats.devs.space](https://242ns-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP2](http://server.242nightstalkers.com:5000) data from [242NS](https://steamcommunity.com/groups/242NS)  
[rb-stats.devs.space](https://rb-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP](https://ocap.red-bear.ru) data from [RED-BEAR](https://www.red-bear.ru)  


## Known issues
  1. player stats are collected based on name only (aliases must be set manually to mitigate this)  
  1. ops recorded before using OCAP2 v1.1.0 had no proper tracking for hit events where the victim is a player  
     * Hits / Shots percent and Shots / Hits must be adjusted accordingly
  1. players can have multiple entities in the same op (rejoins/role switch)  
     * this affects role data since there is no explicit way to tell which entity was actually playing
     * in a few cases two entities of the same player have `killed` events in a single op (dc/spectator bug/_something_?)
  1. vehicle kills are not always registered (not sure why)  
  1. some ops have no winner announced (endMission[1]: Mission ended automatically)  
     * affects all ops recorded before using OCAP2 v1.0.0
     * nobody wins or loses the op (maybe the ending can be assumed or it's just omitted, not sure...)
  1. ops recorded before using OCAP2 v1.0.0 are missing the role info  
  1. commanders can not always be determined automatically and must be fixed manually  
  1. timestamps are all UTC (or should be) and depend on the arma3 server  


## Stats collected  
  * **Shots**  
    nr. of framesFired events (only counts main weapon and sidearm rounds afaik.)  
  \+ nr. of projectile markers tied to a _unit_ (grenades, rockets, mines, etc.)  
  * **Hits**  
    nr. of `hit` events as attacker, where victim is any _unit_ or _vehicle_ (asset)  
  * **Kills**  
    nr. of `killed` events as attacker, where victim is any _unit_ or _vehicle_ (asset)  
  * **Deaths**  
    MAX( nr. of `killed` events, nr. of `_dead` events ) as victim  
  * **Frienldy fire**  
    nr. of `hit` events as attacker, where victim is any _unit_ on the same side  
  * **Teamkills**  
    nr. of `killed` events as attacker, where victim is any _unit_ on the same side  
  * **Destroyed assets**  
    nr. of `killed` events as attacker, where victim is a _vehicle_ (asset)  

Self inflicted and environmental hits / kills (_something_) are omitted! (except for deaths)  


## Search
CTRL+F


## TODO:
  1. format timestamps to local TZ
  1. filter op events visibility by type
  1. player profile tabs:
     * rivals (enemy commanders)
  1. ui to edit entities player assignment
  1. support `capture_delay` properly
