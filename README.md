# Pepper Attack Bot

Version: 1.0

## Requirements

- PHP 8.1
- Composer 2.x

## Install application

In directory with bot
```shell
compsoer install
```

## Running application


### Create `account.json` file

```json
{
  "accounts": [
    {
      "email": "your_email",
      "password": "your_password",
      "map": 2,
      "stage": 16
    }
  ]
}

```

```shell
php collect_rations.php // collect rations, run before stack 500 rations to collect
php season_tasks.php // run on start season if you have enuogh stims 
php tovern_fight.php // run for fight in tovern
php adventure.php // run for fight in adventure
php daily_tasks.php // run to admire potions, claim quests or dice roll
```


Bot doesn't write your password or email anywhere. It's use only for login to PepperAttack game.

# Donate

Pepper Attack Bot is a free application. You can also feel free to modify code.
However, you need to know I had to spend time to create this

I will be very grateful to you if you appreciate my work and donate me any amount on the address:
```
0xfc6187e8ece22C0515DEBc129Bb7568a1Dd4fC9a
```
You can send ETH, MATIC or MYTE which you will earn with this bot :)


# Set schedule to run your bot.

Linux 
1. Install `cron` on your computer.
```shell
sudo apt-get install cron
```

2. Edit config file and set schedule for your bot
```shell
nano /etc/crontab
```

And you append a line on end of file
```shell
30 2 * * WED php /home/debian/PepperAttackBot/season_tasks.php

0 */6 * * * php /home/debian/PepperAttackBot/collect_rotion.php
0 7 * * * php /home/debian/PepperAttackBot/daily_tasks.php

0 8 * * * php /home/debian/PepperAttackBot/adventure.php
0 10 * * * php /home/debian/PepperAttackBot/tovern_fight.php 
```

Save file and forgot to collection ration and admire potions :)
