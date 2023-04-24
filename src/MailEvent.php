<?php

namespace MailEvent;

class MailEvent
{
    private static $instance = null;

    private function __construct()
    {
        $this->api = new SendGridApi();

        $this->addActions();
    }

    public static function boot()
    {
        if (!self::$instance) {
            self::$instance = new MailEvent();
        }

        return self::$instance;
    }
    public function addActions(): void
    {
        add_action( 'draft_to_publish', [$this, 'onPublishPost'], 10, 1 );
        add_action( 'pending_to_publish', [$this, 'onPublishPost'], 10, 1 );
    }

    public function onPublishPost($post): void
    {
        if ($post->post_type !== 'tribe_events') {
            return;
        }

        $contacts = (new SendGridApi())->getContacts();

        foreach ($contacts['result'] as $contact) {
            $event = tribe_get_event($post);
            $this->sendMail($contact, $event);
        }
    }

    protected function sendMail($contact, $event): void
    {
        $mailData = (new MailData())
            ->setTemplateId(get_option('mail_event_template_id'))
            ->setFromName(get_option('mail_event_from_email'), get_option('mail_event_from_name'))
            ->setContact($contact)
            ->setEvent($event);

        (new SendGridApi())->sendEmail($mailData);
    }
}
