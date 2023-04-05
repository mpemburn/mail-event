<?php
/**
 * @package MailEvent
 * @version 1.0.0
 */
/*
Plugin Name: MailEvent
Plugin URI:
Description: Triggers SendGrid to send message when new The Events Calendar event is published.
Author: Mark Pemburn
Version: 1.0.0
Author URI:
*/

namespace MailEvent;

require_once __DIR__ . '/vendor/autoload.php';

use MailEvent\AdminPage;

AdminPage::boot();
