# ocap-stats
## What is this?
[OCAP](https://github.com/OCAP2/OCAP) JSON data parser/editor/viewer web service.  

Provides a handy UI to parse OCAP JSON data files, then store and edit the data in a database.  
The data is presented on a simple spreadsheet like web frontend.  
> _OCAP web server » [JSON] » ocap-stats (parser) » [SQL] » ocap-stats (editor/viewer) » [HTML]_  


## Features
 * OCAP JSON data file parser that inserts the contents in an SQL database
   * `markers`, `framesFired` and `positions` entries are aggregated to create more entity data and events, not stored in their entirety
   * compatible with all OCAP versions (with limitations)
 * automatic corrections for known and detectable OCAP JSON data errors
 * commander detection based on group and role configuration
 * player tracking via the unit name or uid (not implemented in the official OCAP release)
 * content manager interface
   * select what OCAP entries get added to the stats as ops
   * fix incorrect or missing op data (event type, mission info, commanders)
   * assign player aliases


## Demo sites
[fnf-stats.devs.space](https://fnf-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP2](http://aar.fridaynightfight.org) data from [FNF](https://www.fridaynightfight.org)  
[ofcra-stats.devs.space](https://ofcra-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP](https://game.ofcra.org/ocap) data from [OFCRA](https://ofcrav2.org)  
[ofcra2-stats.devs.space](https://ofcra2-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP2](http://aar.ofcra.org:5000) data from [OFCRA](https://ofcrav2.org)  
[3cb-stats.devs.space](https://3cb-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP2](https://ocap.3commandobrigade.com) data from [3CB](https://www.3commandobrigade.com)  
[rb-stats.devs.space](https://rb-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP](https://ocap.red-bear.ru) data from [RED-BEAR](https://www.red-bear.ru)  
[tbd-stats.devs.space](https://tbd-stats.devs.space)  
 &nbsp; &rdca; based on [OCAP2](http://tbdevent.eu:5000) data from [TBD](https://tbdevent.eu)  


## Search
CTRL+F


## Aggregated data
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
  * **Distance traveled**  
    the delta sum of all the position coordinates of an entity 
  * **Time in game**  
    the nr. of frames recorded of an entity in game (including spectator), adjusted by the ops capture delay configuration  

Self inflicted and environmental hits / kills (_something_) are omitted! (except for deaths)  


## Aggregated events
 * **_enter_vehicle**
 * **_exit_vehicle**
 * **_awake**
 * **_uncon**
 * **_dead**
 * **_projectile**


## Known issues
  1. player stats are collected based on name only (aliases must be set manually to mitigate this)  
  1. ops recorded before using OCAP2 v1.1.0 had no proper tracking for hit events where the victim is a player  
     * Hits / Shots percent and Shots / Hits are adjusted accordingly
  1. players can have multiple entities in the same op (rejoins/role switch)  
     * this affects role data since there is no explicit way to tell which entity was actually playing
     * players can have `killed` events as spectator sometimes in relation to this
  1. vehicle kills are not always registered  
  1. some hits / kills are registered to the weapon or item selected by the attacker at the time of the event  
  1. some ops have no winner announced (endMission[1]: Mission ended automatically)  
     * affects all ops recorded before using OCAP2 v1.0.0
  1. ops recorded before using OCAP2 v1.0.0 are missing the role info  
  1. commanders can not always be determined automatically and must be set manually  
  1. timestamps depend on the arma3 server config  


## Custom views, charts and live dashboards
### OData
[ocap-odata](https://github.com/a-sync/ocap-odata) is an open source web service _(and one day complete replacement of ocap-stats maybe)_ that provides OData, OpenAPI and other REST APIs to an existing OCAP (ocap-stats) database. The different endpoints can be consumed by a variety of analytics and business intelligence tools.

### Direct database connection
To create more complex views and charts directly from the database you can use tools like [Seal Report](https://sealreport.org/) or [Metabase](https://www.metabase.com/start/oss/).


## TODO:
  1. format timestamps to local TZ (data-ts; Intl.DateTimeFormat().resolvedOptions().timeZone)
  1. filter op events visibility by type, event links to OCAP with timecode
  1. ui to edit entities player assignment
  1. support `capture_delay` properly
  1. track friendly fire on assets by checking the side of the asset's crew  
     (preprocess and track asset entities crews via positions)
  1. new player tab for kill/death log ☠💀


## Similar projects _(not open source)_
 * https://game.ofcra.org/stats/
 * https://en.stats.wogames.info/projects/wog-a3/
