<?php

namespace MailEvent;

class AdminPage
{
    private static $instance = null;
    protected $listTable;
    protected $api;

    private function __construct()
    {
        $this->api = new SendGridApi();

        $this->loadScript();

        $this->addActions();
    }

    public static function boot()
    {
        if (!self::$instance) {
            self::$instance = new AdminPage();
        }

        return self::$instance;
    }

    protected function addActions(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('wp_ajax_nopriv_save_mail_event_settings', [$this, 'saveMailEventSettings']);
        add_action('wp_ajax_save_mail_event_settings', [$this, 'saveMailEventSettings']);
        add_action('wp_ajax_nopriv_add_or_remove_email', [$this, 'addOrRemoveEmail']);
        add_action('wp_ajax_add_or_remove_email', [$this, 'addOrRemoveEmail']);
    }

    public function saveMailEventSettings(): void
    {
        $templateId = $_REQUEST['template_id'] ?? null;

        if ($templateId) {
            update_option('mail_event_template_id', $templateId);
        }

        $fromEmail = $_REQUEST['from_email'] ?? null;

        if ($fromEmail) {
            update_option('mail_event_from_email', $fromEmail);
        }

        $fromName = $_REQUEST['from_name'] ?? null;

        if ($fromName) {
            update_option('mail_event_from_name', $fromName);
        }

        wp_send_json([
                'success' => true,
                'template_id' => $templateId
        ]);

        die();
    }

    public function addOrRemoveEmail(): void
    {
        $email = $_REQUEST['email'] ?? null;

        wp_send_json([
            'success' => true,
            'email' => $email
        ]);
        die();
    }

    public function addMenuPage(): void
    {
        add_menu_page(
            __('Mail Event', 'uri'),
            'Mail Event',
            'switch_themes',
            'mail-event',
            [$this, 'showListPage'],
            'dashicons-admin-tools',
            10
        );
    }

    public function showListPage(): void
    {
        $this->getStyle();
        echo '<div class="container">';

        $this->getMailEventOptions();
        $this->getContactList();

        echo '</div>';
    }

    protected function getStyle(): void
    {
        ob_start();
        ?>
        <style>
            div.container {
                max-width: 90%;
                margin-top: 2rem;
            }
            div.settings {
                width: 20rem;
            }
            table tr {
                background-color: #fefffe;
            }
            table tr td {
                font-size: 16px;
                padding: 5px 20px;
            }
            input[type=text].settings {
                display: table;
                min-width: 20rem;
            }
            #mail_event_update {
                margin-top: 5px;
                text-align: right;
            }
        </style>
        <?php
        ob_end_flush();
    }

    protected function loadScript(): void
    {
        $file = plugin_dir_path(__FILE__) . 'js/mail_event.js';
        $cacheBuster = filemtime($file);
        wp_register_script('mail_event', plugins_url('/js/mail_event.js', __FILE__), array(), $cacheBuster, true);
        wp_enqueue_script('mail_event');
    }

    protected function getMailEventOptions(): void
    {
        ob_start();
        ?>
        <h1>Settings</h1>
            <div class="settings">
                <label for="mail_event_template_id"><strong>Template ID:</strong></label>
                <input type="text" class="settings" name="mail_event_template_id" value="<?php  echo get_option('mail_event_template_id'); ?>">
                <label for="mail_event_from_email"><strong>From Email:</strong></label>
                <input type="text" class="settings" name="mail_event_from_email" value="<?php  echo get_option('mail_event_from_email'); ?>">
                <label for="mail_event_from_name"><strong>From Name:</strong></label>
                <input type="text" class="settings" name="mail_event_from_name" value="<?php  echo get_option('mail_event_from_name'); ?>">

                <button id="mail_event_update" class="button thickbox">Update</button>
            </div>
        <?php
        ob_end_flush();
    }

    protected function getContactList(): void
    {
        $contacts = $this->api->getContacts();

        ob_start();
        ?>
        <h1>Event Subscribers</h1>
        <table>
            <thead>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th></th>
            </thead>
            <tbody>
                <td><input type="text" name="new_first_name"></td>
                <td><input type="text" name="new_last_name"></td>
                <td><input type="text" name="new_email"></td>
                <td><button class="button" data-email="">Add</button></td>
            </tbody>
            <?php
            foreach ($contacts['result'] as $contact) {
                echo '<tr>';
                echo '<td class="contact">' . $contact['first_name'] . '</td>';
                echo '<td class="contact">' . $contact['last_name'] . '</td>';
                echo '<td class="contact">' . $contact['email'] . '</td>';
                echo '<td><button data-email="' . $contact['email'] . '" class="button">Remove</button></td>';
                echo '</tr>';
            }
            ?>
        </table>
        <?php
        ob_end_flush();
    }

}
