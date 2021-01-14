<?php

/**
 * Plugin Name:       GravityForms SendGrid Dynamic Template Notification
 * Plugin URI:        https://github.com/itinerisltd/gf-sendgrid-dynamic-template-notification
 * Description:       Send GravityForms notifications via SendGrid dynamic transactional templates.
 * Version:           0.1.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Itineris Limited
 * Author URI:        https://www.itineris.co.uk/
 * Text Domain:       gf-sendgrid-dynamic-template-notification
 */

declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification;

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

add_action('gform_loaded', [Plugin::class, 'run'], 5);
