<?php

namespace WP_CLI\Embeds;

use WP_CLI;
use WP_CLI\Process;
use WP_CLI\Utils;
use WP_CLI\Formatter;
use WP_CLI_Command;

class Fetch_Command extends WP_CLI_Command {
	/**
	 * Attempts to convert a URL into embed HTML.
	 *
	 * Starts by checking the URL against the regex of the registered embed handlers.
	 * If none of the regex matches and it's enabled, then the URL will be given to the WP_oEmbed class.
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : URL to retrieve oEmbed data for.
	 *
	 * [--width=<width>]
	 * : Width of the embed in pixels.
	 *
	 * [--height=<height>]
	 * : Height of the embed in pixels.
	 *
	 * [--post-id=<id>]
	 * : Cache oEmbed response for a given post.
	 *
	 * [--skip-cache]
	 * : Ignore already cached oEmbed responses.
	 *
	 * [--raw]
	 * : Return the raw oEmbed response instead of the resulting HTML. Only
	 * possible when there's no internal handler for the given URL.
	 *
	 * [--dry-run]
	 * : Do not perform any HTTP requests.
	 *
	 * [--discover]
	 * : Enabled oEmbed discovery. Defaults to true.
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
	 *     # Get embed HTML for a given URL.
	 *     $ wp embeds fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ
	 *
	 *     # Get raw oEmbed data for a given URL.
	 *     $ wp embeds fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --raw --skip-cache
	 */
	public function __invoke( $args, $assoc_args ) {
		/** @var \WP_Embed $wp_embed */
		global $wp_embed;

		$url                 = $args[0];
		$raw                 = Utils\get_flag_value( $assoc_args, 'raw' );
		$post_id             = Utils\get_flag_value( $assoc_args, 'post-id' );
		$discover            = Utils\get_flag_value( $assoc_args, 'discover', true );
		$response_size_limit = Utils\get_flag_value( $assoc_args, 'limit-response-size' );

		$oembed_args = array(
			'discover' => $discover,
			'width' => Utils\get_flag_value( $assoc_args, 'width' ),
			'height' => Utils\get_flag_value( $assoc_args, 'height' ),
		);

		if ( $response_size_limit ) {
			add_filter( 'oembed_remote_get_args', function ( $args ) use ( $response_size_limit ) {
				$args['limit_response_size'] = $response_size_limit;

				return $args;
			} );
		}

		// WP_Embed::shortcode() can't return raw data, which means we need to use WP_oEmbed.
		if ( $raw ) {
			remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result' );
			add_filter( 'pre_oembed_result', array( $this, 'filter_pre_oembed_result' ), 10, 3 );

			$oembed = _wp_oembed_get_object();

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

			if ( $raw ) {
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

			return;
		}

		if ( $post_id ) {
			$GLOBALS['post'] = get_post( $post_id );
		}

		$wp_embed->usecache = ! Utils\get_flag_value( $assoc_args, 'skip-cache' );

		$html = $wp_embed->shortcode( $oembed_args, $url );

		if ( false === $html ) {
			WP_CLI::error( 'There was an error fetching the oEmbed data.' );
		}

		WP_CLI::line( $html );
	}

	/**
	 * Filters the oEmbed result before any HTTP requests are made.
	 *
	 * If the URL belongs to the current site, the result is fetched directly instead of
	 * going through the oEmbed discovery process.
	 *
	 * This function is identical to wp_filter_pre_oembed_result() with the exception that it
	 * returns the raw oEmbed data instead of just the resulting HTML.
	 *
	 * @param null|string $result The UNSANITIZED (and potentially unsafe) HTML that should be used to embed. Default null.
	 * @param string      $url    The URL that should be inspected for discovery `<link>` tags.
	 * @param array       $args   oEmbed remote get arguments.
	 * @return null|string The UNSANITIZED (and potentially unsafe) HTML that should be used to embed.
	 *                     Null if the URL does not belong to the current site.
	 */
	protected function filter_pre_oembed_result( $result, $url, $args ) {
		$switched_blog = false;

		if ( is_multisite() ) {
			$url_parts = wp_parse_args(
				wp_parse_url( $url ), array(
					'host' => '',
					'path' => '/',
				)
			);

			$qv = array(
				'domain' => $url_parts['host'],
				'path'   => '/',
			);

			// In case of subdirectory configs, set the path.
			if ( ! is_subdomain_install() ) {
				$path = explode( '/', ltrim( $url_parts['path'], '/' ) );
				$path = reset( $path );

				if ( $path ) {
					$qv['path'] = get_network()->path . $path . '/';
				}
			}

			$sites = get_sites( $qv );
			$site  = reset( $sites );

			if ( $site && (int) $site->blog_id !== get_current_blog_id() ) {
				switch_to_blog( $site->blog_id );
				$switched_blog = true;
			}
		}

		$post_id = url_to_postid( $url );

		/** This filter is documented in wp-includes/class-wp-oembed-controller.php */
		$post_id = apply_filters( 'oembed_request_post_id', $post_id, $url );

		if ( ! $post_id ) {
			if ( $switched_blog ) {
				restore_current_blog();
			}

			return $result;
		}

		$width = isset( $args['width'] ) ? $args['width'] : 0;

		$data = get_oembed_response_data( $post_id, $width );
		// $data = _wp_oembed_get_object()->data2html( (object) $data, $url );

		if ( $switched_blog ) {
			restore_current_blog();
		}

		if ( ! $data ) {
			return $result;
		}

		return $data;
	}
}
