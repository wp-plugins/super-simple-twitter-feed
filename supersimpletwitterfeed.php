<?php 
/*
Plugin Name: Super Simple Twitter Feed
Plugin URI: http://www.betterweatherinc.com/seed/super-simple-twitter-feed
Description: Gets your latest tweet(s). This plugin uses Twitter API (V1.1). It also uses cURL which needs to be enabled on your server or host environment (typically it is).
Author: Betterweather Inc.
Version: 2.0
Author URI: http://www.betterweatherinc.com
*/
/*  Copyright 2013  Betterweather Inc.  (email : designed@betterweatherinc.com)
    - Inspired by http://stackoverflow.com/users/695192/rivers

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
$shortCodeRun == FALSE;
/*
 * Primary function which returns the twitter feed wherever it needs to be used
 * 
 * @return string
 */ 
function sstf_gettwitterfeed(){
	global $wp_version;
	
	if(!version_compare($wp_version, "3.2", ">=")){
		die('You must have at least Wordpress Version 3.2 or greater to use the Simple Twitter Feed plugin!');	
	}	
	return getTwitterFeed();
}
/*
 * Secondary function which returns the twitter feed
 * 
 * @return string
 */ 
function getTwitterFeed(){
/*
	 * Build and sort the URL variables for Twitter Oauth Request
	 * 
	 * @param string $baseURI - URI for Twitter
	 * @param string $method  - Method (GET or POST) to use when transmitting
	 * @param array $params   - Required Oauth parameters sent to Twitter
	 * @return string
	 */ 
	function buildBaseString($baseURI, $method, $params) {
		 $r = array(); 
		 ksort($params); 
		 foreach($params as $key=>$value){
		 	 $r[] = "$key=" . rawurlencode($value); 
		 } 
		 return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r)); 
	}
	
	/*
	 * Build the Oauth Header for Twitter
	 * 
	 * @param array $oauth - options for header elements
	 * @return string
	 */	
	function buildAuthorizationHeader($oauth) {
		$r = 'Authorization: OAuth '; 
		$values = array(); 
		foreach($oauth as $key=>$value){ 
			$values[] = "$key=\"" . rawurlencode($value) . "\"";
		} 
		$r .= implode(', ', $values); 
		return $r; 
	}	
	
	/**
	* Make any URLs in Twitter posts clickable links
	* 
	* @param string $string - A string that might contain a URL
	* @return string
	*/
	function makeLink($string){
		/*** make sure there is an http:// on all URLs ***/
		/*$string = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2",$string);*/
	  $string = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $string);
	  $string = preg_replace("#(^|[\n ])((www)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $string);
		/*** Turn Any @ symbol into a link ***/
	  $string = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $string);
		/*** Turn any # symbol into a link ***/
	  $string = preg_replace("/#(\w+)/", "<a href=\"http://twitter.com/search?q=%23\\1\" target=\"_blank\">#\\1</a>", $string);		
		
		return $string;
	}	
	
  /*
	 * Check for Cached Version of Feed
	 * if not cached, create cache version
	 */
	$isScode = $sCode;
	$sstf_transient = get_transient('sstf_cached');
	if ((!($sstf_transient) || (trim(esc_attr(get_option('sstf_consumer_twitter_cache_time')))=='0'))){
		//Remove any Transient Value
		delete_transient('sstf_cached');
		delete_transient('sstf_sCode');
		
		//Oauth settings		
		$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
			
		$consumer = trim(esc_attr(get_option('sstf_consumer_screen_name')));
		$consumer_num = trim(esc_attr(get_option('sstf_consumer_num')));
		$consumer_links = trim(esc_attr(get_option('sstf_consumer_make_links')));
		$consumer_key = trim(esc_attr(get_option('sstf_consumer_key')));
		$consumer_secret = trim(esc_attr(get_option('sstf_consumer_secret'))); 
		$oauth_access_token = trim(esc_attr(get_option('sstf_consumer_token'))); 
		$oauth_access_token_secret = trim(esc_attr(get_option('sstf_consumer_token_secret'))); 
		
		
		$oauth = array( 'oauth_consumer_key' => $consumer_key, 'oauth_nonce' => time(), 'oauth_signature_method' => 'HMAC-SHA1', 'oauth_token' => $oauth_access_token, 'oauth_timestamp' => time(), 'oauth_version' => '1.0', 'count' => $consumer_num, 'screen_name' => $consumer );
		
		$base_info = buildBaseString($url, 'GET', $oauth); 
		$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret); 
		$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true)); 
		$oauth['oauth_signature'] = $oauth_signature;
		
		
		// Make cURL Request to Twitter 
		$header = array(buildAuthorizationHeader($oauth), 'Expect:'); 
		$options = array( 
			CURLOPT_HTTPHEADER => $header, 
			CURLOPT_HEADER => false, 
			CURLOPT_URL => $url . '?screen_name='.$consumer.'&count='.$consumer_num, 
			CURLOPT_RETURNTRANSFER => true, 
			CURLOPT_SSL_VERIFYPEER => false
			);
		
		$feed = curl_init(); 
		curl_setopt_array($feed, $options); 
		$json = curl_exec($feed); 
		curl_close($feed);
			
		$twitter_data = json_decode($json, true);
		
		/*
		 * format the feed
		 */
		$t = 0;
		$sstfTweet = '';
		$sstfTweetLinks = '';
		$wrapTweet = false;
		$closeUl = '';
		$sstfElement = trim(esc_attr(get_option('sstf_consumer_element')));
		if($sstfElement){
			if($sstfElement=="li"){
				$sstfTweetLinks = '<ul class="sstfeedwrap">';
				$closeUl = "</ul>";
			}
			$wrapTweet = true;
		}
		
		foreach($twitter_data as &$tData){
			if($consumer_links=="yes"){
				$sstfTweet = makeLink($twitter_data[$t]['text']);
			}
			else {
				$sstfTweet = $twitter_data[$t]['text'];
			}
			if($wrapTweet){	
				$sstfTweetLinks .= "<".$sstfElement." class=\"sstfeed\">".$sstfTweet.$shortCodeRun."</".$sstfElement.">";
			}
			else {
				$sstfTweetLinks .= $sstfTweet;
			}
			$t++;		
		}
		
		$sstfTweetLinks = $sstfTweetLinks.$closeUl;


		/*
		 * Cache Feed
		 */		
		$sstf_cache_time = intval(trim(esc_attr(get_option('sstf_consumer_twitter_cache_time'))));
		if($sstf_cache_time==null){
			$sstf_cache_time = 900;
		}
		set_transient('sstf_cached', $sstfTweetLinks, $sstf_cache_time);
		if($sCode){
			set_transient('sstf_sCode','TRUE',$sstf_cache_time);
		}
		return get_transient('sstf_cached');
	}
	else {
		return $sstf_transient;
	}	

}

