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

        return json_decode($response, true)['result'];
    }

    public function createContact(string $email, string $firstName, string $lastName): ?string
    {
        $body = '{
          "contacts": [
            {
              "email": "'. $email . '",
              "first_name": "'. $firstName . '",
              "last_name": "'. $lastName . '"
            }
          ]
        }';

        $request = new Request(
            'PUT',
            self::SENDGRID_BASE_URI . 'marketing/contacts',
            $this->headers,
            $body
        );

        $response = $this->client->sendAsync($request)->wait();

        return ($response) ? $response->getBody() : null;
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

    public function getContactByEmail(string $email): ?string
    {
        $body = '{
            "emails": ["' . $email . '"]
        }';

        $request = new Request(
            'POST',
            self::SENDGRID_BASE_URI . 'marketing/contacts/search/emails',
            $this->headers,
            $body
        );

        $response = $this->client->sendAsync($request)->wait();

        return ($response) ? $response->getBody() : null;
    }

    public function deleteContactById(string $contactId): ?string
    {
        $request = new Request(
            'DELETE',
            self::SENDGRID_BASE_URI . '/marketing/contact?ids=' . $contactId,
            $this->headers
        );

        $response = $this->client->sendAsync($request)->wait();

        return ($response) ? $response->getBody() : null;
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

        return $this->client->sendAsync($request)->wait();

    }
}
