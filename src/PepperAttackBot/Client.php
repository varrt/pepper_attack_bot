<?php
declare(strict_types=1);

namespace PepperAttackBot;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;

class Client
{
    private string $token;
    private HttpClientInterface $client;
    private string $url = 'https://api.pepperattack.com';


    public function __construct() {
        $this->client = HttpClient::createForBaseUri($this->url);
    }

    public function login(string $email, string $password): void
    {
        $response = $this->client->request(
            'POST',
            $this->url."/auth/login",
            [
                'json' => [
                    'email' => $email,
                    'password' => $password
                ]
            ]

        );
        $data = json_decode($response->getContent(), true);
        $this->token = $data['data']['token'];
    }

    public function collectRation(): void
    {
        $this->client->request(
            'POST',
            $this->url."/inventory/ration/charge",
            [
                'headers' => [
                    'authorization' => 'Bearer '. $this->token
                ]
            ]
        );
    }

}