/*
 * ShortCode Function
 * 
 * @string getTwitterFeed() or cached - sstf_transient_sc
 * 
 */
function sstf_ShortCode(){
	$sstf_transient_sc = get_transient('sstf_cached');
	if (($sstf_transient_sc != NULL)&&(trim(esc_attr(get_option('sstf_consumer_twitter_cache_time')))!='0')){
		return $sstf_transient_sc;		
	}
	else {
		return getTwitterFeed();
	}	
}
add_shortcode('sstfeed','sstf_ShortCode');

/*
 * Begin Wordpress Admin Section
 */
 
/*
 * Register Twitter Specific Options
 */

function sstf_init(){
	register_setting('sstf_options','sstf_consumer_screen_name');//todo - add sanitization function ", 'functionName'"
	register_setting('sstf_options','sstf_consumer_num');
	register_setting('sstf_options','sstf_consumer_make_links');
	register_setting('sstf_options','sstf_consumer_key');
	register_setting('sstf_options','sstf_consumer_secret');
	register_setting('sstf_options','sstf_consumer_token');
	register_setting('sstf_options','sstf_consumer_token_secret');
	register_setting('sstf_options','sstf_consumer_element');
	register_setting('sstf_options','sstf_consumer_twitter_cache_time');
} 
add_action('admin_init','sstf_init');

/*
 * Display the Options form for Simple Twitter Feed
 */
