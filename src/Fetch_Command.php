<?php

namespace Swissspidy\WP_CLI_Embeds;

use WP_CLI;

class Fetch_Command extends \WP_CLI_Command {
	/**
	 * Get oEmbed data for a given URL.
	 *
	 * <url>
	 * : URL to retrieve oEmbed data for.
	 *
	 * [--post-id=<id>]
	 * : Cache oEmbed response for a given post.
	 *
	 * [--skip-cache]
	 * : Ignore already cached oEmbed responses.
	 *
	 * [--raw]
	 * : Return the raw oEmbed response instead of the resulting HTML.
	 *
	 * [--dry-run]
	 * : Do not perform any HTTP requests.
	 *
	 * [--verbose]
	 * : Show debug information.
	 *
	 * [--discover=<true|false>]
	 * : Whether to use oEmbed discovery or not.
	 *
	 * [--limit-response-size=<size>]
	 * : Limit the size of the resulting HTML when using discovery. Default 150 KB.
	 *
	 * [--format=<format>]
	 * : Which data format to prefer. (filter oembed_linktypes)
	 * ---
	 * default: json
	 * options:
	 *   - json
	 *   - xml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # List format,endpoint fields of available providers.
	 *     $ wp oembed provider list --fields=format,endpoint
	 */
	public function __invoke( array $args, array $assoc_args ) : void {
		$oembed = _wp_oembed_get_object();

		$url = $args[0];
		$discover = \WP_CLI\Utils\get_flag_value( $assoc_args, 'discover', true );
		$response_size_limit = \WP_CLI\Utils\get_flag_value( $assoc_args, 'limit-response-size' );

		if ( $discover && $response_size_limit ) {
			add_filter( 'oembed_remote_get_args', function ( $args ) use ( $response_size_limit ) {
				$args['limit_response_size'] = $response_size_limit;

				return $args;
			} );
		}

		$oembed_args = [
			'discover' => $discover,
		];

		$provider = $oembed->get_provider( $url, $oembed_args );

		if ( ! $provider ) {
			if ( ! $discover ) {
				WP_CLI::error( 'No oEmbed provider found for given URL.' );
			} else {
				WP_CLI::error( 'No oEmbed provider found for given URL. Maybe try discovery?' );
			}
		}

		$data = $oembed->fetch( $provider, $url, $oembed_args );

		if ( false === $data ) {
			WP_CLI::error( 'There was an error fetching the oEmbed data.' );
		}

		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'raw', false ) ) {
			WP_CLI::line( $data );

			return;
		}

		/** This filter is documented in wp-includes/class-oembed.php */
		$pre = apply_filters( 'pre_oembed_result', null, $url, $oembed_args );

		if ( null !== $pre ) {
			WP_CLI::line( $pre );

			return;
		}

		/** This filter is documented in wp-includes/class-oembed.php */
		$html = apply_filters( 'oembed_result', $oembed->data2html( $data, $url ), $url, $oembed_args );

		if ( false === $html ) {
			WP_CLI::error( 'There was an error fetching the oEmbed data.' );
		}

		WP_CLI::line( $html );
	}
}
