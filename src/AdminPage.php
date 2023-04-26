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

        update_option('mail_event_list_id', $_REQUEST['list_id']);

        wp_send_json([
            'success' => true,
            'template_id' => $templateId
        ]);

        die();
    }

    public function addOrRemoveEmail(): void
    {
        $success = false;

        $action = $_REQUEST['button_action'] ?? null;
        if ($action) {
            $email = $_REQUEST['email'];
            $firsName = $_REQUEST['first_name'];
            $lastName = $_REQUEST['last_name'];
            if ($action === 'add') {
                // Create new contact
                $contactId = $this->api->createContact($email, $firsName, $lastName);
                if ($contactId) {
                    // Added to event subscribers list
                    $this->api->addContactToList($email, get_option('mail_event_list_id'));
                }

            }

            if ($action === 'remove') {
                $contactId = ($_REQUEST['contact_id']);
                $this->api->deleteContactById($contactId);
            }
        }

        wp_send_json([
            'success' => $success,
            'result' => $_REQUEST
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

            input[type=text].new-email {
                width: 20rem;
            }

            input[type=text].settings,
            select.settings {
                display: table;
                min-width: 20rem;
            }

            button.add-button {
                width: 72px;
            }

            #mail_event_update {
                margin-top: 5px;
                text-align: right;
            }

            #error_message {
                display: none;
                font-size: 10pt;
                color: #c00;
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
            <label for="mail_event_from_email"><strong>From Email:</strong></label>
            <input type="text" class="settings" name="mail_event_from_email"
                   value="<?php echo get_option('mail_event_from_email'); ?>">
            <label for="mail_event_from_name"><strong>From Name:</strong></label>
            <input type="text" class="settings" name="mail_event_from_name"
                   value="<?php echo get_option('mail_event_from_name'); ?>">
            <label for="mail_event_list_name"><strong>SendGrid List Name:</strong></label><br>

            <select name="mail_event_list_id" class="settings">
                <option value="">Select List</option>
                <?php
                $lists = $this->api->getLists();
                $current = get_option('mail_event_list_id');
                foreach ($lists as $list) {
                    $selected = $list['id'] === $current ? 'selected' : '';
                    echo '<option value="' . $list['id'] . '" ' . $selected . '>' . $list['name'] . '</option>';
                }
                ?>
            </select>

            <label for="mail_event_template_id"><strong>SendGrid Template ID:</strong></label>
            <input type="text" class="settings" name="mail_event_template_id"
                   value="<?php echo get_option('mail_event_template_id'); ?>">

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
            <tbody>
            <tr>
                <td><input type="text" name="new_first_name" placeholder="First Name"></td>
                <td><input type="text" name="new_last_name" placeholder="Last Name"></td>
                <td><input type="text" name="new_email" class="new-email" placeholder="Enter a valid email address"></td>
                <td>
                    <button class="button add-button" disabled>Add</button>
                </td>
            </tr>
            <tr>
                <td colspan="4" id="error_message"></td>
            </tr>
            </tbody>
            <?php
            $contactList = $contacts['result'];
            usort($contactList, function($a, $b) {
                return $a['last_name'] <=> $b['last_name'];
            });

            foreach ($contactList as $contact) {
                echo '<tr>';
                echo '<td class="contact">' . $contact['first_name'] . '</td>';
                echo '<td class="contact">' . $contact['last_name'] . '</td>';
                echo '<td class="contact email">' . $contact['email'] . '</td>';
                echo '<td><button data-id="' . $contact['id'] . '" class="button">Remove</button></td>';
                echo '</tr>';
            }
            ?>
        </table>
        <?php
        ob_end_flush();
    }

}
