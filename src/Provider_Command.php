<?php

namespace Swissspidy\WP_CLI_Embeds;

use WP_CLI\Formatter;

class Provider_Command extends \WP_CLI_Command {
	protected $possible_fields = [
		'format',
		'endpoint',
	];

	/**
	 * List all available oEmbed provider.
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
	 *     $ wp oembed provider list --fields=format,endpoint
	 *     +------------------------------+-----------------------------------------+
	 *     | format                       | endpoint                                |
	 *     +------------------------------+-----------------------------------------+
	 *     | #https?://youtu\.be/.*#i     | https://www.youtube.com/oembed          |
	 *     | #https?://flic\.kr/.*#i      | https://www.flickr.com/services/oembed/ |
	 *     | #https?://wordpress\.tv/.*#i | https://wordpress.tv/oembed/            |
	 *
	 * @subcommand list
	 */
	public function list( array $args, array $assoc_args ) : void {
		$oembed = _wp_oembed_get_object();

		$providers = [];

		foreach ( (array) $oembed->providers as $format => $endpoint ) {
			$provider = [
				'format'   => $format,
				'endpoint' => $endpoint,
			];

			foreach ( array_keys( $provider ) as $field ) {
				if ( isset( $assoc_args[ $field ] ) && $assoc_args[ $field ] !== $provider[ $field ] ) {
					continue 2;
				}
			}

			$providers[] = $provider;
		}

		$formatter = $this->get_formatter( $assoc_args );
		$formatter->display_items( $providers );
	}

	/**
	 * Get Formatter object based on supplied parameters.
	 *
	 * @param array $assoc_args Parameters passed to command. Determines formatting.
	 * @return \WP_CLI\Formatter
	 */
	protected function get_formatter( array &$assoc_args ) : Formatter {
		return new Formatter( $assoc_args, $this->possible_fields );
	}
}
