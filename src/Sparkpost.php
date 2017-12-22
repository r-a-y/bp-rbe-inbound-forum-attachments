<?php
/**
 * Inbound Forum Attachments: Sparkpost class
 *
 * Adds forum attachment support for the SparkPost inbound provider.
 *
 * @package RB_RBE\Inbound
 * @subpackage ForumAttachments
 * @since 0.1.0
 */

namespace BP_RBE\Inbound\ForumAttachments;

use BP_RBE\Inbound\ForumAttachments\Base;

/**
 * SparkPost support.
 *
 * @since 0.1.0
 */
class Sparkpost extends Base {
	public function get_email() {
		return $this->data['rfc822'];
	}
}