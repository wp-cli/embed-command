Feature: Manage embed handlers.

  Scenario: List embed handlers
    Given a WP install

    When I run `wp embeds handler list --fields=priority,id`
    Then STDOUT should be a table containing rows:
      | priority | id                |
      | 10       | youtube_embed_url |
      | 9999     | audio             |
      | 9999     | video             |
