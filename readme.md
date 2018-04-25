# BP Reply By Email - Inbound Forum Attachments #

This is a companion plugin for [BP Reply By Email](https://github.com/r-a-y/bp-reply-by-email) (RBE), supporting forum attachments when replying by email when using RBE's Inbound Mode.  Currently only supports SparkPost.

This plugin was developed for the [CUNY Academic Commons](http://commons.gc.cuny.edu).  Licensed under the GPLv2 or later.

#### What does this plugin do?

As of v1.0-RC6, RBE will add forum attachment support when replying by email, but only for IMAP mode:
https://github.com/r-a-y/bp-reply-by-email/issues/104

This plugin allows those using RBE with Inbound Mode to also add attachments when replying to forum emails.
Currently, SparkPost is supported.  SendGrid and Postmark will be added at a later date.

Requirements
-
* BP Reply By Email (with [Inbound mode enabled](https://github.com/r-a-y/bp-reply-by-email/wiki/Starter-Guide#1-inbound-email-mode)).  Use 1.0-RC6 or higher.  Currently only supports SparkPost.
* BuddyPress
* bbPress (with [BuddyPress group support enabled](https://codex.buddypress.org/getting-started/installing-group-and-sitewide-forums/#b-set-up-group-and-sitewide-forums))
* BuddyPress Group Email Subscription v3.8.2 or higher.
* GD bbPress Attachments
* PHP 5.4+ when using SparkPost.

How to use?
-
1. Fulfill the requirements listed above.
2. If you cloned this repo, in your shell prompt, navigate to the plugin's directory and run `composer install` to install all dependencies.  If you don't know what Composer is, visit this [page](https://getcomposer.org/doc/00-intro.md#system-requirements).
3. Activate this plugin.
4. Create a forum post in a BuddyPress group.  You should receive an email.  Reply to this email and add an attachment via your email client.
5. Attachment should be added to the forum post, with a link to the attachment in the corresponding email.