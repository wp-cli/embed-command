<?php

namespace WP_CLI\Embeds;

use WP_CLI;
use WP_CLI\Formatter;
use WP_CLI\Utils;
use WP_CLI_Command;

/**
 * Retrieves oEmbed providers.
 */
class ProviderCommand extends WP_CLI_Command {
	protected $possible_fields = array(
		'format',
		'endpoint',
	);

	/**
	 * List all available oEmbed provider.
	 *
	 * ## OPTIONS
	 *
	 * [--field=<field>]
	 * : Display the value of a single field
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 * ---
	 *
	 * [--force-regex]
	 * : Turn the asterisk-type provider URLs into regex
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each provider:
	 *
	 * * format
	 * * endpoint
	 *
	 * These fields are optionally available:
	 *
	 * * name
	 * * https
	 * * since
	 *
	 * ## EXAMPLES
	 *
	 *     # List format,endpoint fields of available providers.
	 *     $ wp embeds provider list --fields=format,endpoint
	 *     +------------------------------+-----------------------------------------+
	 *     | format                       | endpoint                                |
	 *     +------------------------------+-----------------------------------------+
	 *     | #https?://youtu\.be/.*#i     | https://www.youtube.com/oembed          |
	 *     | #https?://flic\.kr/.*#i      | https://www.flickr.com/services/oembed/ |
	 *     | #https?://wordpress\.tv/.*#i | https://wordpress.tv/oembed/            |
	 *
	 * @subcommand list
	 */
	public function list_providers( $args, $assoc_args ) {
		$oembed = _wp_oembed_get_object();

		$force_regex = Utils\get_flag_value( $assoc_args, 'force-regex' );

		$providers = array();

		foreach (  (array) $oembed->providers as $matchmask => $data ) {
			list( $providerurl, $regex ) = $data;

			// Turn the asterisk-type provider URLs into regex
			if ( $force_regex && ! $regex ) {
				$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
				$matchmask = preg_replace( '|^#http\\\://|', '#https?\://', $matchmask );
			}

			$providers[] = array(
				'format'   => $matchmask,
				'endpoint' => $providerurl,
			);
		}

		$formatter = $this->get_formatter( $assoc_args );
		$formatter->display_items( $providers );
	}

	/**
	 * Get provider for a given URL.
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : URL to retrieve provider for.
	 *
	 * [--verbose]
	 * : Show debug information.
	 *
	 * [--discover]
	 * : Whether to use oEmbed discovery or not. Defaults to true.
	 *
	 * [--limit-response-size=<size>]
	 * : Limit the size of the resulting HTML when using discovery. Default 150 KB.
	 *
	 * [--format=<format>]
	 * : Which data format to prefer.
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
	 *     $ wp embeds provider get https://www.youtube.com/watch?v=dQw4w9WgXcQ
	 *
	 * @subcommand get
	 */
	public function get_provider( $args, $assoc_args ) {
		$oembed = _wp_oembed_get_object();

		$url                 = $args[0];
		$discover            = \WP_CLI\Utils\get_flag_value( $assoc_args, 'discover', true );
		$response_size_limit = \WP_CLI\Utils\get_flag_value( $assoc_args, 'limit-response-size' );
		$format              = \WP_CLI\Utils\get_flag_value( $assoc_args, 'format' );

		if ( $response_size_limit ) {
			add_filter( 'oembed_remote_get_args', function ( $args ) use ( $response_size_limit ) {
				$args['limit_response_size'] = $response_size_limit;

				return $args;
			} );
		}

		if ( $format ) {
			add_filter( 'oembed_linktypes', function ( $linktypes ) use ( $format ) {
				foreach ( $linktypes as $mime_type => $linktype_format ) {
					if ( $format !== $linktype_format ) {
						unset( $linktypes[ $mime_type ] );
					}
				}

				return $linktypes;
			} );
		}

		$oembed_args = array(
			'discover' => $discover,
		);

		$provider = $oembed->get_provider( $url, $oembed_args );

		if ( ! $provider ) {
			if ( ! $discover ) {
				WP_CLI::error( 'No oEmbed provider found for given URL.' );
			} else {
				WP_CLI::error( 'No oEmbed provider found for given URL. Maybe try discovery?' );
			}
		}

		WP_CLI::line( $provider );
	}

	/**
	 * Get Formatter object based on supplied parameters.
	 *
	 * @param array $assoc_args Parameters passed to command. Determines formatting.
	 * @return \WP_CLI\Formatter
	 */
	protected function get_formatter( &$assoc_args ) {
		return new Formatter( $assoc_args, $this->possible_fields );
	}
}
