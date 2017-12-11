Feature: Manage embed handlers.

  Background:
    Given a WP install

  Scenario: List embed handlers
    When I run `wp embeds handler list --fields=priority,id`
    Then STDOUT should be a table containing rows:
      | priority | id                |
      | 10       | youtube_embed_url |
      | 9999     | audio             |
      | 9999     | video             |
