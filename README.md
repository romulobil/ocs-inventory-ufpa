# Enhancement of OCS Inventory notifications mechanism

This code represents an improve of notification mechanism already present in OCS Inventory NG.

## Requirements:
For use this code, it's necessary the Docker's correlated package is installed in your machine. Thus, make sure:
- The docker main packages in your machine was installed.
- The docker-compose was installed, too.
- The current directory of work contains all files of [files here](https://duckduckgo.com). Theses files are essecial for configuration of containers.

## How to Use:
Once installed this preview packages and in possession of theses files [files here](https://duckduckgo.com), execute this following comands inside a directory that contains the _config_ files aforementioned. 

> $ sudo docker build -t ocsinventory/ocsinventory-docker-image-ctic:2.8 .

> $ sudo docker-compose up -d


This command will be building your base image of OCS Inventory application and make the deploy of this application in an new docker container.
Furthermore, remember, this application bring with you a database. Therefore, a MySQL container will be building in this process.

Whether everything works as expected, the both containers, OCS application and MySQL, will be running !

For verify this, use this docker's command below:

> $ sudo docker ps -a

## Acessing the OCS application
For acess the application, its naturally necessary to know the IP address of the machine. Known this, in some browser insert this URL:

> http://IP_of_the_machine/ocsreports
