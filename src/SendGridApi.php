<?php

namespace MailEvent;

use \GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class SendGridApi
{
    const SENDGRID_BASE_URI = 'https://api.sendgrid.com/v3/';

    protected $apiKey;
    protected $headers;
    protected $client;

    public function __construct()
    {
        $this->apiKey = SENDGRID_API_KEY;
        $this->headers = [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json'
            ];
        $this->client = new Client();
    }

    public function getLists()
    {
        $response = $this->client->request(
            'GET',
            self::SENDGRID_BASE_URI . 'marketing/lists',
            ['headers' => $this->headers]
        )->getBody()->getContents();

        return json_decode($response, true);
    }

    public function getContacts()
    {
        $response = $this->client->request(
            'GET',
            self::SENDGRID_BASE_URI . 'marketing/contacts',
            ['headers' => $this->headers]
        )->getBody()->getContents();

        return json_decode($response, true);
    }

    public function sendEmail(MailData $mailData)
    {
        $body = $mailData->build();

        $request = new Request(
            'POST',
            self::SENDGRID_BASE_URI . 'mail/send',
            $this->headers,
            $body
        );

        $response = $this->client->sendAsync($request)->wait();

    }
}
