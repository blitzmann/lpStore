lpStore
=======

lpStore is a website for EVE Online allowing you to browse the LP Stores out of game while calculating the ISK/LP 
value of the items based market data.

Requirements
============
* PHP (tested on 5.5, unsure about minimum version)
* MariaDB with [OQGRAPH computational engine](http://openquery.com.au/node/23) (MariaDB has this bundled from 10.0.7 onward)
* [emdr-py] (https://github.com/blitzmann/emdr-py)
    * Redis
    * Python 2.7
    
Features
============
* Browsable database of all LP Store offers in game
* ISK/LP calculated on-the-fly
* Used emdr-py as a EMDR consumer, gathering the most up-to-date market infomration
* Various filtering options on offer page
