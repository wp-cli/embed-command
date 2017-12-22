Feature: Manage embed handlers.

  Background:
    Given a WP install

  # See https://core.trac.wordpress.org/changeset/37744
  @require-wp-4.6
  Scenario: List embed handlers
    When I run `wp embed handler list --fields=priority,id`
    Then STDOUT should be a table containing rows:
      | priority | id                |
      | 10       | youtube_embed_url |
      | 9999     | audio             |
      | 9999     | video             |
