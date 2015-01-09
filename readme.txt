=== Super Simple Twitter Feed ===
Contributors: designedbw 
Tags: Twitter, Feed
Requires at least: 3.6
Tested up to: 4.1
Stable tag: 2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Gets your latest tweet. This plugin uses Twitter API (V1.1). It also uses cURL which needs to be enabled on your server.

== Description ==

Gets your latest tweet. This plugin uses Twitter API (V1.1). It also uses cURL which needs to be enabled on your server 
or host environment.We did our best to provide us with something we needed for our site - a simple single latest tweet 
that was very raw in format (text of the tweet and sometimes formatted HTML anchor tag or link). We thought others might 
find this useful too - so we decided to contribute it to the Wordpress plugin repository.

We also tried to keep the code concise and clean and in one file on purpose. We didn't think calling 
something "super simple" warranted PHP classes, include files and CSS and such. We just wanted the tweet!

We use this plugin @ http://www.betterweatherinc.com/seed

== Installation ==

1. Upload `super-simple-twitter-feed/supersimpletwitterfeed.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set Options for Twitter from the `Settings/sstf Settings` Admin Menu Link
3. Place `<?php echo sstf_gettwitterfeed(); ?>` in your php templates
4. Place shortcode `[sstfeed]` in your post(s)

== Frequently Asked Questions ==

= Does this plugin use a timed cache for reducing "Rate Limiting" imposed by Twitter =

Yes.

= Will you think your life is "better" because of this plugin? =

Perhaps.

== Screenshots ==

1. Settings Screen from Wordpress Admin.
2. Shortcode Use in Post.

== Changelog ==

= 1.0.1 =
* Changed Admin fields to use drop down lists to make it even more "super simple" 

= 1.0 =
* First release

== Upgrade Notice ==

= 1.0.1 =
New version uses dropdown for selecting Cache Time and HTML Tag.