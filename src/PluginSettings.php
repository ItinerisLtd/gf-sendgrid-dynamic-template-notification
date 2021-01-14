<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification;

class PluginSettings
{
    protected const SENDGRID_API_KEY_NAME = 'sendGridApiKey';

    public static function getApiKey(): string
    {
        $addOn = static::getAddOn();
        return (string) $addOn->get_plugin_setting(static::SENDGRID_API_KEY_NAME);
    }

    protected static function getAddOn(): AddOn
    {
        return AddOn::get_instance();
    }

    public static function toArray(): array
    {
        return [
            [
                'title' => esc_html__('SendGrid', 'gf-sendgrid-dynamic-template-notification'),
                'description' => sprintf(
                    '<p>%s</p>',
                    sprintf(
                        esc_html__('SendGrid makes it easy to reliably send email notifications. If you don\'t have a SendGrid account, you can %1$ssign up for one here%2$s. Once you have signed up, you can %3$sfind your API keys here%4$s.',
                            'gf-sendgrid-dynamic-template-notification'),
                        '<a href="https://sendgrid.com" target="_blank">', '</a>',
                        '<a href="https://app.sendgrid.com/settings/api_keys" target="_blank">', '</a>'
                    )
                ),
                'fields' => [
                    [
                        // TODO: Use `validation_callback` to print error messages.
                        'type' => 'text',
                        'name' => static::SENDGRID_API_KEY_NAME,
                        'label' => esc_html__('SendGrid API Key ', 'gf-sendgrid-dynamic-template-notification'),
                        'required' => true,
                        'input_type' => 'password',
                        'feedback_callback' => function ($value): bool {
                            if (! is_string($value) || '' === $value) {
                                return false;
                            }

                            return SendGrid::isApiKeyValid($value);
                        },
                        'class' => 'medium',
                    ],
                ],
            ],
        ];
    }
}
