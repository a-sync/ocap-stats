# fnf-stats

## What is this?
[AAR](http://aar.fridaynightfight.org) data from [FNF](https://www.fridaynightfight.org) spread out on tables  
 &nbsp; &rdca; based on [OCAP2](https://github.com/OCAP2/OCAP) data  


## Known issues
  1. player stats are collected based on name only (aliases must be set manually to mitigate this)  
  1. ops before 2021-08-05 had no proper hit event tracking  
     * Hits / Shots percent and Shots / Hits are adjusted accordingly
  1. some units have less shots then hits  
     * some weapons produce multiple hit events (eg. MAAWS)  
  1. a few players have two entities in the same op with `killed` events (rejoin bug?)  
     * this affects role stats since there is no explicit way to tell which entity is actually playing
  1. vehicle kills are not always registered (not sure why)  
  1. ops before 2021-07-09, and some later ones have no winner announced (endMission[1]: Mission ended automatically)  
     * nobody wins or loses the op (maybe the ending can be assumed or it's just omitted, not sure...)  
  1. ops before 2021-07-16 have no role info  
     * commanders can not always be determined automatically and must be fixed manually   


## Stats collected  
 * **Shots**  
   nr. of framesFired events (only counts main weapon and sidearm rounds afaik.)  
   \+ nr. of projectile markes tied to a _unit_ (grenades, rockets, etc.)  
 * **Hits**  
   nr. of `hit` events as attacker, where vitctim is any _unit_ or _vehicle_ (asset)  
 * **Kills**  
   nr. of `killed` events as attacker, where victim is any _unit_ or _vehicle_ (asset)  
 * **Deaths**  
   nr. of `killed` events as victim, where attacker is any _unit_ or _vehicle_ (asset), or has at least one `_dead` event  
 * **Frienldy fire**  
   nr. of `hit` events as attacker, where victim is any _unit_ on the same side  
 * **Teamkills**  
   nr. of `killed` events as attacker, where victim is any _unit_ on the same side  
 * **Destroyed assets**  
   nr. of `killed` events as attacker, where victim is a _vehicle_ (asset)  

Self inflicted and environmental hits / kills (_something_) are not counted!  

Timestamps are all UTC.  


## Search
CTRL+F


## TODO:
  1. format timestamps to local TZ  
  1. player profile tabs:  
      * weapon stats  
      * attackers  
      * victims  
      * rivals (enemy commanders)  
  1. op profile (grouped stats for each side)  
     tabs:  
      * weapon stats 

