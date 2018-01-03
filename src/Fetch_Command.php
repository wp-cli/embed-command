<?php

namespace WP_CLI\Embeds;

use WP_CLI;
use WP_CLI\Utils;
use WP_CLI_Command;

class Fetch_Command extends WP_CLI_Command {
	/**
	 * Attempts to convert a URL into embed HTML.
	 *
	 * In non-raw mode, starts by checking the URL against the regex of the registered embed handlers.
	 * If none of the regex matches and it's enabled, then the URL will be given to the WP_oEmbed class.
	 *
	 * In raw mode, checks the providers directly and returns the data.
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
	 * : Ignore already cached oEmbed responses. Has no effect if using the 'raw' option, which doesn't use the cache.
	 *
	 * [--discover]
	 * : Enable oEmbed discovery. Defaults to true.
	 *
	 * [--limit-response-size=<size>]
	 * : Limit the size of the resulting HTML when using discovery. Default 150 KB (the standard WordPress limit). Not compatible with 'no-discover'.
	 *
	 * [--raw]
	 * : Return the raw oEmbed response instead of the resulting HTML. Ignores the cache.
	 *
	 * [--raw-format=<json|xml>]
	 * : Render raw oEmbed data in a particular format. Defaults to json. Can only be specified in conjunction with the 'raw' option.
	 * ---
	 * options:
	 *   - json
	 *   - xml
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Get embed HTML for a given URL.
	 *     $ wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ
	 *     <iframe width="525" height="295" src="https://www.youtube.com/embed/dQw4w9WgXcQ?feature=oembed" ...
	 *
	 *     # Get raw oEmbed data for a given URL.
	 *     $ wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --raw
	 *     {"type":"video","thumbnail_width":480,"height":295,"author_name":"RickAstleyVEVO","html":"<iframe width=\"525\" ...
	 */
	public function __invoke( $args, $assoc_args ) {
		/** @var \WP_Embed $wp_embed */
		global $wp_embed;

		$url                 = $args[0];
		$raw                 = Utils\get_flag_value( $assoc_args, 'raw' );
		$raw_format          = Utils\get_flag_value( $assoc_args, 'raw-format' );
		$post_id             = Utils\get_flag_value( $assoc_args, 'post-id' );
		$discover            = Utils\get_flag_value( $assoc_args, 'discover' );
		$response_size_limit = Utils\get_flag_value( $assoc_args, 'limit-response-size' );

		// The `$key_suffix` used for caching is part based on serializing the attributes array without normalizing it first so need to try to replicate that.
		$oembed_args = array();
		if ( null !== ( $width = Utils\get_flag_value( $assoc_args, 'width' ) ) ) {
			$oembed_args['width'] = $width; // Keep as string as if from a shortcode attribute.
		}
		if ( null !== ( $height = Utils\get_flag_value( $assoc_args, 'height' ) ) ) {
			$oembed_args['height'] = $height; // Keep as string as if from a shortcode attribute.
		}
		if ( null !== $discover ) {
			$oembed_args['discover'] = $discover ? '1' : '0'; // Make it a string as if from a shortcode attribute.
		}

		$discover = null === $discover ? true : (bool) $discover;

		if ( ! $discover && null !== $response_size_limit ) {
			WP_CLI::error( "The 'limit-response-size' option can only be used with discovery." );
		}

		if ( ! $raw && null !== $raw_format ) {
			WP_CLI::error( "The 'raw-format' option can only be used with the 'raw' option." );
		}

		if ( $response_size_limit ) {
			if ( Utils\wp_version_compare( '4.0', '<' ) ) {
				WP_CLI::warning( "The 'limit-response-size' option only works for WordPress 4.0 onwards." );
				// Fall through anyway...
			}
			add_filter( 'oembed_remote_get_args', function ( $args ) use ( $response_size_limit ) {
				$args['limit_response_size'] = $response_size_limit;

				return $args;
			} );
		}

		// If raw, query providers directly, by-passing cache.
		if ( $raw ) {

			$oembed = new oEmbed;

			// Allow `wp_filter_pre_oembed_result()` to provide local URLs (WP >= 4.5.3).

			// Before applying 'pre_oembed_result', make `WP_oEmbed::data2html()` a no-op so get raw data.
			add_filter( 'oembed_dataparse', function ( $return, $data, $url ) {
				return $data;
			}, 9999, 3 ); // Need large priority to avoid `_strip_newlines` filter added in `WP_oEmbed::__construct()`.

			$data = apply_filters( 'pre_oembed_result', null, $url, $oembed_args );

			if ( null === $data ) {

				$provider = $oembed->get_provider( $url, $oembed_args );

				if ( ! $provider ) {
					if ( ! $discover ) {
						WP_CLI::error( 'No oEmbed provider found for given URL. Maybe try discovery?' );
					} else {
						WP_CLI::error( 'No oEmbed provider found for given URL.' );
					}
				}

				$data = $oembed->fetch( $provider, $url, $oembed_args );
			}

			if ( false === $data ) {
				WP_CLI::error( 'There was an error fetching the oEmbed data.' );
			}

			if ( 'xml' === $raw_format ) {
				if ( ! class_exists( 'SimpleXMLElement' ) ) {
					WP_CLI::error( "The PHP extension 'SimpleXMLElement' is not available but is required for XML-formatted output." );
				}
				WP_CLI::log( $this->_oembed_create_xml( (array) $data ) );
			} else {
				WP_CLI::log( json_encode( $data ) );
			}

			return;
		}

		if ( $post_id ) {
			$GLOBALS['post'] = get_post( $post_id );
			if ( null === $GLOBALS['post'] ) {
				WP_CLI::warning( sprintf( "Post id '%s' not found.", $post_id ) );
			}
		}

		$skip_cache = Utils\get_flag_value( $assoc_args, 'skip-cache' );
		if ( $skip_cache ) {
			$wp_embed->usecache = false;
			// In order to skip caching, also need `$cached_recently` to be false in `WP_Embed::shortcode()`, so set TTL to zero.
			add_filter( 'oembed_ttl', '__return_zero' );
		} else {
			$wp_embed->usecache = true;
		}

		if ( ! $discover ) {
			// `WP_Embed::shortcode()` sets the 'discover' attribute based on this filter, no matter what's passed to it, so set to false.
			add_filter( 'embed_oembed_discover', '__return_false' );
		}

		// For WP < 4.9, `WP_Embed::shortcode()` won't check providers if no post_id supplied, so set maybe_make_link to return false so can check and do it ourselves.
		if ( $check_providers = ( Utils\wp_version_compare( '4.9', '<' ) && ! $post_id ) ) {
			add_filter( 'embed_maybe_make_link', '__return_false' );
		}

		$html = $wp_embed->shortcode( $oembed_args, $url );

		if ( false === $html && $check_providers ) {

			// Check providers.
			$html = wp_oembed_get( $url, $oembed_args );

			if ( ! $html ) {
				// Return a clickable link for compatibility with WP 4.9 behaviour.
				remove_filter( 'embed_maybe_make_link', '__return_false' );
				$html = $wp_embed->maybe_make_link( $url );
			}
		}

		if ( false === $html ) {
			WP_CLI::error( 'There was an error fetching the oEmbed data.' );
		}

		WP_CLI::log( $html );
	}

	/**
	 * Creates an XML string from a given array.
	 *
	 * Same as `\_oembed_create_xml()` in "wp-includes\embed.php" introduced in WP 4.4.0. Polyfilled as marked private (and also to cater for older WP versions).
	 *
	 * @see _oembed_create_xml()
	 *
	 * @param array            $data The original oEmbed response data.
	 * @param \SimpleXMLElement $node Optional. XML node to append the result to recursively.
	 * @return string|false XML string on success, false on error.
	 */
	protected function _oembed_create_xml( $data, $node = null ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}

		if ( null === $node ) {
			$node = new \SimpleXMLElement( '<oembed></oembed>' );
		}

		foreach ( $data as $key => $value ) {
			if ( is_numeric( $key ) ) {
				$key = 'oembed';
			}

			if ( is_array( $value ) ) {
				$item = $node->addChild( $key );
				$this->_oembed_create_xml( $value, $item );
			} else {
				$node->addChild( $key, esc_html( $value ) );
			}
		}

		return $node->asXML();
	}
}
