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
  1. vehicle kills are not always registered (not sure why)  
  1. some ops have no winner announced (endMission[1]: Mission ended automatically)  
     * nobody wins or loses the op (maybe the ending can be assumed or it's just omitted, not sure...)  
  1. the REAL commanders can not always be determined automatically and must be fixed manually (especially in older ops where group and role fields are missing)  


## Stats collected  
 * **Shots**  
   nr. of framesFired events (only counts main weapon and sidearm rounds afaik.)  
   \+ nr. of projectile markes tied to a unit (grenades, rockets, etc.)  
 * **Hits**  
   nr. of `hit` events tied to any unit or vehicle (asset)  
 * **Kills**  
   nr. of `killed` events tied to any unit or vehicle (asset) as attacker  
 * **Deaths**  
   nr. of `killed` (or `_dead` if no `killed` events can be found) events tied to any unit or vehicle (asset) as victim  
 * **Frienldy fire**  
   nr. of `hit` events tied to friendly units  
 * **Teamkills**  
   nr. of `killed` events tied to friendly units  
 * **Destroyed assets**  
   nr. of `killed` events tied to any vehicle (asset)  

Self inflicted and environmental hits / kills (_something_) are not counted!  

Timestamps are all UTC.  


## Search
CTRL+F


## TODO:
  1. format timestamps to local TZ  
  1. player profile tabs:  
      * role grouped stats  
      * weapon grouped stats  
      * attackers  
      * victims   
      * rivals (enemy commanders)  
  1. op profile (grouped stats for each side sum);  
     tabs:  
      * weapon stats 

