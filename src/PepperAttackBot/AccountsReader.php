<?php
declare(strict_types=1);

namespace PepperAttackBot;

use PepperAttackBot\Model\Account;

class AccountsReader
{
    private array $accounts;

    public function __construct(string $file)
    {
        $data = json_decode(file_get_contents($file), true);

        foreach ($data['accounts'] as $account) {
            $stage = (int)$account['stage'];
            if ($stage % 10 === 0) {
                $map = (($stage - ($stage % 10)) / 10) ;
            } else {
                $map = (($stage - ($stage % 10)) / 10) + 1;
            }

            $this->accounts[] = new Account($account['email'], $account['password'], $map, $stage, $account['new_season_stats'] ?? []);
        }
    }

    /** @return Account[] */
    public function getAccounts(): array
    {
        return $this->accounts;
    }

    public function setAccounts(array $accounts): void
    {
        $this->accounts = $accounts;
    }

    public function getAccount(string $email): ?Account
    {
        foreach ($this->getAccounts() as $account) {
            if ($account->getEmail() == $email) {
                return $account;
            }
        }
        return null;
    }
}