function sstf_option_page(){
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Super Simple Twitter Feed Options</h2>
		<p>Here you can set or edit the fields needed for the plugin.</p>
		<p>You can find these settings here: <a href="https://dev.twitter.com/apps" target="_blank">Twitter API</a></p>
		<form action="options.php" method="post" id="sstf-options-form">
			<?php settings_fields('sstf_options'); ?>
			<table class="form-table">
				<tr class="even" valign="top">
					<th scope="row"><label for="sstf_consumer_screen_name">Twitter Screen Name: </label></th>				
					<td><input type="text" id="sstf_consumer_screen_name" name="sstf_consumer_screen_name" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_screen_name')); ?>" />
						<p class="description">(Without the "@" symbol)</p>
					</td>
				</tr>
				<tr class="odd" valign="top">
					<th scope="row"><label for="sstf_consumer_key">Consumer Key: </label></th>				
					<td><input type="text" id="sstf_consumer_key" name="sstf_consumer_key" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_key')); ?>" />
						<p></p>
					</td>
				</tr>
				<tr class="even" valign="top">
					<th scope="row"><label for="sstf_consumer_secret">Consumer Secret: </label></th>
					<td><input type="text" id="sstf_consumer_secret" name="sstf_consumer_secret" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_secret')); ?>" />
						<p></p>
					</td>
				</tr>
				<tr class="odd" valign="top">
					<th scope="row"><label for="sstf_consumer_token">Access Token: </label></th>
					<td><input type="text" id="sstf_consumer_token" name="sstf_consumer_token" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_token')); ?>" />
						<p></p>
					</td>
				</tr>
				<tr class="even" valign="top">
					<th scope="row"><label for="sstf_consumer_token_secret">Access Token Secret: </label></th>
					<td><input type="text" id="sstf_consumer_token_secret" name="sstf_consumer_token_secret" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_token_secret')); ?>" />
						<p></p>
					</td>
				</tr>
				<?php
				/*
				 * Set up for checking current value
				 */
				 $currentCache = trim(esc_attr(get_option('sstf_consumer_twitter_cache_time')));
				 $cacheTimeArray = array(
				 	"300","600","900","1800","3600","21600","0"
				 );
				 $cacheFormArray = array(
				 	"5 minutes","10 minutes","15 minutes","30 minutes", "1 hour", "6 hours","Do Not Cache (Testing Only!)"
				 );				 
				?>
				<tr class="odd" valign="top">
					<th scope="row"><label for="sstf_consumer_twitter_cache_time">Twitter Cache Length: </label></th>
					<td><select id="sstf_consumer_twitter_cache_time" name="sstf_consumer_twitter_cache_time">
						<?php
						$i = 0;
						 foreach($cacheFormArray as &$cacheText){
						 	if($cacheTimeArray[$i]==$currentCache){
						 		echo '<option value="'.$cacheTimeArray[$i].'" selected>';
						 	}
							else{
								echo '<option value="'.$cacheTimeArray[$i].'">';
							}
							echo $cacheText.'</option>';
							$i++;
						 }
						?>
						</select>
						<p class="description">On average, 15 minutes should work well.</p>
					</td>
				</tr>
				<?php
				/*
				 * Set up for checking current value
				 */
				 $currentlinks = trim(esc_attr(get_option('sstf_consumer_make_links')));
				 $linksArray   = array(
				 	"yes","no"
				 );
				?>
				<tr class="even" valign="top">
					<th scope="row"><label for="sstf_consumer_make_links">Make Links Work: </label></th>
					<td><select id="sstf_consumer_make_links" name="sstf_consumer_make_links">
							<?php 
								foreach($linksArray as &$link){
									if($currentlinks==$link){
										echo '<option value="'.$link.'" selected>';
									}
									else {
										echo '<option value="'.$link.'">';
									}
									echo $link.'</option>';
								}
							?>
							</select>
							<p class="description">Choose "yes" to turn on Hash Tags, "@" and URL's into active links (otherwise they show as text only).</p>
					</td>
				</tr>							
				<?php
				/*
				 * Set up for checking current value
				 */
				 $currentnum   = trim(esc_attr(get_option('sstf_consumer_num')));
				 $countFormArray = array(
				 	"1","2","3","4","5","6","7","8","9","10"
				 );
				?>
				<tr class="odd" valign="top">
					<th scope="row"><label for="sstf_consumer_num">Number of Tweets to show: </label></th>
					<td><select id="sstf_consumer_num" name="sstf_consumer_num">
							<?php 
								foreach($countFormArray as &$num){
									if($currentnum==$num){
										echo '<option value="'.$num.'" selected>';
									}
									else {
										echo '<option value="'.$num.'">';
									}
									echo $num.'</option>';
								}
							?>
							</select>
					</td>
				</tr>
				<?php
				/*
				 * Set up for checking current value
				 */
				 $currentElement = trim(esc_attr(get_option('sstf_consumer_element')));
				 $elementArray = array(
				 	"div","li","p","q","h2","h3","h4","h5"
				 );				 
				?>
				<tr class="even" valign="top">
					<th scope="row"><label for="sstf_consumer_element">Wrap Your Tweet(s) With HTML: </label></th>
					<td><select id="sstf_consumer_element" name="sstf_consumer_element">
						<option value="">Select an HTML Tag  </option>
						<?php
							foreach($elementArray as &$value){
								if($currentElement==$value){
									echo '<option value="'.$value.'" selected>';	
								}
								else {
									echo '<option value="'.$value.'">';
								}
								echo $value.'</option>';
							}
						?>
						</select>
						<p class="description">This will wrap your tweet with the desired HTML tag. (Note: if "li" is selected the outer warapper will be "ul")</p>
					</td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="submit" class="button-primary" value="Save Settings" /></p>			
		</form>
	</div>
	<?php
}

