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

![notification_page](https://gl.idc.ufpa.br/ocs_inventory-ufpa/2.8/-/raw/master/downloads/ocs_inventory_notifications_config.png "title")

Some fields of this page is self explanatory. But is also important specify all these fields. Therefore:
- `NOTIF_FOLLOW`: A checklist to activate the notification by email.
- `NOTIF_MAIL_ADMIN`: administrator mail to receive notifications.
- `NOTIF_NAME_ADMIN`: username of administrator.
- `NOTIF_SEND_MODE`: Type of mail send mode. In the other words: With criptography(SSL | TLS) or not(Only SMTP). Remember, the most mail clients doesn't allowed send mails without some criptography. Thus, is extremely recommended to use SSL or TLS protocol in this process.
- `NOTIF_SMTP_HOST`: Your host smtp to send mail. e.g. `smtp.gmail.com` for google mails. Remember, if you pretend use an organization mail, consult your network _admin_ for more information about SMTP host of its organization.
- `NOTIF_PORT_SMTP`: This field depends of protocol use in NOTIF_SEND_MODE. That is, if TLS is being use, then, the port number is 587. Else, if SSL is being use, then, the port number is 465.
- `NOTIF_PASSWD_SMTP`: Your password mail.

After finish this configuration remember to save in
