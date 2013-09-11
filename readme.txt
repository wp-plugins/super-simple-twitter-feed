=== Super Simple Twitter Feed ===
Contributors: designedbw 
Tags: Twitter, Feed
Requires at least: 3.2
Tested up to: 3.5.2
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gets your latest tweet. This plugin uses Twitter API (V1.1). It also uses cURL which needs to be enabled on your server (typically it is).

== Description ==

Now gets your latest tweet OR tweets! This plugin uses Twitter API (V1.1). It also uses cURL which needs to be enabled 
on your server or host environment. We've updated the plugin from getting a single tweet and formatting only urls as 
links to allowing you to choose how many tweets you want, how to format them, AND formatting hash tags "#" and "@" text 
as links. ALL of which is configurable by you! In keeping with the "Simple" theme, these choices are easy to make in the 
settings of the plugin.

We've also kept the code concise and clean and in one file on purpose.

Portions of this plugin are in use @ http://www.betterweatherinc.com/

== Installation ==

1. Upload `super-simple-twitter-feed/supersimpletwitterfeed.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set Options for Twitter from the `Settings/sstf Settings` Admin Menu Link
3. Place `<?php echo sstf_gettwitterfeed(); ?>` in your php templates
4. Place shortcode `[sstfeed]` in your post(s)
5. Use the widget to place it in your sidebar or other areas!

== Frequently Asked Questions ==

= Does this plugin use a timed cache for reducing "Rate Limiting" imposed by Twitter =

Yes.

= Can I make a rainbow out of shrinky dinks? =

Most definitely.

== Screenshots ==

1. Settings Screen from Wordpress Admin.
2. Shortcode Use in Post.
3. Widget Example

== Changelog ==

= 2.0 =
* Added the ability to choose number of tweets to return (1-10)
* Added the ability to format @ & # tags as links (and urls)
* Reconfigured how the widget was leveraging cache
* Testing mode now instantly removes tweets from cache

= 1.0.5 =
* Removed word `cached` used in testing from feed results when using an HTML tag. (Sorry Folks!)

= 1.0.4 =
* Fixed bug where cached value would not be overwritten when it should expire 
* Fixed bug for `Do Not Cache` testing value.

= 1.0.3 =
* Modification made to make better use of Cached feed when using the template function code.

= 1.0.2 =
* Added widget functionality - you can now use the widget to place the tweet in your theme. Super Simple!

= 1.0.1 =
* Changed Admin fields to use drop down lists to make it even more "super simple" 

= 1.0 =
* First release

== Upgrade Notice ==

= 2.0 =
* Added the ability to choose number of tweets to return (1-10)
* Added the ability to format @ & # tags as links (and urls)
* Reconfigured how the widget was leveraging cache
* Testing mode now instantly removes tweets from cache
