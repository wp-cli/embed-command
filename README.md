wp-cli/embed-command
====================

Inspects oEmbed providers, clears embed cache, and more.

[![Build Status](https://travis-ci.org/wp-cli/embed-command.svg?branch=master)](https://travis-ci.org/wp-cli/embed-command)

Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing) | [Support](#support)

## Using

This package implements the following commands:

### wp embed fetch

Attempts to convert a URL into embed HTML.

~~~
wp embed fetch <url> [--width=<width>] [--height=<height>] [--post-id=<id>] [--skip-cache] [--raw] [--dry-run] [--discover] [--limit-response-size=<size>] [--format=<format>]
~~~

Starts by checking the URL against the regex of the registered embed handlers.
If none of the regex matches and it's enabled, then the URL will be given to the WP_oEmbed class.

**OPTIONS**

	<url>
		URL to retrieve oEmbed data for.

	[--width=<width>]
		Width of the embed in pixels.

	[--height=<height>]
		Height of the embed in pixels.

	[--post-id=<id>]
		Cache oEmbed response for a given post.

	[--skip-cache]
		Ignore already cached oEmbed responses.

	[--raw]
		Return the raw oEmbed response instead of the resulting HTML. Only
		possible when there's no internal handler for the given URL.

	[--dry-run]
		Do not perform any HTTP requests.

	[--discover]
		Enabled oEmbed discovery. Defaults to true.

	[--limit-response-size=<size>]
		Limit the size of the resulting HTML when using discovery. Default 150 KB.

	[--format=<format>]
		Which data format to prefer.
		---
		default: json
		options:
		  - json
		  - xml
		---

**EXAMPLES**

    # Get embed HTML for a given URL.
    $ wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ

    # Get raw oEmbed data for a given URL.
    $ wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --raw --skip-cache



### wp embed provider list

Lists all available oEmbed providers.

~~~
wp embed provider list [--field=<field>] [--fields=<fields>] [--format=<format>] [--force-regex]
~~~

**OPTIONS**

	[--field=<field>]
		Display the value of a single field

	[--fields=<fields>]
		Limit the output to specific fields.

	[--format=<format>]
		Render output in a particular format.
		---
		default: table
		options:
		  - table
		  - csv
		  - json
		---

	[--force-regex]
		Turn the asterisk-type provider URLs into regex

**AVAILABLE FIELDS**

These fields will be displayed by default for each provider:

* format
* endpoint

These fields are optionally available:

* name
* https
* since

**EXAMPLES**

    # List format,endpoint fields of available providers.
    $ wp embed provider list --fields=format,endpoint
    +------------------------------+-----------------------------------------+
    | format                       | endpoint                                |
    +------------------------------+-----------------------------------------+
    | #https?://youtu\.be/.*#i     | https://www.youtube.com/oembed          |
    | #https?://flic\.kr/.*#i      | https://www.flickr.com/services/oembed/ |
    | #https?://wordpress\.tv/.*#i | https://wordpress.tv/oembed/            |



### wp embed provider get

Gets the provider for a given URL.

~~~
wp embed provider get <url> [--verbose] [--discover] [--limit-response-size=<size>] [--format=<format>]
~~~

**OPTIONS**

	<url>
		URL to retrieve provider for.

	[--verbose]
		Show debug information.

	[--discover]
		Whether to use oEmbed discovery or not. Defaults to true.

	[--limit-response-size=<size>]
		Limit the size of the resulting HTML when using discovery. Default 150 KB.

	[--format=<format>]
		Which data format to prefer.
		---
		default: json
		options:
		  - json
		  - xml
		---

**EXAMPLES**

    # List format,endpoint fields of available providers.
    $ wp embed provider get https://www.youtube.com/watch?v=dQw4w9WgXcQ



### wp embed handler list

Lists all available embed handlers.

~~~
wp embed handler list [--field=<field>] [--fields=<fields>] [--format=<format>]
~~~

**OPTIONS**

	[--field=<field>]
		Display the value of a single field

	[--fields=<fields>]
		Limit the output to specific fields.

	[--format=<format>]
		Render output in a particular format.
		---
		default: table
		options:
		  - table
		  - csv
		  - json
		---

**AVAILABLE FIELDS**

These fields will be displayed by default for each handler:

* id
* regex

These fields are optionally available:

* callback
* priority

**EXAMPLES**

    # List id,regex,priority fields of available handlers.
    $ wp embed handler list --fields=priority,id
    +----------+-------------------+
    | priority | id                |
    +----------+-------------------+
    | 10       | youtube_embed_url |
    | 9999     | audio             |
    | 9999     | video             |



### wp embed cache clear

Deletes all oEmbed caches for a given post.

~~~
wp embed cache clear <post_id>
~~~

**OPTIONS**

	<post_id>
		ID of the post to clear the cache for.

**EXAMPLES**

    # Clear cache for a post
    $ wp embed cache clear 123



### wp embed cache find

Finds the oEmbed cache post ID for a given URL.

~~~
wp embed cache find <url> [--width=<width>] [--height=<height>]
~~~

Starts by checking the URL against the regex of the registered embed handlers.
If none of the regex matches and it's enabled, then the URL will be given to the WP_oEmbed class.

**OPTIONS**

	<url>
		URL to retrieve oEmbed data for.

	[--width=<width>]
		Width of the embed in pixels.

	[--height=<height>]
		Height of the embed in pixels.

**EXAMPLES**

    # Find cache post ID for a given URL.
    $ wp embed cache find https://www.youtube.com/watch?v=dQw4w9WgXcQ --width=500



### wp embed cache trigger

Triggers a caching of all oEmbed results for a given post.

~~~
wp embed cache trigger <post_id>
~~~

**OPTIONS**

	<post_id>
		ID of the post to do th caching for.

**EXAMPLES**

    # Clear cache for a post
    $ wp embed cache trigger 456

## Installing

This package is included with WP-CLI itself, no additional installation necessary.

To install the latest version of this package over what's included in WP-CLI, run:

    wp package install git@github.com:wp-cli/embed-command.git

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

For a more thorough introduction, [check out WP-CLI's guide to contributing](https://make.wordpress.org/cli/handbook/contributing/). This package follows those policy and guidelines.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/wp-cli/embed-command/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/wp-cli/embed-command/issues/new). Include as much detail as you can, and clear steps to reproduce if possible. For more guidance, [review our bug report documentation](https://make.wordpress.org/cli/handbook/bug-reports/).

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/wp-cli/embed-command/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, [please follow our guidelines for creating a pull request](https://make.wordpress.org/cli/handbook/pull-requests/) to make sure it's a pleasant experience. See "[Setting up](https://make.wordpress.org/cli/handbook/pull-requests/#setting-up)" for details specific to working on this package locally.

## Support

Github issues aren't for general support questions, but there are other venues you can try: https://wp-cli.org/#support


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
