<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification\API;

class Mailer
{
    use RemoteRequestArgsTrait;

    const ENDPOINT = 'https://api.sendgrid.com/v3/mail/send';

    public static function send(array $data, string $apiKey): bool
    {
        if ([] === $data) {
            return false;
        }

        if ('' === $apiKey) {
            return false;
        }

        $response = wp_remote_post(
            static::ENDPOINT,
            array_merge(
                static::getRemoteRequestArgs($apiKey),
                [
                    'body' => json_encode($data),
                ]
            )
        );

        $statusCode = wp_remote_retrieve_response_code($response);

        return 202 === $statusCode;
    }
}
