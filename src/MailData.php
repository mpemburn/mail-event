<?php

namespace MailEvent;

class MailData
{
    protected ?string $templateId = null;
    protected ?string $fromEmail = null;
    protected ?string $fromName = null;
    protected ?string $toEmail = null;
    protected ?string $firstName = null;
    protected ?string $fullName = null;
    protected ?string $eventTitle = null;
    protected ?string $eventDate = null;
    protected ?string $eventPermalink = null;

    public function setTemplateId(?string $templateId): self
    {
        if (! $templateId) {
            return $this;
        }

        $this->templateId = $templateId;

        return $this;
    }

    public function setContact(?array $contact): self
    {
        if (! $contact) {
            return $this;
        }

        $this->toEmail = $contact['email'];
        $this->firstName = $contact['first_name'];
        $this->fullName = trim($contact['first_name'] . ' ' . $contact['last_name']);

        return $this;
    }

    public function setEvent(?\WP_Post $event): self
    {
        if (! $event) {
            return $this;
        }

        $this->eventTitle = $event->post_title;
        $this->eventDate = date('F jS, Y', strtotime($event->start_date));
        $this->eventPermalink = get_permalink($event);

        return $this;
    }

    public function setFromName(?string $fromEmail, ?string $fromName): self
    {
        if (! $fromEmail && ! $fromName) {
            return $this;
        }

        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;

        return $this;
    }

    public function build(): string
    {
        return '{
          "from": {
            "email": "' . $this->fromEmail . '",
            "name": "' . $this->fromName . '"
          },
          "personalizations": [
            {
              "to": [
                {
                  "email": "' . $this->toEmail . '",
                  "name": "' . $this->fullName . '"
                }
              ],
              "dynamic_template_data": {
                "first_name": "' . $this->firstName . '",
                "event_date": "' . $this->eventDate . '",
                "event_title": "' . $this->eventTitle . '",
                "event_permalink": "' . $this->eventPermalink . '"
              }
            }
          ],
          "template_id": "' . $this->templateId . '"
        }';
    }
}