/*
 * Setup Admin menu item
 */
function sstf_plugin_menu(){
	add_options_page('Super Simple Twitter Settings','sstf Settings','manage_options','sstf-plugin','sstf_option_page');
}

/*
 * Make Admin Menu Item
 */
add_action('admin_menu','sstf_plugin_menu');

/*
 * SSTF Widget
 * enables the ability to use a widget to place the tweet feed in the widget areas 
 * of a theme.
 */
 class sstfWidget extends WP_Widget
 {
 		
 	/*
	 * Register the widget for use in WordPress
	 */ 
 	public function sstfWidget()
 	{
 		$widget_options = array(
			'classname' => 'sstf_widget',
			'description' => 'Super Simple Twitter Feed Widget, Displays your latest Tweet'
		);
		parent::WP_Widget('sstf_widget','sstf Widget',$widget_options);
 	}
	
	/*
	 * widget handles the front end display of widget title
	 * 
	 * @param array $args - arguments from the theme (html berfore and after the widget)
	 * @param array $instance - holds saved values from database
	 */
	public function widget($args, $instance)
	{
		extract($args, EXTR_SKIP);	
		$title = ($instance['title']) ? $instance['title'] : '';
		$sstf_transient_sc = get_transient('sstf_cached');
		if(($sstf_transient_sc) && ((trim(esc_attr(get_option('sstf_consumer_twitter_cache_time')))!='0'))){
			$body = $sstf_transient_sc;
		}
		elseif((!($sstf_transient_sc)) && ((trim(esc_attr(get_option('sstf_consumer_twitter_cache_time')))!='0'))){
			$body = getTwitterFeed();
		}
		elseif((trim(esc_attr(get_option('sstf_consumer_twitter_cache_time'))))=='0') {
			if($sstf_transient_sc){
				$body = $sstf_transient_sc;
				delete_transient(sstf_cached);
			}
			else {
				$body = getTwitterFeed();
			}
		}	
		?>
		<?php 
			echo $before_widget;
			if($title){
				 echo $before_title . $title . $after_title; 
			} 
			echo $body; 
			echo $after_widget; 
		?>
		<?php
	}
	
	/*
	 * No current need to override the WP_Widget class function
	 * 
	 * public function update(){}
	 */
	
	/*
	 * Admin form for widget
	 * 
	 * @param array $instance - previously saved values from the database
	 */ 
	public function form($instance)
	{
		?>
		<label for="<?php echo $this->get_field_id('title'); ?>">
			Title:
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($instance['title']); ?>" class="widefat" />
		<p class="description">Leave blank for no tweet title.</p>
		</label>
		
		<?php
		
	}
				
 }
 /*
  * Remove Cached Data on Settings Updates
  */
 function sstf_save_post_data(){
 	if(get_transient('sstf_cached')){
 		delete_transient('sstf_cached');
	}
 }
 if(isset($_GET['settings-updated'])){
	 sstf_save_post_data();
 }

 /*
  * Register the sstfWidget widget
  */
function sstf_widget_init()
{
	register_widget('sstfWidget');
}
add_action('widgets_init', 'sstf_widget_init');
