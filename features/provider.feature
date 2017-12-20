Feature: Manage oEmbed providers.

  Background:
    Given a WP install

  Scenario: List oEmbed providers
    When I run `wp embed provider list --fields=format,endpoint`
    Then STDOUT should be a table containing rows:
      | format                                                                     | endpoint                                                              |
      | #https?://((m\|www)\.)?youtube\.com/watch.*#i                              | https://www.youtube.com/oembed                                        |
      | #https?://((m\|www)\.)?youtube\.com/playlist.*#i                           | https://www.youtube.com/oembed                                        |
      | #https?://youtu\.be/.*#i                                                   | https://www.youtube.com/oembed                                        |
      | #https?://(.+\.)?vimeo\.com/.*#i                                           | https://vimeo.com/api/oembed.{format}                                 |
      | #https?://(www\.)?dailymotion\.com/.*#i                                    | https://www.dailymotion.com/services/oembed                           |
      | #https?://dai\.ly/.*#i                                                     | https://www.dailymotion.com/services/oembed                           |
      | #https?://(www\.)?flickr\.com/.*#i                                         | https://www.flickr.com/services/oembed/                               |
      | #https?://flic\.kr/.*#i                                                    | https://www.flickr.com/services/oembed/                               |
      | #https?://(.+\.)?smugmug\.com/.*#i                                         | https://api.smugmug.com/services/oembed/                              |
      | #https?://(www\.)?hulu\.com/watch/.*#i                                     | http://www.hulu.com/api/oembed.{format}                               |
      | http://i*.photobucket.com/albums/*                                         | http://api.photobucket.com/oembed                                     |
      | http://gi*.photobucket.com/groups/*                                        | http://api.photobucket.com/oembed                                     |
      | #https?://(www\.)?scribd\.com/doc/.*#i                                     | https://www.scribd.com/services/oembed                                |
      | #https?://wordpress\.tv/.*#i                                               | https://wordpress.tv/oembed/                                          |
      | #https?://(.+\.)?polldaddy\.com/.*#i                                       | https://polldaddy.com/oembed/                                         |
      | #https?://poll\.fm/.*#i                                                    | https://polldaddy.com/oembed/                                         |
      | #https?://(www\.)?funnyordie\.com/videos/.*#i                              | http://www.funnyordie.com/oembed                                      |
      | #https?://(www\.)?twitter\.com/\w{1,15}/status(es)?/.*#i                   | https://publish.twitter.com/oembed                                    |
      | #https?://(www\.)?twitter\.com/\w{1,15}$#i                                 | https://publish.twitter.com/oembed                                    |
      | #https?://(www\.)?twitter\.com/\w{1,15}/likes$#i                           | https://publish.twitter.com/oembed                                    |
      | #https?://(www\.)?twitter\.com/\w{1,15}/lists/.*#i                         | https://publish.twitter.com/oembed                                    |
      | #https?://(www\.)?twitter\.com/\w{1,15}/timelines/.*#i                     | https://publish.twitter.com/oembed                                    |
      | #https?://(www\.)?twitter\.com/i/moments/.*#i                              | https://publish.twitter.com/oembed                                    |
      | #https?://(www\.)?soundcloud\.com/.*#i                                     | https://soundcloud.com/oembed                                         |
      | #https?://(.+?\.)?slideshare\.net/.*#i                                     | https://www.slideshare.net/api/oembed/2                               |
      | #https?://(www\.)?instagr(\.am\|am\.com)/p/.*#i                            | https://api.instagram.com/oembed                                      |
      | #https?://(open\|play)\.spotify\.com/.*#i                                  | https://embed.spotify.com/oembed/                                     |
      | #https?://(.+\.)?imgur\.com/.*#i                                           | https://api.imgur.com/oembed                                          |
      | #https?://(www\.)?meetu(\.ps\|p\.com)/.*#i                                 | https://api.meetup.com/oembed                                         |
      | #https?://(www\.)?issuu\.com/.+/docs/.+#i                                  | https://issuu.com/oembed_wp                                           |
      | #https?://(www\.)?collegehumor\.com/video/.*#i                             | https://www.collegehumor.com/oembed.{format}                          |
      | #https?://(www\.)?mixcloud\.com/.*#i                                       | https://www.mixcloud.com/oembed                                       |
      | #https?://(www\.\|embed\.)?ted\.com/talks/.*#i                             | https://www.ted.com/services/v1/oembed.{format}                       |
      | #https?://(www\.)?(animoto\|video214)\.com/play/.*#i                       | https://animoto.com/oembeds/create                                    |
      | #https?://(.+)\.tumblr\.com/post/.*#i                                      | https://www.tumblr.com/oembed/1.0                                     |
      | #https?://(www\.)?kickstarter\.com/projects/.*#i                           | https://www.kickstarter.com/services/oembed                           |
      | #https?://kck\.st/.*#i                                                     | https://www.kickstarter.com/services/oembed                           |
      | #https?://cloudup\.com/.*#i                                                | https://cloudup.com/oembed                                            |
      | #https?://(www\.)?reverbnation\.com/.*#i                                   | https://www.reverbnation.com/oembed                                   |
      | #https?://videopress\.com/v/.*#                                            | https://public-api.wordpress.com/oembed/?for=http%3A%2F%2Fexample.com |
      | #https?://(www\.)?reddit\.com/r/[^/]+/comments/.*#i                        | https://www.reddit.com/oembed                                         |
      | #https?://(www\.)?speakerdeck\.com/.*#i                                    | https://speakerdeck.com/oembed.{format}                               |
      | #https?://www\.facebook\.com/.*/posts/.*#i                                 | https://www.facebook.com/plugins/post/oembed.json/                    |
      | #https?://www\.facebook\.com/.*/activity/.*#i                              | https://www.facebook.com/plugins/post/oembed.json/                    |
      | #https?://www\.facebook\.com/.*/photos/.*#i                                | https://www.facebook.com/plugins/post/oembed.json/                    |
      | #https?://www\.facebook\.com/photo(s/\|\.php).*#i                          | https://www.facebook.com/plugins/post/oembed.json/                    |
      | #https?://www\.facebook\.com/permalink\.php.*#i                            | https://www.facebook.com/plugins/post/oembed.json/                    |
      | #https?://www\.facebook\.com/media/.*#i                                    | https://www.facebook.com/plugins/post/oembed.json/                    |
      | #https?://www\.facebook\.com/questions/.*#i                                | https://www.facebook.com/plugins/post/oembed.json/                    |
      | #https?://www\.facebook\.com/notes/.*#i                                    | https://www.facebook.com/plugins/post/oembed.json/                    |
      | #https?://www\.facebook\.com/.*/videos/.*#i                                | https://www.facebook.com/plugins/video/oembed.json/                   |
      | #https?://www\.facebook\.com/video\.php.*#i                                | https://www.facebook.com/plugins/video/oembed.json/                   |
      | #https?://(www\.)?screencast\.com/.*#i                                     | https://api.screencast.com/external/oembed                            |
      | #https?://([a-z0-9-]+\.)?amazon\.(com\|com\.mx\|com\.br\|ca)/.*#i          | https://read.amazon.com/kp/api/oembed                                 |
      | #https?://([a-z0-9-]+\.)?amazon\.(co\.uk\|de\|fr\|it\|es\|in\|nl\|ru)/.*#i | https://read.amazon.co.uk/kp/api/oembed                               |
      | #https?://([a-z0-9-]+\.)?amazon\.(co\.jp\|com\.au)/.*#i                    | https://read.amazon.com.au/kp/api/oembed                              |
      | #https?://([a-z0-9-]+\.)?amazon\.cn/.*#i                                   | https://read.amazon.cn/kp/api/oembed                                  |
      | #https?://(www\.)?a\.co/.*#i                                               | https://read.amazon.com/kp/api/oembed                                 |
      | #https?://(www\.)?amzn\.to/.*#i                                            | https://read.amazon.com/kp/api/oembed                                 |
      | #https?://(www\.)?amzn\.eu/.*#i                                            | https://read.amazon.co.uk/kp/api/oembed                               |
      | #https?://(www\.)?amzn\.in/.*#i                                            | https://read.amazon.in/kp/api/oembed                                  |
      | #https?://(www\.)?amzn\.asia/.*#i                                          | https://read.amazon.com.au/kp/api/oembed                              |
      | #https?://(www\.)?z\.cn/.*#i                                               | https://read.amazon.cn/kp/api/oembed                                  |
      | #https?://www\.someecards\.com/.+-cards/.+#i                               | https://www.someecards.com/v2/oembed/                                 |
      | #https?://www\.someecards\.com/usercards/viewcard/.+#i                     | https://www.someecards.com/v2/oembed/                                 |
      | #https?://some\.ly\/.+#i                                                   | https://www.someecards.com/v2/oembed/                                 |

  Scenario: Match an oEmbed provider
    When I run `wp embed provider match https://www.youtube.com/watch?v=dQw4w9WgXcQ`
    And STDOUT should be:
      """
      https://www.youtube.com/oembed
      """
    And STDERR should be empty
