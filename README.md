# Pepper Attack Bot

Version: 0.7

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
php adventure.php you_remail your_password map_id stage_id heal_hp_left<optional>
php daily_quest.php you_remail your_password
```

# Adventure mod
- map_id - it's first number in your current stage (displayed on screen). Example: Stage 2-1 your map_id = 2
- stage_id - it's second number in your current state (displayed on screen) plus map_id multiple 10 minus 10. Example: Stage 3-2 your stage_id = 22  
- heal_hp_left - it's max health points left after healing your peppers. 
  - Example1: Your pepper has max HP 160 and his current HP is 140, and you set heal_hp_left on 50 than your pepper will not be healed.
  - Example2: Your pepper has max HP 160 and his current HP is 140, and you set heal_hp_left on 20 than your pepper will be healed.
- 

 
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
0 6 * * * php BOT_DIR_PATH/admire.php your_email your_password
0 9 * * * php BOT_DIR_PATH/tovern_fight.php your_email your_password
0 11 */2 * * php BOT_DIR_PATH/tovern_fight.php your_email your_password map_id stage_id hp_left_points<optional>
0 22 * * * php BOT_DIR_PATH/daily_quest.php your_email your_password
```

Save file and forgot to collection ration and admire potions :)
