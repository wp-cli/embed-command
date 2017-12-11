<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $autoload ) ) {
	require_once $autoload;
}

if ( class_exists( 'WP_CLI\Dispatcher\CommandNamespace' ) ) {
	WP_CLI::add_command( 'embeds', '\WP_CLI\Embeds\EmbedsNamespace' );
}

WP_CLI::add_command( 'embeds fetch', '\WP_CLI\Embeds\FetchCommand', array(
	'before_invoke' => function () {
		if ( \WP_CLI\Utils\wp_version_compare( '2.9', '<' ) ) {
			WP_CLI::error( 'Requires WordPress 2.9 or greater.' );
		}
	},
) );

WP_CLI::add_command( 'embeds provider', '\WP_CLI\Embeds\ProviderCommand', array(
	'before_invoke' => function () {
		if ( \WP_CLI\Utils\wp_version_compare( '2.9', '<' ) ) {
			WP_CLI::error( 'Requires WordPress 2.9 or greater.' );
		}
	},
) );

WP_CLI::add_command( 'embeds cache', '\WP_CLI\Embeds\CacheCommand', array(
	'before_invoke' => function () {
		if ( \WP_CLI\Utils\wp_version_compare( '2.9', '<' ) ) {
			WP_CLI::error( 'Requires WordPress 2.9 or greater.' );
		}
	},
) );
