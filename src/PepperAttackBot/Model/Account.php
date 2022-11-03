<?php
declare(strict_types=1);

namespace PepperAttackBot\Model;

class Account
{
    public function __construct(
        private string $email,
        private string $password,
        private int    $map,
        private int    $stage
    )
    {

    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getMap(): int
    {
        return $this->map;
    }

    public function getStage(): int
    {
        return $this->stage;
    }
}
