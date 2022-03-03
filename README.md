# Enhancement of OCS Inventory notifications mechanism

This code represents an improve of notification mechanism already present in OCS Inventory NG.

## :memo: Requirements:
For use this code, it's necessary the Docker's correlated package is installed in your machine. Thus, make sure:
- The docker main packages in your machine was installed.
- The docker-compose was installed, too.
- The current directory of work contains all files of [files here](https://duckduckgo.com). Theses files are essecial for configuration of containers.

## :information_source: How to Use:
Once installed this preview packages and in possession of theses [files here](https://duckduckgo.com), execute this following comands inside a directory that contains the _config_ files aforementioned. 

> $ sudo docker build -t ocsinventory/ocsinventory-docker-image-ctic:2.8 .

:warning: Note, there is a _dot_ in the final of above command. Remember, this dot represent a special directory on GNU/Linux system, that is, the itself directory. 

> $ sudo docker-compose up -d


This command will be building your base image of OCS Inventory application and make the deploy of this application in an new docker container.
Furthermore, remember, this application bring with you a database. Therefore, a MySQL container will be building in this process.

If everything works as expected, the both containers, OCS application and MySQL, will be running !

For verify this, use this docker command below:

> $ sudo docker ps -a

## Acessing the OCS application
For acess the application, it's naturally necessary to know the IP address of the machine. Known this, in some browser, insert this URL:

> http://IP_of_the_machine/ocsreports

And with this, you will be welcomed with a login screen. The default credentials is:
- user: admin
- password: admin

:exclamation: It's no longer necessary to say the extremely required to change these credentials, once inside the system.
|-----------------------------------------|

## Configuring the notifications
Inside the system go to `Configuration > Notifications`. You will see this following page:
