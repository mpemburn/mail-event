<?php

namespace MailEvent;

class MailEvent
{
    public function addActions(): void
    {
        add_action('publish_post', [$this, 'onPublishPost']);
    }

    public function onPublishPost($id, $post): void
    {
        error_log('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
//        if ($post->post_type !== 'tribe_event') { // && $post->post_date !== $post->post_modified) {
//            return;
//        }

        $contacts = (new SendGridApi())->getContacts();

        foreach ($contacts['result'] as $contact) {
            if ($contact['email'] !== 'mark@pemburn.com') {
                continue;
            }
            $event = tribe_get_event($post);
            $message = $this->parseMessage($contact, $event);
            $subject = 'My Very First Event';
            $this->sendMail($contact, $subject, $message);
        }
    }

    protected function sendMail($contact, $subject, $message): void
    {
        (new SendGridApi())->sendEmail($contact, $subject, $message);
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
