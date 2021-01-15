<?php
declare(strict_types=1);

namespace Itineris\GFSendGridDynamicTemplateNotification;

use GFAPI;
use GFCommon;

class NotificationService
{
    public static function register(array $services): array
    {
        $addOn = AddOn::get_instance();

        $services[Plugin::SLUG] = [
            'label' => __('SendGrid Dynamic Template', 'gf-sendgrid-dynamic-template-notification'),
            'image' => $addOn->get_base_url() . '/images/icon.svg',
            'disabled' => ! SendGrid::isSavedApiKeyValid(),
            'disabled_message' => sprintf(
                esc_html__(
                    'You must %sauthenticate with SendGrid%s before sending emails using their service.',
                    'gf-sendgrid-dynamic-template-notification'
                ),
                "<a href='" . esc_url(admin_url('admin.php?page=gf_settings&subview=' . Plugin::SLUG)) . "'>",
                '</a>'
            ),
        ];

        return $services;
    }

    /**
     * If the notification "Email Service" setting is a match prepare to send the email.
     *
     * @param array  $email          The email properties.
     * @param string $message_format The message format, html, or text.
     * @param array  $notification   The Notification object which produced the current email.
     *
     * @return array
     */
    public static function maybeSendEmail(array $email, string $messageFormat, array $notification, array $entry): array
    {
        // If the notification is not assigned to this service or the service API is not initialized, return the email.
        if (Plugin::SLUG !== rgar($notification, 'service')) {
            return $email;
        }

        $addOn = AddOn::get_instance();

        // If the email has already been aborted, return the email.
        if ($email['abort_email']) {
            $addOn->log_debug(__METHOD__ . '(): Not sending email because the notification has already been aborted by another Add-On.');
            return $email;
        }

        // Do nothing if API key is invalid.
        if (! SendGrid::isSavedApiKeyValid()) {
            $addOn->log_debug(__METHOD__ . '(): Not sending email because the SendGrid api key is invalid.');
            return $email;
        }

        // Do nothing if template id is invalid.
        $templateId = NotificationSettings::getTemplateId($notification);
        if (! SendGrid::isTemplateIdValid($templateId)) {
            $addOn->log_debug(__METHOD__ . '(): Not sending email because the SendGrid dynamic template ID is invalid.');
            return $email;
        }

        $isSent = static::sendEmail($email, $notification, $entry);
        if ($isSent) {
            $addOn->add_note(
                $entry['id'],
                $notification['name'] . ' has been sent with SendGrid dynamic template ' . $templateId,
                'success'
            );
        } else {
            $addOn->add_note(
                $entry['id'],
                'Failed to send ' . $notification['name'] . ' with SendGrid dynamic template ' . $templateId,
                'error'
            );
        }

        // Prevent WordPress and other add-ons from also sending the email.
        $email['abort_email'] = true;

        return $email;
    }

    public static function sendEmail(array $email, array $notification, array $entry): bool
    {
        $dynamicTemplateData = NotificationSettings::getDynamicTemplateData($entry, $notification);

        // Get form object.
        $form = GFAPI::get_form($entry['form_id']);

        $fromEmail = '';
        // Get from email address from email header.
        preg_match('/<(.*)>/', $email['headers']['From'], $fromEmail);

        // Prepare email for SendGrid.
        $emailData = [
            'template_id' => NotificationSettings::getTemplateId($notification),
            'from' => [
                'email' => $fromEmail[1],
                'name' => GFCommon::replace_variables(
                    rgar($notification, 'fromName'),
                    $form,
                    $entry,
                    false,
                    false,
                    false,
                    'text'
                ),
            ],
            'personalizations' => [
                [
                    'dynamic_template_data' => $dynamicTemplateData,
                ],
            ],
        ];

        $to_emails = array_map('trim', explode(',', $email['to']));
        foreach ($to_emails as $to_email) {
            $emailData['personalizations'][0]['to'][] = ['email' => $to_email];
        }

        // Add BCC.
        if (rgar($notification, 'bcc')) {
            $bcc_emails = GFCommon::replace_variables(
                rgar($notification, 'bcc'),
                $form,
                $entry,
                false,
                false,
                false,
                'text'
            );
            $bcc_emails = array_map('trim', explode(',', $bcc_emails));
            foreach ($bcc_emails as $bcc_email) {
                $emailData['personalizations'][0]['bcc'][] = ['email' => $bcc_email];
            }
        }

        // Add Reply To.
        if (rgar($notification, 'replyTo')) {
            $emailData['reply_to']['email'] = GFCommon::replace_variables(
                rgar($notification, 'replyTo'),
                $form,
                $entry,
                false,
                false,
                false,
                'text'
            );
        }

        // Add attachments.
        if (! empty($email['attachments'])) {
            // Loop through notification attachments, add to SendGrid email.
            foreach ($email['attachments'] as $attachment) {
                $emailData['attachments'][] = [
                    'content' => base64_encode(file_get_contents($attachment)),
                    'filename' => basename($attachment),
                ];
            }
        }

        return SendGrid::sendEmail($emailData);
    }
}
