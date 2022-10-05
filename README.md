# Pepper Attack Bot

Version: 0.3

## Requirements

- PHP 8.1
- Composer 2.x

## Install application

In directory with bot
```shell
compsoer install
```

## Running application

```shell
php collect_rotion.php you_remail your_password
php admire.php you_remail your_password
php tovern_fight.php you_remail your_password
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
10 */6 * * * php BOT_DIR_PATH/collect_rotion.php your_email your_password
10 6 * * * php BOT_DIR_PATH/admire.php your_email your_password
10 9 * * * php BOT_DIR_PATH/tovern_fight.php your_email your_password
```

Save file and forgot to collection ration and admire potions :)
