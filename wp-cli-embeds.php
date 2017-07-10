<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

use \Swissspidy\WP_CLI_Embeds;

WP_CLI::add_command( 'oembed fetch', WP_CLI_Embeds\Fetch_Command::class, [
	'before_invoke' => function () {
		if ( \WP_CLI\Utils\wp_version_compare( '2.9', '<' ) ) {
			WP_CLI::error( 'Requires WordPress 2.9 or greater.' );
		}
	},
] );

WP_CLI::add_command( 'oembed provider', WP_CLI_Embeds\Provider_Command::class, [
	'before_invoke' => function () {
		if ( \WP_CLI\Utils\wp_version_compare( '2.9', '<' ) ) {
			WP_CLI::error( 'Requires WordPress 2.9 or greater.' );
		}
	},
] );
