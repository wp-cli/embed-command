Feature: Manage oEmbed cache.

  Background:
    Given a WP install

  Scenario: Clear oEmbed cache for an empty post
    When I run `wp post create --post_title="Foo Bar" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {POST_ID}

    When I run `wp embed cache clear {POST_ID}`
    Then STDERR should be:
      """
      Error: No cache to clear!
      """

  Scenario: Clear oEmbed cache for a post
    When I run `wp post-meta add 1 _oembed_foo 'bar'`
    Then STDOUT should not be empty

    When I run `wp post-meta get 1 _oembed_foo`
    Then STDOUT should be:
      """
      bar
      """

    When I run `wp embed cache clear 1`
    Then STDOUT should be:
      """
      Success: Cleared oEmbed cache.
      """

  Scenario: Trigger oEmbed cache for a non-existent post
    When I try `wp embed cache trigger 123456`
    Then STDERR should contain:
      """
      Post 123456 does not exist!
      """
    And the return code should be 0

  Scenario: Trigger oEmbed cache for a hidden post
    When I run `wp post create --post_title="Foo Bar" --post_type=revision --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {POST_ID}

    When I try `wp embed cache trigger {POST_ID}`
    Then STDERR should contain:
      """
      Cannot cache oEmbed results for revision post type
      """
    And the return code should be 0

  @require-wp-4.9
  Scenario: Find oEmbed cache post ID for a non-existent key
    When I try `wp embed cache find foo`
    Then STDERR should be:
      """
      Error: No cache post ID found!
      """
    And the return code should be 1

  @require-wp-4.9
  Scenario: Find oEmbed cache post ID for an existing key
    When I run `wp eval 'echo md5( "foo" . serialize( array( "width" => 600, "height" => 400 ) ) );'`
    Then STDOUT should not be empty
    And save STDOUT as {CACHE_KEY}

    When I run `wp post create --post_title=Foo --post_name={CACHE_KEY} --post_type=oembed_cache --post_status=publish --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {POST_ID}

    When I run `wp embed cache find foo --width=600 --height=400`
    Then STDOUT should be:
      """
     {POST_ID}
      """
