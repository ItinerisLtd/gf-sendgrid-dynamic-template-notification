<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification\API;

trait RemoteRequestArgsTrait
{
    protected static function getRemoteRequestArgs(string $apiKey): array
    {
        return [
            'timeout' => 10,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => [],
        ];
    }
}
