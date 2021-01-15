<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification;

use GFAddOn;

/**
 * Avoid adding code in this class!
 *
 * @see https://docs.gravityforms.com/gfaddon/
 * @see https://docs.gravityforms.com/gffeedaddon/
 */
class AddOn extends GFAddOn
{
    /**
     * @var ?self $_instance If available, contains an instance of this class.
     */
    private static $_instance = null;
    protected $_version = Plugin::VERSION;
    protected $_min_gravityforms_version = MinimumRequirements::GRAVITY_FORMS_VERSION;
    protected $_slug = Plugin::SLUG;
    protected $_path = 'gf-sendgrid-dynamic-template-notification/gf-sendgrid-dynamic-template-notification.php';
    protected $_full_path = __FILE__;
    protected $_title = 'GravityForms SendGrid Dynamic Template Notification';
    protected $_short_title = 'GF SendGrid Dynamic Template Notification';

    /**
     * Returns an instance of this class, and stores it in the $_instance property.
     *
     * @return ?self $_instance An instance of this class.
     */
    public static function get_instance(): self
    {
        if (null === static::$_instance) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function minimum_requirements(): array
    {
        return MinimumRequirements::toArray();
    }

    public function plugin_settings_fields(): array
    {
        return PluginSettings::toArray();
    }

    public function init(): void
    {
        parent::init();

        if (! $this->is_gravityforms_supported()) {
            return;
        }

        add_filter('gform_notification_services', [NotificationService::class, 'register']);
        add_filter('gform_pre_send_email', [NotificationService::class, 'maybeSendEmail'], 19, 4);

        add_filter('gform_notification_settings_fields', [NotificationSettings::class, 'register'], 10, 2);
        add_filter('gform_pre_notification_save', [NotificationSettings::class, 'save']);
    }
}
