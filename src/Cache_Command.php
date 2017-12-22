<?php

namespace WP_CLI\Embeds;

use WP_CLI;
use WP_CLI\Process;
use WP_CLI\Utils;
use WP_CLI\Formatter;
use WP_CLI_Command;

/**
 * Finds, triggers, and deletes oEmbed caches.
 */
class Cache_Command extends WP_CLI_Command {

	/**
	 * Deletes all oEmbed caches for a given post.
	 *
	 * ## OPTIONS
	 *
	 * <post_id>
	 * : ID of the post to clear the cache for.
	 *
	 * ## EXAMPLES
	 *
	 *     # Clear cache for a post
	 *     $ wp embed cache clear 123
	 */
	public function clear( $args, $assoc_args ) {
		/** @var \WP_Embed $wp_embed */
		global $wp_embed;

		$post_id = $args[0];

		$post_metas = get_post_custom_keys( $post_id );

		if ( empty( $post_metas ) ) {
			WP_CLI::line( sprintf( 'No metadata available for post with ID %d!', $post_id ) );

			return;
		}

		$wp_embed->delete_oembed_caches( $post_id );

		WP_CLI::success( 'Cleared oEmbed cache.' );
	}

	/**
	 * Finds the oEmbed cache post ID for a given URL.
	 *
	 * Starting with WordPress 4.9, embeds that aren't associated with a specific post will be cached in
	 * a new oembed_cache post type.
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
	 * ## EXAMPLES
	 *
	 *     # Find cache post ID for a given URL.
	 *     $ wp embed cache find https://www.youtube.com/watch?v=dQw4w9WgXcQ --width=500
	 */
	public function find( $args, $assoc_args ) {
		if ( Utils\wp_version_compare( '4.9', '<' ) ) {
			WP_CLI::error( 'Requires WordPress 4.9 or greater.' );
		}

		/** @var \WP_Embed $wp_embed */
		global $wp_embed;

		$url = $args[0];

		$oembed_args = array(
			'width'    => (int) Utils\get_flag_value( $assoc_args, 'width' ),
			'height'   => (int) Utils\get_flag_value( $assoc_args, 'height' ),
		);

		$attr       = wp_parse_args( $oembed_args, wp_embed_defaults( $url ) );
		$key_suffix = md5( $url . serialize( $attr ) );

		$cached_post_id = $wp_embed->find_oembed_post_id( $key_suffix );

		if ( $cached_post_id ) {
			WP_CLI::line( $cached_post_id );

			return;
		}

		WP_CLI::error( 'No cache post ID found!' );
	}

	/**
	 * Triggers a caching of all oEmbed results for a given post.
	 *
	 * ## OPTIONS
	 *
	 * <post_id>
	 * : ID of the post to do th caching for.
	 *
	 * ## EXAMPLES
	 *
	 *     # Clear cache for a post
	 *     $ wp embed cache trigger 456
	 */
	public function trigger( $args, $assoc_args ) {
		/** @var \WP_Embed $wp_embed */
		global $wp_embed;

		$post_id    = $args[0];
		$post       = get_post( $post_id );
		$post_types = get_post_types( array( 'show_ui' => true ) );

		if ( empty( $post->ID ) ) {
			WP_CLI::warning( sprintf( 'Post %d does not exist!', $post_id ) );

			return;
		}

		/** This filter is documented in wp-includes/class-wp-embed.php */
		if ( ! in_array( $post->post_type, apply_filters( 'embed_cache_oembed_types', $post_types ), true ) ) {
			WP_CLI::warning( sprintf( 'Cannot cache oEmbed results for %s post type', $post->post_type ) );

			return;
		}

		$wp_embed->cache_oembed( $post_id );

		WP_CLI::success( 'Caching triggered!' );
	}
}
