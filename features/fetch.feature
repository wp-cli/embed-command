Feature: Manage oEmbed cache.

  Background:
    Given a WP install

  Scenario: Get HTML embed code for a given URL
    # Provider not requiring discovery
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --width=500`
    Then STDOUT should contain:
      """
      https://www.youtube.com/
      """
    And STDOUT should contain:
      """
      dQw4w9WgXcQ
      """

    # Provider requiring discovery
    # Old versions of WP_oEmbed can trigger PHP "Only variables should be passed by reference" notices on discover so use "try" to cater for these.
    When I try `wp embed fetch https://asciinema.org/a/140798`
    Then the return code should be 0
    And STDERR should not contain:
      """
      Error:
      """
    And STDOUT should contain:
      """
      asciinema.org/
      """

  Scenario: Get raw oEmbed data for a given URL
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --raw`
    And save STDOUT as {DEFAULT_STDOUT}
    Then STDOUT should contain:
      """
      "type":"video"
      """

    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --raw --raw-format=json`
    And save STDOUT as {DEFAULT_STDOUT}
    Then STDOUT should be:
      """
      {DEFAULT_STDOUT}
      """

  Scenario: Bails when no oEmbed provider is found for a raw request
    When I try `wp embed fetch https://foo.example.com --raw`
    # Old versions of WP_oEmbed can trigger PHP "Only variables should be passed by reference" notices on discovery so use "contain" to ignore these.
    Then STDERR should contain:
      """
      Error: No oEmbed provider found for given URL.
      """

  Scenario: Bails when no oEmbed provider is found for a raw request is found and discovery is off
    When I try `wp embed fetch https://foo.example.com --raw --discover=0`
    Then STDERR should be:
      """
      Error: No oEmbed provider found for given URL. Maybe try discovery?
      """

  Scenario: Makes unknown URLs clickable
    # Old versions of WP_oEmbed can trigger PHP "Only variables should be passed by reference" notices on discover so use "try" to cater for these.
    When I try `wp embed fetch https://foo.example.com`
    Then the return code should be 0
    And STDERR should not contain:
      """
      Error:
      """
    And STDOUT should contain:
      """
      <a href="https://foo.example.com">https://foo.example.com</a>
      """

  Scenario: Caches oEmbed response data for a given post
    When I run `wp post create --post_title="Foo Bar" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {POST_ID}

    When I run `wp embed fetch https://foo.example.com --post-id={POST_ID}`
    Then STDOUT should contain:
      """
      <a href="https://foo.example.com">https://foo.example.com</a>
      """

    When I run `wp embed cache clear {POST_ID}`
    Then STDOUT should be:
      """
      Success: Cleared oEmbed cache.
      """

  Scenario: Return data as XML when requested
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --raw-format=xml --raw`
    Then STDOUT should contain:
      """
      <type>video</type>
      """

  # Depends on `oembed_remote_get_args` filter introduced WP 4.0 https://core.trac.wordpress.org/ticket/23442
  @require-wp-4.0
  Scenario: Get embed code for a URL with limited response size
    # Need post_id for caching to work for WP < 4.9
    When I run `wp post create --post_title="Foo Bar" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {POST_ID}

    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --post-id={POST_ID}`
    And save STDOUT as {DEFAULT_STDOUT}
    Then STDOUT should contain:
      """
      iframe
      """

    # Response limit too small but cached so ignored.
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --post-id={POST_ID} --limit-response-size=10`
    Then STDOUT should be:
      """
      {DEFAULT_STDOUT}
      """

    # Response limit too small and skip cache (and as html failed the cache will not be updated)
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --post-id={POST_ID} --limit-response-size=10 --skip-cache`
    Then STDOUT should not contain:
      """
      {DEFAULT_STDOUT}
      """
    And STDOUT should be:
      """
      <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">https://www.youtube.com/watch?v=dQw4w9WgXcQ</a>
      """

    # Response limit big enough and don't skip cache but as previous failed result not cached it doesn't matter
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --post-id={POST_ID} --limit-response-size=50000`
    Then STDOUT should be:
      """
      {DEFAULT_STDOUT}
      """

    # Response limit big enough and skip cache
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --post-id={POST_ID} --limit-response-size=50000 --skip-cache`
    Then STDOUT should be:
      """
      {DEFAULT_STDOUT}
      """

  # Same as above but without the post_id. WP > 4.9 only
  @require-wp-4.9
  Scenario: Get embed code for a URL with limited response size and post-less cache
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ`
    And save STDOUT as {DEFAULT_STDOUT}
    Then STDOUT should contain:
      """
      iframe
      """

    # Response limit too small but cached so ignored.
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --limit-response-size=10`
    Then STDOUT should be:
      """
      {DEFAULT_STDOUT}
      """

    # Response limit too small and skip cache (and as html failed the cache will not be updated)
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --limit-response-size=10 --skip-cache`
    Then STDOUT should not contain:
      """
      {DEFAULT_STDOUT}
      """
    And STDOUT should be:
      """
      <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">https://www.youtube.com/watch?v=dQw4w9WgXcQ</a>
      """

    # Response limit big enough and don't skip cache but as previous failed result not cached it doesn't matter
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --limit-response-size=50000`
    Then STDOUT should be:
      """
      {DEFAULT_STDOUT}
      """

    # Response limit big enough and skip cache
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --limit-response-size=50000 --skip-cache`
    Then STDOUT should be:
      """
      {DEFAULT_STDOUT}
      """

  Scenario: Incompatible options
    When I try `wp embed fetch https://www.example.com/watch?v=dQw4w9WgXcQ --no-discover --limit-response-size=50000`
    Then the return code should be 1
    And STDERR should be:
      """
      Error: The 'limit-response-size' option can only be used with discovery.
      """
    And STDOUT should be empty

    When I try `wp embed fetch https://www.example.com/watch?v=dQw4w9WgXcQ --raw-format=json`
    Then the return code should be 1
    And STDERR should be:
      """
      Error: The 'raw-format' option can only be used with the 'raw' option.
      """
    And STDOUT should be empty
