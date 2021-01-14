<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification;

class MinimumRequirements
{
    public const GRAVITY_FORMS_VERSION = '2.5-beta-3';

    public static function toArray(): array
    {
        return [
            'wordpress' => [
                'version' => '5.6',
            ],
            'php' => [
                'version' => '7.4',
            ],
        ];
    }
}
