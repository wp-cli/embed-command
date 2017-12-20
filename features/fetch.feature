Feature: Manage oEmbed cache.

  Background:
    Given a WP install

  Scenario: Get HTML embed code for a given URL
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --width=500`
    Then STDOUT should be:
      """
      <iframe width="500" height="281" src="https://www.youtube.com/embed/dQw4w9WgXcQ?feature=oembed" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>
      """

  Scenario: Get raw oEmbed data for a given URL
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --raw`
    Then STDOUT should contain:
      """
      "type":"video"
      """

  Scenario: Bails when no oEmbed provider is found for a raw request
    When I run `wp embed fetch https://foo.example.com --raw`
    Then STDERR should be:
      """
      Error: No oEmbed provider found for given URL.
      """

  Scenario: Bails when no oEmbed provider is found for a raw request is found and discovery is off
    When I run `wp embed fetch https://foo.example.com --raw --discover=0`
    Then STDERR should be:
      """
      Error: No oEmbed provider found for given URL. Maybe try discovery?
      """

  Scenario: Makes unknown URLs clickable
    When I run `wp embed fetch https://foo.example.com`
    Then STDOUT should contain:
      """
      <a href="https://foo.example.com">https://foo.example.com</a>
      """

  Scenario: Caches oEmbdd response data for a given post
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
    When I run `wp embed fetch https://www.youtube.com/watch?v=dQw4w9WgXcQ --format=xml --raw`
    Then STDOUT should contain:
      """
      <type>video</type>
      """
