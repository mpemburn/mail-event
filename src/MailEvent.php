<?php

namespace MailEvent;

class MailEvent
{
    private static $instance = null;

    private function __construct()
    {
        $this->addActions();
    }

    public static function boot()
    {
        if (!self::$instance) {
            self::$instance = new MailEvent();
        }

        return self::$instance;
    }

    public function addActions()
    {
        add_action('publish_post', [$this, 'onPublishPost']);
    }

    public function onPublishPost($id, $post)
    {
        if ($post->post_type !== 'tribe_event') { // && $post->post_date !== $post->post_modified) {
            return;
        }

        $contacts = (new SendGridApi())->getContacts();

        foreach ($contacts as $contact) {
            $event = tribe_get_event($post);
            $message = $this->parseMessage($contact, $event);
        }
    }

    protected function parseMessage($contact, $event): string
    {
        $message = get_option('mail_event_message');
        $vars = [
            '[name]' => $contact['first_name'],
            '[event_title]' => $event->post_title,
            '[event_date]' => $event->start_date,
            '[event_permalink]' => get_permalink($event),
        ];

        preg_match_all('/\[[\w_]+\]/', $message, $matches);
        foreach (current($matches) as $match) {
            $replace = $vars[$match];
            $message = str_replace($match, $replace, $message);
        }

        return $message;
    }
}
