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
        if ($post->post_type !== 'tribe_event' && $post->post_date !== $post->post_modified) {
            return;
        }


    }

    protected function parseMessage($contact, $post): string
    {
        $message = get_option('mail_event_message');
        $vars = [
            '[name]' => $contact['first_name'],
            '[event_title]' => $post->post_title,
            '[event_date]' => '2023-04-04 15:30',
            '[event_permalink]' => $post->post_permalink,
        ];

        preg_match_all('/\[[\w_]+\]/', $message, $matches);
        foreach (current($matches) as $match) {
            $replace = $vars[$match];
            $message = str_replace($match, $replace, $message);
        }

        return $message;
    }
}
