<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification\API;

class TemplateIdValidator
{
    use RemoteRequestArgsTrait;

    protected const ENDPOINT = 'https://api.sendgrid.com/v3/templates';

    public static function isValid(string $id, string $apiKey): bool
    {
        if ('' === $id) {
            return false;
        }

        if ('' === $apiKey) {
            return false;
        }

        $response = wp_remote_get(
            static::ENDPOINT . '/' . $id,
            static::getRemoteRequestArgs($apiKey)
        );

        $statusCode = wp_remote_retrieve_response_code($response);
        return 200 === $statusCode;
    }
}
