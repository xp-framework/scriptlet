Web configuration
=================

The XP web layout can be configured via an ini file named **web.ini**.
Here is an example:

  *[app]*
  mappings="/rss:rss|/:site"

  *[app::rss]*
  class="com.example.site.scriptlet.RssFeed"
  filters="com.example.site.Ratelimit"
  debug="TRACE|STACKTRACE|ERRORS"

  *[app::site]*
  class="com.example.site.scriptlet.Site"
  prop-base="etc/`{PROFILE}`"
  init-params="com.example.site.scriptlet|`{WEBROOT}`/xsl"
  init-envs="DEF_PROD:example|DEF_LANG:en_US|DEF_STATE:static"


The placeholders `{PROFILE}` and `{WEBROOT}` are expanded with their 
respective runtime values.