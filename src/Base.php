<?php
/**
 * Inbound Forum Attachments: Base class
 *
 * Abstract base class meant to be extended by various inbound providers.
 *
 * @package RB_RBE\Inbound
 * @subpackage ForumAttachments
 * @since 0.1.0
 */

namespace BP_RBE\Inbound\ForumAttachments;

use BP_Reply_By_Email_Parser as InboundParser;
use BBP_RBE_Extension as Attachment;

/**
 * Abstract base class meant to be extended by various inbound providers.
 *
 * @since 0.1.0
 */
abstract class Base {
	/**
	 * Miscellaneous email data.
	 *
	 * @since 0.1.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * Message number.
	 *
	 * @since 0.1.0
	 * @var int
	 */
	protected $i = 0;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	final public function __construct( $data, $i ) {
		$this->data = $data;
		$this->i    = $i;
	}

	/**
	 * Main attachments parser.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function parse() {
		if ( ! $this->is_parseable() ) {
			return $this->data;
		}

		$email       = $this->get_email();
		$attachments = $this->get_attachments();
		if ( empty( $email ) && empty( $attachments ) ) {
			return $this->data;
		}

		if ( ! empty( $email ) ) {
			$data = $this->parse_from_email( $email );
		} elseif ( ! empty( $attachments ) ) {
			$data = $this->parse_from_attachments( $attachments );
		} else {
			return $this->data;
		}

		// Add our attachment metadata to the email data.
		if ( ! empty( $data['attachments'] ) ) {
			$this->data['bbp_attachments'] = $data['attachments'];
		}
		if ( ! empty( $data['errors'] ) ) {
			$this->data['bbp_attachments_errors'] = $data['errors'];
		}

		return $this->data;
	}

	/**
	 * Parse attachments from raw, multi-part email.
	 *
	 * @since 0.1.0
	 *
	 * @param  string $email Raw email, including headers and boundaries.
	 * @return array
	 */
	protected function parse_from_email( $email ) {
		/**
		 * Load email parsing library.
		 */
		require_once realpath( __DIR__ . '/../vendor/autoload.php' );

		$email  = \bashkarev\email\Parser::email( $email );
		$retval = array();

		$attachment_data = $attachment_errors = array();

		foreach ($email->getAttachments() as $attachment) {
			$save = Attachment::save_attachment( array(
				'name'   => $attachment->getFileName(),
				'data'   => $attachment->getStream()->getContents(),
				'i'      => $this->i,
				'params' => InboundParser::$params
			) );

			if ( ! empty( $save['data'] ) ) {
				$attachment_data[] = $save['data'];
			}
			if ( ! empty( $save['errors'] ) ) {
				$attachment_errors = array_merge_recursive( $attachment_errors, $save['errors'] );
			}
		}

		// Set up return array.
		if ( ! empty( $attachment_data ) ) {
			$retval['attachments'] = $attachment_data;
		}
		if ( ! empty( $attachment_errors ) ) {
			$retval['errors'] = $attachment_errors;
		}

		return $retval;
	}

	/**
	 * Parse attachments from already-set attachments.
	 *
	 * @todo When adding support for SendGrid and Postmark, use this.
	 * @link https://github.com/Ziggeo/php-sendgrid-parse/blob/master/SendgridParse.php#L27
	 * @link https://postmarkapp.com/developer/user-guide/inbound/parse-an-email
	 *
	 * @since 0.1.0
	 *
	 * @param array $attachments Multi-dimensional array of attachments. {
	 *     @type string $name     Required. Attachment filename.
	 *     @type string $tmp_file Location where attachment was temporarily uploaded.
	 *     @type string $data     Attachment inline data. Use it if your attachment is inline data and needs
	 *                            to be saved to the server. Use if $tmp_file is not available.
	 * }
	 * @return array
	 */
	protected function parse_from_attachments( $attachments ) {
		$retval = array();

		$attachment_data = $attachment_errors = array();

		// @todo This is all theoretical at the moment.
		foreach ( $attachments as $attachment) {
			$save = array();

			// Inline data requires saving to tmp directory.
			if ( ! empty( $attachment['data'] ) ) {
				$save = Attachment::save_attachment( array(
					'name'   => $attachment['name'],
					'data'   => $attachment['data'],
					'i'      => $this->i,
					'params' => InboundParser::$params
				) );

			// Already uploaded via $_FILES array or similar format.
			} elseif ( ! empty( $attachment['tmp_name'] ) && ! empty( $attachment['name'] ) ) {
				$save = Attachment::save_attachment( array(
					'name'     => $attachment['name'],
					'tmp_name' => $attachment['tmp_name'],
					'i'        => $this->i,
					'params'   => InboundParser::$params
				) );
			}

			if ( ! empty( $save['data'] ) ) {
				$attachment_data[] = $save['data'];
			}
			if ( ! empty( $save['errors'] ) ) {
				$attachment_errors = array_merge_recursive( $attachment_errors, $save['errors'] );
			}
		}

		// Set up return array.
		if ( ! empty( $attachment_data ) ) {
			$retval['attachments'] = $attachment_data;
		}
		if ( ! empty( $attachment_errors ) ) {
			$retval['errors'] = $attachment_errors;
		}

		return $retval;
	}

	/**
	 * Check if replied email is a bbPress item.
	 *
	 * @since 0.1.0
	 */
	public function is_parseable() {
		// Check if email params match a bbPress item.
		$allowed = array_flip( array( 'bbpf', 'bbpr', 'bbpt' ) );
		return (bool) array_intersect_key( InboundParser::$params, $allowed );
	}

	/** EXTENDABLE METHODS **************************************************/

	/**
	 * Fetch raw, multi-part email from inbound provider's POST response.
	 *
	 * Meant to be overriden in extended classes for supported inbound providers.
	 *
	 * @since 0.1.0
	 *
	 * @return string The raw email. Should be RFC822 email.
	 */
	public function get_email() {
		return '';
	}

	/**
	 * Fetch attachments from inbound provider's POST response.
	 *
	 * Meant to be overriden in extended classes for supported inbound providers.
	 *
	 * This is preferred over get_email() since this is lighter than passing the
	 * entire raw email.
	 *
	 * @since 0.1.0
	 *
	 * @return array Multi-dimensional array.
	 */
	public function get_attachments() {
		return array();
	}
}