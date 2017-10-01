# reaper-killmail-bot
Posts Eve killmails to a discord webhook

# Installation
Run these commands:
```
git clone https://github.com/atwardowski/reaper-killmail-bot
cd reaper-killmail-bot
```
Now follow the instructions to get php composer here: https://getcomposer.org/download/

Then run `php composer.phar install` to install dependencies.

Create a webhook on your discord server and copy the URL to put in the config file.

Now create a config.php with settings in it like below, 
replacing the ID numbers in the array with the Id's of the character/corp/alliances you want to monitor.  
The config.php file overrides the settings at the top of the program file.  
You can put multiple IDs in each array if you want to.
```
<?php
$webhookurl = 'https://discordapp.com/api/webhooks/12345678/abcdefgh';
$chars = ['94307228'];
$corps = ['98276273'];
$alliances = ['99003214'];
$postkills = true;
$postlosses = true;
$terminate_after_post = false;
```
Now setup something that will run your script in the background.  I recommend supervisor, there is a supervisor sample config included.
