=== Plugin Name ===
Contributors: designedbw 
Tags: Twitter, Feed
Requires at least: 3.2
Tested up to: 3.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gets your latest tweet. This plugin uses Twitter API (V1.1). It uses CURL which needs to be enabled on your server.

== Description ==

Uses the Twitter User Timeline to get your latest tweet. This plugin uses Twitter API (V1.1). 
It uses CURL which needs to be enabled on your server or host environment.We did our best to 
provide us with something we needed for our site - a simple single latest tweet that was very 
"raw" in format (text of the tweet and sometimes formatted HTML anchor tag or link).

We also tried our best to keep the code concise and clean and in one file on purpose. We didn't 
think calling something "super simple" warranted PHP classes and include files and CSS and such. 
We just wanted the tweet!

You can see this plugin by Betterweather Inc. in action @ http://www.betterweatherinc.com/seed

== Installation ==

1. Upload `super-simple-twitter-feed/supersimpletwitterfeed.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the Settings Page to setup your Twitter API account information
3.1 Twitter Cache Length is suggested as: `60*60*.25` (15 minutes or greater on high volume sites)
3.2 Wrap your Tweet With HTML - not required, but may be helpful for design purposes (we add the class "sstfeed" to any html element you choose)
3.2.1 DO NOT USE "<" or ">" - simply put the tag you want the tweet wrapped in.
4. Place `<?php echo sstf_gettwitterfeed(); ?>` in your templates - no sidebar widget is provided yet :)
5. Place shortcode `[sstfeed]` in your post(s)

== Frequently Asked Questions ==

= Does this plugin use a timed cache for reducing "Rate Limiting" imposed by Twitter =

Yes.

== Screenshots ==

1. Settings Screen from Wordpress Admin.
2. Shortcode Used in Post.

== Changelog ==

= 1.0 =
* First release
