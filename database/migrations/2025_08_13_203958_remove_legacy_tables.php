<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        $tables = [
            'accordions',
            'accordion_types',
            'architect_settings',
            'daily_update_types',
            'daily_updates_queue',
            'featured_images',
            'image_associations',
            'image_categories',
            'images',
            'login_attempts',
            'mailcoach_automation_action_subscriber',
            'mailcoach_automation_actions',
            'mailcoach_automation_mail_clicks',
            'mailcoach_automation_mail_links',
            'mailcoach_automation_mail_opens',
            'mailcoach_automation_mail_unsubscribes',
            'mailcoach_automation_mails',
            'mailcoach_automation_triggers',
            'mailcoach_automations',
            'mailcoach_campaign_clicks',
            'mailcoach_campaign_links',
            'mailcoach_campaign_opens',
            'mailcoach_campaign_unsubscribes',
            'mailcoach_campaigns',
            'mailcoach_email_list_allow_form_subscription_tags',
            'mailcoach_email_list_subscriber_tags',
            'mailcoach_email_lists',
            'mailcoach_mailers',
            'mailcoach_negative_segment_tags',
            'mailcoach_positive_segment_tags',
            'mailcoach_segments',
            'mailcoach_send_feedback_items',
            'mailcoach_sends',
            'mailcoach_settings',
            'mailcoach_subscriber_imports',
            'mailcoach_subscribers',
            'mailcoach_tags',
            'mailcoach_templates',
            'mailcoach_transactional_mail_clicks',
            'mailcoach_transactional_mail_log_items',
            'mailcoach_transactional_mail_opens',
            'mailcoach_transactional_mails',
            'mailcoach_uploads',
            'mailcoach_webhook_configuration_email_lists',
            'mailcoach_webhook_configurations',
            'password_resets',
            'place_requests',
            'scrapbook_items',
            'scrapbooks',
            'user_daily_update_subscriptions',
            'user_levels',
            'user_password_changes',
            'webhook_calls',
        ];

        Schema::disableForeignKeyConstraints();

        foreach($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }
};
