<?php
declare(strict_types=1);

namespace PepperAttackBot;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use PepperAttackBot\Model\Details;
use PepperAttackBot\Model\Inventory;
use PepperAttackBot\Model\Pepper;

class Client
{
    private string $token;
    private HttpClientInterface $client;
    private string $url = 'https://api.pepperattack.com';

    private array $headers = [
        'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36',
        'origin' => 'https://play.pepperattack.com',
        'referer' => 'https://play.pepperattack.com',
        'accept-language' => 'pl-PL,pl;q=0.9,en-US;q=0.8,en;q=0.7',
        'accept-encoding' => 'gzip, deflate, br',
    ];


    public function __construct()
    {
        $this->client = HttpClient::createForBaseUri($this->url);
    }

    public function login(string $email, string $password): void
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/auth/login",
            [
                'headers' => $this->headers,
                'json' => [
                    'email' => $email,
                    'password' => $password
                ]
            ]

        );
        if ($response->getStatusCode() !== 201) {
            echo "Error login. Status code " . $response->getStatusCode() . "\n";
        }
        $data = json_decode($response->getContent(), true);
        $this->token = $data['data']['token'];
    }

    public function collectRation(): void
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/inventory/ration/charge",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 201) {
            echo "Error call. Status code " . $response->getStatusCode() . "\n";
        }
    }

    public function getDetails(): Details
    {
        $response = $this->client->request(
            'GET',
            $this->url . "/home",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 200) {
            echo "Error call. Status code " . $response->getStatusCode() . "\n";
        }

        $data = json_decode($response->getContent(), true);
        return new Details((int)$data['data']['numFreeBeers']);
    }

    public function admireTournament(string $id): void
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/game/admire?tournament_id=" . $id,
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
            echo "Error admire. Status code " . $response->getStatusCode() . "\n";
        }
    }

    public function currentSeason(): array
    {
        $response = $this->client->request(
            'GET',
            $this->url . "/season/current",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 200) {
            echo "Error season. Status code " . $response->getStatusCode() . "\n";
        }

        $data = json_decode($response->getContent(), true);

        return array_map(function (array $tournament) {
            return [
                'id' => $tournament['id'],
                'name' => match ($tournament['type']) {
                    3 => 'Tavern Fight',
                    4 => 'Adventure EXP',
                    5 => 'Boosters'
                }
            ];
        }, $data['data']['tournaments']);
    }

    public function findMatch(): ?string
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/pvp/find-match",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 201) {
            echo "Error call. Status code " . $response->getStatusCode() . "\n";
        }

        $data = json_decode($response->getContent(), true);

        if (isset($data['msg']) && $data['msg'] === 'PVP_MATCHES_LIMIT_EXCEEDED') {
            return null;
        }

        return $data['data']['matchUp']['id'];
    }

    public function battlePvP(): array
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/pvp/battle9x9",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 201 || $response->getStatusCode() !== 200) {
            echo "Error call. Status code " . $response->getStatusCode() . "\n";
        }

        return json_decode($response->getContent(), true);
    }

    public function getInventory(): Inventory
    {
        $potionItemId = '87f03c4e-596e-44a9-b1fb-8de42a256b4c';
        $rationItemId = 'fa13abbc-2eb8-4f38-afdc-ae00d8e79325';
        $stimItemId = '83eb57cc-a4d2-475a-98c7-b02d71134958';
        $crowId = 'fc51eb89-23ca-4c86-bdfa-81632e938278';
        $beerId = '561b901b-8b33-4b29-b2b6-e15d674fafd3';

        $response = $this->client->request(
            'GET',
            $this->url . "/inventory",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 200) {
            echo "Error call. Status code " . $response->getStatusCode() . "\n";
        }

        $data = json_decode($response->getContent(), true);

        $items = $data['data']['user_items'];

        $potionCnt = 0;
        $stimCnt = 0;
        $rationCnt = 0;
        $crowCnt = 0;
        $beerCnt = 0;
        foreach ($items as $item) {
            if ($item['item_id'] == $potionItemId) {
                $potionCnt = (int)$item['quantity'];
            } elseif ($item['item_id'] == $stimItemId) {
                $stimCnt = (int)$item['quantity'];
            } elseif ($item['item_id'] == $rationItemId) {
                $rationCnt = (int)$item['quantity'];
            } elseif ($item['item_id'] == $crowId) {
                $crowCnt = (int)$item['quantity'];
            } elseif ($item['item_id'] == $beerId) {
                $beerCnt = (int)$item['quantity'];
            }
        }

        return new Inventory($rationCnt, $stimCnt, $potionCnt, $crowCnt, $beerCnt);
    }

    public function healPepper(string $pepperId): bool
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/inventory/hp/use",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token,
                    'content-type' => 'application/json'
                ], $this->headers),
                'json' => [
                    'pepper_id' => $pepperId,
                    'to_max' => false
                ]
            ]
        );

        if ($response->getStatusCode() !== 201) {
            echo "Error heal peppers. Status code " . $response->getStatusCode() . "\n";
            return false;
        }
        return true;
    }

    public function healPeppers(array $peppers): bool
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/inventory/hp/use-for-team",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token,
                    'content-type' => 'application/json'
                ], $this->headers),
                'json' => [
                    'peppers' => $peppers
                ]
            ]
        );

        if ($response->getStatusCode() !== 201 && $response->getStatusCode() !== 200) {
            echo "Error heal peppers. Status code " . $response->getStatusCode() . "\n";
            return false;
        }
        return true;
    }

    public function getPeppers(bool $baseStats = false): array
    {
        $response = $this->client->request(
            'GET',
            $this->url . "/peppers/my-peppers",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 200) {
            echo "Error get peppers. Status code " . $response->getStatusCode() . "\n";
        }

        $data = json_decode($response->getContent(), true);

        $peppersRawData = $data['data']['peppers'];

        $peppers = [];
        foreach ($peppersRawData as $pepper) {
            $peppers[] = new Pepper(
                $pepper['pepper_id'],
                $pepper['current_hp'],
                Pepper::calculateMaxHP((int)$pepper['pepper']['pepper_info']['vit'], (int)$pepper['temp_vit']),
                $pepper['pepper']['pepper_info']['character'],
                $baseStats ? (int)$pepper['pepper']['pepper_info']['atk'] : (int)$pepper['boosted_atk'],
                $baseStats ? (int)$pepper['pepper']['pepper_info']['def'] : (int)$pepper['boosted_def'],
                $baseStats ? (int)$pepper['pepper']['pepper_info']['crit'] : (int)$pepper['boosted_crit'],
                $baseStats ? (int)$pepper['pepper']['pepper_info']['eva'] : (int)$pepper['boosted_eva'],
                $baseStats ? (int)$pepper['pepper']['pepper_info']['vit'] : (int)$pepper['boosted_vit'],
                $baseStats ? (int)$pepper['pepper']['pepper_info']['enr'] : (int)$pepper['boosted_enr'],
                (int)$pepper['boosted_num']
            );
        }
        return $peppers;
    }

    public function battlePvE(int $mapId, int $stageId): array
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/pve/battle9x9",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token,
                    'content-type' => 'application/json'
                ], $this->headers),
                'json' => [
                    'map_id' => $mapId,
                    'stage_id' => $stageId
                ]
            ]
        );

        if ($response->getStatusCode() !== 201) {
            echo "Error call battle. Status code " . $response->getStatusCode() . "\n";
        }

        return json_decode($response->getContent(), true);
    }

    public function getDailyQuests(): array
    {
        $response = $this->client->request(
            'GET',
            $this->url . "/daily-quest/all",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 200) {
            echo "Error get peppers. Status code " . $response->getStatusCode() . "\n";
        }

        $data = json_decode($response->getContent(), true);

        return $data['data']['dailyQuests'];
    }

    public function claimDailyQuests(array $dailyQuests): void
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/daily-quest/claim/quest",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token,
                    'content-type' => 'application/json'
                ], $this->headers),
                'json' => [
                    'quest_ids' => $dailyQuests
                ]
            ]
        );

        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
            echo "Error claim daily quest. Status code " . $response->getStatusCode() . "\n";
            print_r($response->getContent());
        }
    }

    public function claimRewards(int $level): void
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/daily-quest/claim/tier",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token,
                    'content-type' => 'application/json'
                ], $this->headers),
                'json' => [
                    'tier_level' => (string)$level
                ]
            ]
        );

        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
            echo "Error claim rewards. Status code " . $response->getStatusCode() . "\n";
        }
    }

    public function getMyRank(string $tournament): int
    {
        $response = $this->client->request(
            'GET',
            $this->url . "/leaderboard/tournament/" . $tournament,
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 200) {
            echo "Error get peppers. Status code " . $response->getStatusCode() . "\n";
        }

        $data = json_decode($response->getContent(), true);

        return (int)$data['data']['myRank'];
    }

    public function treasureHuntRoll()
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/treasure-hunt/roll",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers)
            ]
        );

        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
            echo "Error roll. Status code " . $response->getStatusCode() . "\n";
        }

        return json_decode($response->getContent(), true);
    }

    public function useStim(string $pepperId, string $state): array
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/inventory/stim/use",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers),
                'json' => [
                    'pepper_id' => $pepperId,
                    'state' => $state
                ]
            ]
        );

        if ($response->getStatusCode() !== 201) {
            echo "Error get peppers. Status code " . $response->getStatusCode() . "\n";
        }

        return json_decode($response->getContent(), true);
    }

    public function setUpTeamPvP($data): array
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/pvp/team",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers),
                'json' => $data
            ]
        );

        if ($response->getStatusCode() !== 201) {
            echo "Error get peppers. Status code " . $response->getStatusCode() . "\n";
        }

        return json_decode($response->getContent(), true);
    }

    public function setUpTeamPvE(array $data): array
    {
        $response = $this->client->request(
            'POST',
            $this->url . "/pve/team",
            [
                'headers' => array_merge([
                    'authorization' => 'Bearer ' . $this->token
                ], $this->headers),
                'json' => $data
            ]
        );

        if ($response->getStatusCode() !== 201) {
            echo "Error get peppers. Status code " . $response->getStatusCode() . "\n";
        }

        return json_decode($response->getContent(), true);
    }
}
