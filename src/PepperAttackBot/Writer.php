<?php
declare(strict_types=1);

namespace PepperAttackBot;

class Writer
{
    public static function white(string $message, ...$args): void
    {
        echo "\e[97m".sprintf($message, ...$args)." \e[97m \n";
    }

    public static function green(string $message, ...$args): void
    {
        echo "\033[32m".sprintf($message, ...$args)." \e[97m \n";
    }

    public static function red(string $message, ...$args): void
    {
        echo "\033[31m".sprintf($message, ...$args)." \e[97m \n";
    }

    public static function yellow(string $message, ...$args): void
    {
        echo "\033[93m".sprintf($message, ...$args)." \e[97m \n";
    }

    public static function magenta(string $message, ...$args): void
    {
        echo "\033[95m".sprintf($message, ...$args)." \e[97m \n";
    }

    public static function blue(string $message, ...$args): void
    {
        echo "\033[94m".sprintf($message, ...$args)." \e[97m \n";
    }
}
