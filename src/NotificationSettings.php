<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification;

class NotificationSettings
{
    protected const TEMPLATE_ID_KEY = 'templateId';
    protected const DYNAMIC_TEMPLATE_KEY = 'dynamicTemplateData';

    public static function register(array $settings, $notification): array
    {
        $service = $notification['service'] ?? null;
        if (isset($_POST['_gform_setting_service'])) {
            $service = sanitize_text_field(wp_unslash($_POST['_gform_setting_service']));
        }

        if (Plugin::SLUG !== $service) {
            return $settings;
        }

        foreach ($settings as &$setting) {
            $setting['fields'] = array_filter($setting['fields'], function (array $field): bool {
                return ! in_array($field['name'], ['message', 'disableAutoformat'], true);
            });
        }
        unset($setting);

        $settings[] = [
            'title' => esc_html__(
                'SendGrid Dynamic Template Notification',
                'gf-sendgrid-dynamic-template-notification'
            ),
            'fields' => [
                [
                    // TODO: Use `validation_callback` to print error messages.
                    'type' => 'text',
                    'name' => static::TEMPLATE_ID_KEY,
                    'label' => esc_html__('Template ID', 'gf-sendgrid-dynamic-template-notification'),
                    'required' => true,
                    'feedback_callback' => function ($value): bool {
                        if (! is_string($value) || '' === $value) {
                            return false;
                        }
                        return SendGrid::isTemplateIdValid($value);
                    },
                    'class' => 'medium',
                ],
                [
                    'type' => 'dynamic_field_map',
                    'name' => static::DYNAMIC_TEMPLATE_KEY,
                    'label' => esc_html__('Dynamic Template Data', 'gf-sendgrid-dynamic-template-notification'),
                    'exclude_field_types' => 'creditcard',
                ],
            ],
        ];

        return $settings;
    }

    public static function save(array $notification): array
    {
        $notification[static::TEMPLATE_ID_KEY] = rgpost('_gform_setting_' . static::TEMPLATE_ID_KEY);
        $notification[static::DYNAMIC_TEMPLATE_KEY] = json_decode(rgpost('_gform_setting_' . static::DYNAMIC_TEMPLATE_KEY)) ?: [];

        return $notification;
    }

    public static function getTemplateId(array $notification): string
    {
        return $notification[static::TEMPLATE_ID_KEY] ?? '';
    }

    public static function getDynamicTemplateData(array $entry, array $notification): array
    {
        return static::getDynamicFieldMapValues($entry, $notification, static::DYNAMIC_TEMPLATE_KEY);
    }

    /**
     * Get mapped key/value pairs for dynamic field map for specific entry.
     *
     * @param array  $entry        Entry object.
     * @param array  $notification Notification object.
     * @param string $key          Dynamic field map field name.
     *
     * @return array
     */
    protected static function getDynamicFieldMapValues(array $entry, array $notification, string $key): array
    {
        $fields = static::getDynamicFieldMapFields($notification, $key);

        $values = [];
        foreach ($fields as $index => $field) {
            $values[$index] = $entry[$field] ?? null;
        }

        return array_filter($values);
    }

    /**
     * Get mapped key/value pairs for dynamic field map.
     *
     * @param array  $notification Notification object.
     * @param string $key          Dynamic field map field name.
     *
     * @return array
     */
    protected static function getDynamicFieldMapFields(array $notification, string $key): array
    {
        // Initialize return fields array.
        $fields = [];

        // Get dynamic field map field.
        $dynamicFields = rgars($notification, $key);

        // If dynamic field map field is found, loop through mapped fields and add to array.
        if (! empty($dynamicFields)) {
            // Loop through mapped fields.
            foreach ($dynamicFields as $dynamic_field) {
                // Get mapped key or replace with custom value.
                $field_key = 'gf_custom' === $dynamic_field['key'] ? $dynamic_field['custom_key'] : $dynamic_field['key'];
                // Add mapped field to return array.
                $fields[$field_key] = $dynamic_field['value'];
            }
        }

        return $fields;
    }
}
