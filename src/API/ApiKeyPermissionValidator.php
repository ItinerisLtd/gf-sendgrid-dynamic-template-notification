<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification\API;

class ApiKeyPermissionValidator
{
    use RemoteRequestArgsTrait;

    protected const ENDPOINT = 'https://api.sendgrid.com/v3/scopes';
    protected const REQUIRED_SCOPES = [
        'mail.send',
        'templates.read',
    ];

    public static function isValid(string $apiKey): bool
    {
        if ('' === $apiKey) {
            return false;
        }

        $response = wp_remote_get(
            static::ENDPOINT,
            static::getRemoteRequestArgs($apiKey)
        );

        $statusCode = wp_remote_retrieve_response_code($response);
        if (200 !== $statusCode) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true) ?: [];

        $scopes = $data['scopes'] ?? [];
        $missingScopes = array_diff(static::REQUIRED_SCOPES, $scopes);

        return [] === $missingScopes;
    }
}
