lpStore
=======

lpStore is a website for EVE Online allowing you to browse the LP Stores out of game while calculating the ISK/LP 
value of the items based market data.

Requirements
============
* PHP 5.4 (may work on earlier versions, but `short_open_tag` must be enabled for themes to work)
* [Redis](http://redis.io/) with at least 2 databases available (one for emdr, one specifically for lpStore)
* [phpredis](https://github.com/nicolasff/phpredis)
* MariaDB with [OQGRAPH computational engine](http://openquery.com.au/node/23) (MariaDB has this bundled from 10.0.7-beta, or before 5.5 release). OQGRAPH is not neccessary for basic functionality of application, however a few features will not work without it. Load database with:
    * [EVE Static Database Dump](https://www.fuzzwork.co.uk/dump/mysql-latest.tar.bz2)
    * [LP Database](https://forums.eveonline.com/default.aspx?g=posts&m=2508255)
    * [eve-oqgraph](https://github.com/blitzmann/eve_oqgraph/)
* [emdr-py] (https://github.com/blitzmann/emdr-py) (or any other script which does that same thing)
* Savant3 (http://phpsavant.com/download/)
    
Features
============
* Browsable database of all LP Store offers in game
* ISK/LP calculated on-the-fly
* Used emdr-py as a EMDR consumer, gathering the most up-to-date market infomration
* Various filtering options on offer page

Getting Started
============
1. Ensure that you have emdr-py up and running and properly adding pricing data into Redis database.\
2. Set up database with required data.
3. Edit config files in `inc/`, move somewhere secure (recommended out of web root)
4. Edit `lib/Config.php`, specifiy Redis DB number for emdr-py and also one to be used for lpStore. Edit paths to point to config files.
