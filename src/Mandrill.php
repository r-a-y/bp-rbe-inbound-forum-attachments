<?php
/**
 * Inbound Forum Attachments: Mandrill class
 *
 * Adds forum attachment support for the Mandrill inbound provider.
 *
 * @package RB_RBE\Inbound
 * @subpackage ForumAttachments
 * @since 0.1.1
 */

namespace BP_RBE\Inbound\ForumAttachments;

use BP_RBE\Inbound\ForumAttachments\Base;

/**
 * Mandrill support.
 *
 * @see   https://mailchimp.com/developer/transactional/docs/webhooks/#inbound-messages
 * @since 0.1.1
 */
class Mandrill extends Base {
	/**
	 * Get attachments from Mandrill webhook.
	 *
	 * @since 0.1.1
	 *
	 * @return array
	 */
	public function get_attachments() {
		$attachments = $this->data['mandrill_attachments'];

		// If no attachments, return empty array.
		if ( empty( $attachments ) ) {
			return [];
		}

		$retval = [];
		foreach ( $attachments as $attachment ) {
			$content = $attachment->content;
			if ( $attachment->base64 ) {
				$content = base64_decode( $attachment->content );
			}

			$retval[] = [
				'name' => $attachment->name,
				'data' => $content
			];
		}

		return $retval;
	}
}
