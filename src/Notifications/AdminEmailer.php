<?php

namespace AskMeAnything\Notifications;

/**
 * AdminEmailer.php
 *
 * @package   ask-me-anything
 * @copyright Copyright (c) 2026, Ashley Gibson
 * @license   MIT
 */
class AdminEmailer
{
    public function send(\AMA_Question $question): bool
    {
        $adminEmails = $this->getEmails();
        if (empty($adminEmails)) {
            return false;
        }

        $subject = sprintf(__('New Question: %s', 'ask-me-anything'), wp_strip_all_tags($question->get_title()));
        $message = sprintf(
            __("A new question has been posted on your site.\n\n%s\n\nView: %s\nEdit: %s", 'ask-me-anything'),
            $question->post_content,
            get_permalink($question->ID),
            admin_url('post.php?post='.$question->ID.'&action=edit')
        );

        return wp_mail($adminEmails, $subject, $message);
    }

    protected function getEmails(): array
    {
        $adminEmailString = ask_me_anything_get_option('admin_email');
        if (empty($adminEmailString)) {
            return [];
        }

        $adminEmails = explode(',', $adminEmailString);
        if (! is_array($adminEmails)) {
            return [];
        }

        $adminEmails = array_map('trim', $adminEmails);

        return array_values(
            array_filter(
                $adminEmails,
                function ($email) {
                    return ! empty($email) && is_email($email);
                }
            )
        );
    }
}
