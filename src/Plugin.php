<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification;

use GFAddOn;
use GFForms;

class Plugin
{
    public const VERSION = '0.1.1';
    public const SLUG = 'gf-sendgrid-dynamic-template-notification';

    public static function run(): void
    {
        if (! method_exists(GFForms::class, 'include_addon_framework')) {
            // TODO: Display warnings.
            return;
        }

        if (! method_exists(GFAddOn::class, 'register')) {
            // TODO: Display warnings.
            return;
        }

        GFForms::include_addon_framework();
        GFAddOn::register(AddOn::class);
    }
}
