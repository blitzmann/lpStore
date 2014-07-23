lpStore
===

lpStore is a website for EVE Online allowing you to browse the LP Stores out of game while calculating the ISK/LP 
value of the items based market data.

lpStore is currently hosted here: http://blitzmann.it.cx/lpStore

Requirements
---
* PHP 5.4 (may work on earlier versions, but `short_open_tag` must be enabled for themes to work)
* [emdr-py] (https://github.com/blitzmann/emdr-py) (or any other EMDR consumer that stores data in Redis)
* [Redis](http://redis.io/) - 2 databases
    * A database to share with emdr. The application **will not** work without this.
    * Other database is optional and specifically for lpStore caching. The application **will** work without this.
* [phpredis](https://github.com/nicolasff/phpredis) PHP extension
* MariaDB with [OQGRAPH computational engine](http://openquery.com.au/node/23) (MariaDB has this bundled from 10.0.7-beta, or before 5.5 release). OQGRAPH is not neccessary for basic functionality of application, however a few features will not work without it (filtering offers by group and solar system pathfinding). Load database with:
    * [EVE Static Database Dump](https://www.fuzzwork.co.uk/dump/mysql-latest.tar.bz2)
    * [LP Database](https://forums.eveonline.com/default.aspx?g=posts&m=2508255)
    * [eve-oqgraph](https://github.com/blitzmann/eve_oqgraph/)
* [Savant3] (http://phpsavant.com/download/)
    
Features
---
* Browsable database of all LP Store offers in game
* ISK/LP calculated on-the-fly
* Used emdr-py as a EMDR consumer, gathering the most up-to-date market infomration
* Various filtering options on offer page
* Can change various pricing settings from the preferences page

Getting Started
---
1. Ensure that you have emdr-py up and running and properly adding pricing data into Redis database.
2. Set up MariaDB database with required data.
3. Edit `.ini` config files in `inc/` for database information, move somewhere secure (recommended out of web root)
4. Edit `lib/Config.php`
    * Specifiy Redis DB number for emdr-py and also one to be used for lpStore.
    * Edit paths to point to config files.

License
---
lpStore is released under GNU GPLv3 - see included gpl.txt

Screenshots
---
#### Landing Page
![lpstore](https://cloud.githubusercontent.com/assets/3904767/3668732/f36b056c-1223-11e4-8887-34a41cba8369.png)
#### Store Offer Listing
![lpstore 1](https://cloud.githubusercontent.com/assets/3904767/3668730/f3693606-1223-11e4-8b39-855c8cef2333.png)
#### Offer Details
![lpstore 2](https://cloud.githubusercontent.com/assets/3904767/3668731/f36ad4b6-1223-11e4-92e1-7560e7e9fb5d.png)

