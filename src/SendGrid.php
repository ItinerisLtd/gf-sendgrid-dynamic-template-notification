<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification;


use Itineris\GFSendGridDynamicTemplateNotification\API\ApiKeyPermissionValidator;
use Itineris\GFSendGridDynamicTemplateNotification\API\Mailer;
use Itineris\GFSendGridDynamicTemplateNotification\API\TemplateIdValidator;

class SendGrid
{
    public static function isSavedApiKeyValid(): bool
    {
        return static::isApiKeyValid(
            PluginSettings::getApiKey()
        );
    }

    public static function isApiKeyValid(string $apiKey): bool
    {
        return ApiKeyPermissionValidator::isValid($apiKey);
    }

    public static function isTemplateIdValid(string $id): bool
    {
        return TemplateIdValidator::isValid(
            $id,
            PluginSettings::getApiKey()
        );
    }

    public static function sendEmail(array $data): bool
    {
        return Mailer::send(
            $data,
            PluginSettings::getApiKey()
        );
    }
}
