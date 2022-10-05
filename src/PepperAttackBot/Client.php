<?php
declare(strict_types=1);

namespace PepperAttackBot;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use PepperAttackBot\Model\Details;

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
            echo "Error login. Status code " . $response->getStatusCode(). "\n";
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
            echo "Error collect. Status code " . $response->getStatusCode(). "\n";
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
            echo "Error collect. Status code " . $response->getStatusCode(). "\n";
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

        if ($response->getStatusCode() !== 200) {
            echo "Error collect. Status code " . $response->getStatusCode(). "\n";
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
            echo "Error collect. Status code " . $response->getStatusCode() . "\n";
        }

        $data = json_decode($response->getContent(), true);

        return array_map(function (array $tournament) {
            return $tournament['id'];
        }, $data['data']['tournaments']);
    }
}
