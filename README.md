# SimCity-Online-Region-Exchange
LAMP web application for hosting multiplayer SimCity 4 regions.

Migrated from SourceForge for historical purposes.

![Proof of Age]
(/proof.png?raw=true "Proof of age.")

## Original README

SCORE Stands for SimCity Online Region Exchange. The goal of the project is to provide the SimCity 4 user community a means of hosting shared games where players co-operatively play a region or regions together - not unlike SimCityScape. The benefits of SCORE are that anyone can setup and host their own community games and control various settings for those games such as if users get cities only for a few days or indefinitely, the terrain and size of regions and cities. Other features of SCORE are email notification of neighbor city updates, and managed upload/download of cities and regions, detailed information regarding region and city population numbers.

The challenges of this project have largely been overcome already (been three weeks in development). Largely these challenged have related to extracting the necessary information from the save game files and building a cohesive region image based on each cities varied size isometric tile (picture of the city).

This is a web application utilizing Php, GD Image Library and a SQL database and will be a cross platform, cross-browser solution.

Feature List:

flexible sized regions
mixed city sizes in those regions (small, medium and large)
user tracking of who’s mayor of which city
email notification of updates to neighbor cities
multiple regions(games) hosted on one server
many details about the region and cities available in the game will be available on the web site
no ftp and easy management of uploads and downloads of city files
Existing data formats utilized:

Images are PNG format
Reading .sc4 (SimCity save game) files
In future SCORE will likely integrate with PhpBB and or PostNuke just in terms of sharing a single user account across all three solutions.
