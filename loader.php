<?php
/*
Plugin Name: BP Reply By Email - Inbound Forum Attachments
Description: Attachment support when replying by email to bbPress forum items. Requires GD bbPress Attachments, bbPress and RBE in Inbound mode.
Author: r-a-y
Author URI: http://profiles.wordpress.org/r-a-y
Version: 0.1-alpha
License: GPLv2 or later
*/

/**
 * Loader.
 *
 * @since 0.1.0
 */
function bp_rbe_ifa_loader() {
	// RBE isn't loaded or RBE's requirements are not fulfilled.
	if ( ! function_exists( 'bp_rbe_is_required_completed' ) || ! bp_rbe_is_required_completed() ) {
		return;
	}

	// Check for bbPress.
	if ( ! function_exists( 'bbpress' ) ) {
		return;
	}

	// Check for GD bbPress Attachments.
	if ( ! defined( 'GDBBPRESSATTACHMENTS_CAP' ) ) {
		return;
	}

	/**
	 * Bail if PHP < 5.4 and if SparkPost is current inbound provider.
	 *
	 * SparkPost requires using an email parsing lib that uses PHP 5.4.
	 */
	$provider = bp_rbe_get_setting( 'inbound-provider' );
	if ( version_compare( phpversion(), '5.4', '<' ) && ( 'sparkpost' === $provider ) ) {
		$error = __( 'BP Reply By Email - Inbound Forum Attachments requires PHP 5.4 or higher for SparkPost. Please upgrade PHP or deactivate this plugin.', 'bp-rbe-inbound-forum-attachments' );
		add_action( 'admin_notices', function() {
			echo '<div class="error"><p>' . $error . '</p></div>';
		} );
		return;
	}

	// Bail if in wp-admin or if this is an AJAX request.
	if ( defined( 'WP_NETWORK_ADMIN' ) && defined( 'DOING_AJAX' ) ) {
		return;
	}

	// Autoloader.
	spl_autoload_register( function( $class ) {
		$prefix = 'BP_RBE\\Inbound\\ForumAttachments\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class, strlen( $prefix ) );

		$base_dir = dirname( __FILE__ ) . '/src/';

		$file = $base_dir . str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class . '.php' );

		if ( file_exists( $file ) ) {
			require $file;
		}
	} );

	// Do the damn thing.
	add_filter( 'bp_rbe_parser_misc_data', function( $retval, $i ) {
		// Check if an inbound provider is set. If not, bail.
		if ( empty( $GLOBALS['bp_rbe']->inbound_provider ) ) {
			return $retval;
		}

		// Attachment time!
		$provider = 'BP_RBE\\Inbound\\ForumAttachments\\' . ucfirst( bp_rbe_get_setting( 'inbound-provider' ) );
		if ( class_exists( $provider ) ) {
			$provider = new $provider( $retval, $i );
			return $provider->parse();
		} else {
			return $retval;
		}
	}, 10, 2 );
}
add_action( 'bp_include', 'bp_rbe_ifa_loader', 25 );