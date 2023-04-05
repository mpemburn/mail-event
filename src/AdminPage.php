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
        add_action('wp_ajax_nopriv_save_mail_message', [$this, 'saveMailMessage']);
        add_action('wp_ajax_save_mail_message', [$this, 'saveMailMessage']);
    }

    public function saveMailMessage(): void
    {
        $message = $_REQUEST['mail_event_message'] ?? null;

        if ($message) {
            update_option('mail_event_message', $message);
        }

        wp_send_json([
                'success' => true,
                'message' => $message
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
        $this->getScript();
        $this->getStyle();
        echo '<div class="container">';

        $this->getMailMessage();
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
            table tr td {
                font-size: 14px;
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
                $('#update_message').on('click', function () {
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: "/wp-admin/admin-ajax.php",
                        data: {
                            action: 'save_mail_message',
                            mail_event_message: tinymce.activeEditor.getContent()
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

    protected function getMailMessage(): void
    {
        ob_start();
        ?>
        <h1>Message</h1>
        <?php
            $content = get_option('mail_event_message');
            wp_editor($content, 'mail_event_message', ['textarea_rows' => '10']);
        ?>
        <button id="update_message" class="button thickbox">Update</button>
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
