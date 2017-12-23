Feature: Manage oEmbed providers.

  Background:
    Given a WP install

  Scenario: List oEmbed providers
    When I run `wp embed provider list --fields=format,endpoint`
    Then STDOUT should contain:
      """
      youtube\.com/watch.*
      """
    And STDOUT should contain:
      """
      //www.youtube.com/oembed
      """
    And STDOUT should contain:
      """
      flickr\.com/
      """
    And STDOUT should contain:
      """
      flickr.com
      """
    And STDOUT should contain:
      """
      twitter\.com/
      """
    And STDOUT should contain:
      """
      twitter.com
      """
    And STDOUT should contain:
      """
      \.spotify\.com/
      """
    And STDOUT should contain:
      """
      spotify.com
      """

  Scenario: Match an oEmbed provider
    When I run `wp embed provider match https://www.youtube.com/watch?v=dQw4w9WgXcQ`
    And STDOUT should be:
      """
      //www.youtube.com/oembed
      """
    And STDERR should be empty
