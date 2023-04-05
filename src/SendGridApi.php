<?php

namespace MailEvent;

use \GuzzleHttp\Client;

class SendGridApi
{
    const SENDGRID_BASE_URI = 'https://api.sendgrid.com/v3/';

    protected $apiKey;
    protected $headers;
    protected $client;

    public function __construct()
    {
        $this->apiKey = SENDGRID_API_KEY;
        $this->headers = ['headers' =>
            [
                'Authorization' => "Bearer {$this->apiKey}"
            ]
        ];
        $this->client = new Client();
    }

    public function getLists()
    {
        $response = $this->client->request(
            'GET',
            self::SENDGRID_BASE_URI . 'marketing/lists',
            $this->headers
        )->getBody()->getContents();

        return json_decode($response, true);
    }

    public function getContacts()
    {
        $response = $this->client->request(
            'GET',
            self::SENDGRID_BASE_URI . 'marketing/contacts',
            $this->headers
        )->getBody()->getContents();

        return json_decode($response, true);
    }
}
