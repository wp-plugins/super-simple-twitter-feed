<?php 
/*
Plugin Name: Super Simple Twitter Feed
Plugin URI: http://www.betterweatherinc.com/sstf
Description: Gets your latest tweet. This plugin uses Twitter API (V1.1). It uses CURL which needs to be enabled on your server or host environment.
Author: Betterweather Inc. - Inspired by http://stackoverflow.com/users/695192/rivers
Version: 1.0
Author URI: http://www.betterweatherinc.com
*/
/*  Copyright 2013  Betterweather Inc.  (email : designed@betterweatherinc.com)

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
/*
 * Primary function which returns the twitter feed
 * 
 * @return string
 */ 
function sstf_gettwitterfeed(){
	global $wp_version;
	
	if(!version_compare($wp_version, "3.2", ">=")){
		die('You must have at least Wordpress Version 3.2 or greater to use the Simple Twitter Feed plugin!');	
	}
	
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
		$string = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i", "$1http://$2",$string);
		/*** make all URLs links ***/
		$string = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">$1</A>",$string);
		/*** make all emails hot links (just in case ;-) ***/
		$string = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<A HREF=\"mailto:$1\">$1</A>",$string);
		
		return $string;
	}	
	
		
	$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
		
	$consumer = trim(esc_attr(get_option('sstf_consumer_screen_name')));
	$consumer_key = trim(esc_attr(get_option('sstf_consumer_key')));
	$consumer_secret = trim(esc_attr(get_option('sstf_consumer_secret'))); 
	$oauth_access_token = trim(esc_attr(get_option('sstf_consumer_token'))); 
	$oauth_access_token_secret = trim(esc_attr(get_option('sstf_consumer_token_secret'))); 
	
	
	$oauth = array( 'oauth_consumer_key' => $consumer_key, 'oauth_nonce' => time(), 'oauth_signature_method' => 'HMAC-SHA1', 'oauth_token' => $oauth_access_token, 'oauth_timestamp' => time(), 'oauth_version' => '1.0', 'count' => 1, 'screen_name' => $consumer );
	
	$base_info = buildBaseString($url, 'GET', $oauth); 
	$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret); 
	$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true)); 
	$oauth['oauth_signature'] = $oauth_signature;
	
	
	// Make cURL Request to Twitter 
	$header = array(buildAuthorizationHeader($oauth), 'Expect:'); 
	$options = array( 
		CURLOPT_HTTPHEADER => $header, 
		CURLOPT_HEADER => false, 
		CURLOPT_URL => $url . '?screen_name='.$consumer.'&count=1', 
		CURLOPT_RETURNTRANSFER => true, 
		CURLOPT_SSL_VERIFYPEER => false
		);
	
	$feed = curl_init(); 
	curl_setopt_array($feed, $options); 
	$json = curl_exec($feed); 
	curl_close($feed);
		
	$twitter_data = json_decode($json, true);
	
	//format, cache and return the feed
	$sstfTweet = makeLink($twitter_data[0]['text']);
	$sstfElement = trim(esc_attr(get_option('sstf_consumer_element')));
	if($sstfElement){
		$sstfTweet = "<".$sstfElement." class=\"sstfeed\">".$sstfTweet."</".$sstfElement.">";	
	}	 
	
	$sstf_cache_time = trim(esc_attr(get_option('sstf_consumer_twitter_cache_time')));
	if($sstf_cache_time==null){
		$sstf_cache_time = '60*60*.25';
	}
	set_transient('sstf_cached', $sstfTweet, $sstf_cache_time);
	return get_transient('sstf_cached');

}
function sstf_ShortCode(){
	$sstf_transient = get_transient('sstf_cached');
	if ( empty( $sstf_transient ) ){
		return sstf_gettwitterfeed();
	}
	else {
		return $sstf_transient;
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
						<span class="description">(Without the "@" symbol)</span>
					</td>
				</tr>
				<tr class="odd" valign="top">
					<th scope="row"><label for="sstf_consumer_key">Consumer Key: </label></th>				
					<td><input type="text" id="sstf_consumer_key" name="sstf_consumer_key" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_key')); ?>" /></td>
				</tr>
				<tr class="even" valign="top">
					<th scope="row"><label for="sstf_consumer_secret">Consumer Secret: </label></th>
					<td><input type="text" id="sstf_consumer_secret" name="sstf_consumer_secret" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_secret')); ?>" /></td>
				</tr>
				<tr class="odd" valign="top">
					<th scope="row"><label for="sstf_consumer_token">Access Token: </label></th>
					<td><input type="text" id="sstf_consumer_token" name="sstf_consumer_token" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_token')); ?>" /></td>
				</tr>
				<tr class="even" valign="top">
					<th scope="row"><label for="sstf_consumer_token_secret">Access Token Secret: </label></th>
					<td><input type="text" id="sstf_consumer_token_secret" name="sstf_consumer_token_secret" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_token_secret')); ?>" /></td>
				</tr>
				<tr class="odd" valign="top">
					<th scope="row"><label for="sstf_consumer_twitter_cache_time">Twitter Cache Length: </label></th>
					<td><input type="text" id="sstf_consumer_twitter_cache_time" name="sstf_consumer_twitter_cache_time" class="regular-text" value="<?php echo esc_attr(get_option('sstf_consumer_twitter_cache_time')); ?>" />
						<span class="description">Example (15 minutes): 60*60*.25 --- 60 seconds * 60 minutes * .25 hours : Set to 0 recommended for testing ONLY</span>
					</td>
				</tr>
				<tr class="even" valign="top">
					<th scope="row"><label for="sstf_consumer_element">Wrap Your Tweet With HTML: </label></th>
					<td><input type="text" id="sstf_consumer_element" name="sstf_consumer_element" value="<?php echo esc_attr(get_option('sstf_consumer_element')); ?>" />
						<span class="description">Example: div or p or q or h2, h3, h4, h5 (No "<" or ">" Needed it will break the code!")</span>
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







































