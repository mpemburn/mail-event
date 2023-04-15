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
//        (new MailEvent())->onPublishPost(96, get_post(96));
        $this->getScript();
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
            table tr td {
                font-size: 14px;
            }
            input[type=text] {
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

    protected function getScript(): void
    {
        ob_start();
        ?>
        <script>
            jQuery(document).ready(function ($) {
                $('#mail_event_update').on('click', function () {
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: "/wp-admin/admin-ajax.php",
                        data: {
                            action: 'save_mail_event_settings',
                            template_id: $('input[name="mail_event_template_id"]').val(),
                            from_email: $('input[name="mail_event_from_email"]').val(),
                            from_name: $('input[name="mail_event_from_name"]').val()
                        },
                        success: function (data) {
                            console.log(data);
                        },
                        error: function (msg) {
                            console.log(msg);
                        }
                    });
                })
            });

        </script>
        <?php
        ob_end_flush();
    }

    protected function getMailEventOptions(): void
    {
        ob_start();
        ?>
        <h1>Settings</h1>
            <div class="settings">
                <label for="mail_event_template_id"><strong>Template ID:</strong></label>
                <input type="text" name="mail_event_template_id" value="<?php  echo get_option('mail_event_template_id'); ?>">
                <label for="mail_event_from_email"><strong>From Email:</strong></label>
                <input type="text" name="mail_event_from_email" value="<?php  echo get_option('mail_event_from_email'); ?>">
                <label for="mail_event_from_name"><strong>From Name:</strong></label>
                <input type="text" name="mail_event_from_name" value="<?php  echo get_option('mail_event_from_name'); ?>">

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
            </thead>
            <?php
            foreach ($contacts['result'] as $contact) {
                echo '<tr>';
                echo '<td>' . $contact['first_name'] . '</td>';
                echo '<td>' . $contact['last_name'] . '</td>';
                echo '<td>' . $contact['email'] . '</td>';
                echo '</tr>';
            }
            ?>
        </table>
        <?php
        ob_end_flush();
    }

}
