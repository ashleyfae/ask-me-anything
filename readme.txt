=== Ask Me Anything ===
Contributors: NoseGraze
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=L2TL7ZBVUMG9C
Requires at least: 4.0
Requires PHP: 7.4
Tested up to: 6.7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow your readers to submit questions.

== Description ==

Allow your readers to submit questions.

== Installation ==

1. Upload `ask-me-anything` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Adjust the settings in Questions -> Settings

== Changelog ==

**1.2.2 - 13 February 2026**

* Fix: Support admin notifications to multiple email addresses (comma-separated)
* Fix: Slashes before apostrophes in admin email notification subjects
* Fix: PHP deprecation errors

**1.2.1 - 10 December 2024**

* Fix: Ensure the "Leave a Comment" text does not appear if the current user cannot leave a comment.

**1.2.0 - 10 December 2024**

* New: The "Allow Comments on Questions" setting has been switched to a dropdown to allow more fine-grain control.

**1.1.3 - 16 May 2018**

* New: Add question data to WordPress personal information export.
* New: Anonymize questions when using WordPress personal information eraser.
* New: Added filter to `ask_me_anything_get_public_statuses()` function.
* New: Add privacy policy agreement to question form (enabled in Questions > Settings > Questions > Fields).
* Tweak: Don't check on notification checkboxes by default.
* Fix: Default values not being passed into settings functions.

**1.1.2 - 12 October 2016**

* Increased z-index of modal to better ensure it appears above everything else.
* Add extra class name with commenter's user ID to comment wrapper.

**1.1.1 - 10 July 2016**

* When new comment is added, it is now prepended rather than appended.
* Fixed issue where name and email were always required, despite choosing otherwise in the settings.
* Avatar is now only displayed if it exists (added conditional check to comments.php template).
* Added a new option to hide status labels on the front-end.

**1.1.0 - 1 June 2016**

* Fixed question edit URL in admin notification emails.
* Post titles now display curly quotes correctly.
* Fixed new comment notification email - it was including the question content instead of the comment content.
* Added Akismet integration for questions.
* Added some actions to template files.

**1.0.1 - 25 May 2016**

* Fixed an issue with license key activation.
* Added some reset heading styles inside the AMA modal.

**1.0.0 - 24 May 2016**

* Initial release.
