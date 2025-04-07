<?php
/**
 * Inbound Forum Attachments: Postmark class
 *
 * Adds forum attachment support for the Postmark inbound provider.
 *
 * @package RB_RBE\Inbound
 * @subpackage ForumAttachments
 * @since 0.1.1
 */
namespace BP_RBE\Inbound\ForumAttachments;

use BP_RBE\Inbound\ForumAttachments\Base;

/**
 * Postmark support.
 *
 * @see   https://postmarkapp.com/developer/user-guide/inbound/parse-an-email
 * @since 0.1.1
 */
class Postmark extends Base {
	/**
	 * Get attachments from Postmark webhook.
	 *
	 * @since 0.1.1
	 *
	 * @return array
	 */
	public function get_attachments() {
		$response = file_get_contents( 'php://input' );
		$response = json_decode( $response );

		// If no attachments, return empty array.
		if ( empty( $response->Attachments ) ) {
			return [];
		}

		$attachments = [];
		foreach ( $response->Attachments as $attachment ) {
			$attachments[] = [
				'name' => $attachment->Name,
				'data' => base64_decode( $attachment->Content )
			];
		}

		return $attachments;
	}
}
