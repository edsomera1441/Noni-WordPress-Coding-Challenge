<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              noni
 * @since             1.0.0
 * @package           Noni_Wordpress_Coding_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Noni-Wordpress-Coding-Challenge
 * Plugin URI:        noni
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Nonibrands
 * Author URI:        noni
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       noni-wordpress-coding-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NONI_WORDPRESS_CODING_PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-noni-wordpress-coding-plugin-activator.php
 */
function activate_noni_wordpress_coding_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-noni-wordpress-coding-plugin-activator.php';
	Noni_Wordpress_Coding_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-noni-wordpress-coding-plugin-deactivator.php
 */
function deactivate_noni_wordpress_coding_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-noni-wordpress-coding-plugin-deactivator.php';
	Noni_Wordpress_Coding_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_noni_wordpress_coding_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_noni_wordpress_coding_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-noni-wordpress-coding-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_noni_wordpress_coding_plugin() {

	$plugin = new Noni_Wordpress_Coding_Plugin();
	$plugin->run();

}
run_noni_wordpress_coding_plugin();

add_action('admin_menu', 'addNoniMenu');

function addNoniMenu(){
	add_menu_page('Part 1', 'API Connection', 'manage_options', 'noni-part-1', 'part1Contents');
	add_menu_page('Part 2', 'Form Validation', 'manage_options', 'noni-part-2', 'part2Contents');
}

function part1Contents(){

	$apiKeyOption = get_option('news_api_key','');

	echo '<div class="wrap">';

	if(array_key_exists('submit-button',$_POST)){

		if(isAPIKeyValid($_POST['api-key'])){

			echo '<div class="updated settings-error notice is-dismissible">You may now use the shortcode \'[noni-wordpress-api]\' to display the news.</div>';
			$apiKeyOption = $_POST['api-key'];

		}else{

			echo '<div class="updated settings-error notice is-dismissible">Invalid API Key.</div>';
			$apiKeyOption = '';

		}

	}

		echo '<h2>API Connection</h2>';
		echo '<br/>';
		echo '<form method="post">';
		echo '<label for="api-key">Enter API Key: ';
		echo '<input type="text" name="api-key" value="' . $apiKeyOption . '">';
		echo '<input type="submit" name="submit-button">';
		echo '</form>';

	echo '</div>';
}

function isAPIKeyValid($apiKey){

	$url = 'https://newsapi.org/v2/top-headlines?country=us&apiKey=' . $apiKey;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	curl_close($ch);

	$obj = json_decode($result, true);
	
	if($obj['status'] == 'error'){
		update_option('news_api_key', '');
		return false;
	}
	else if($obj['status'] == 'ok'){

		update_option('news_api_key', $apiKey);
		return true;

	}
}

function parseNews($apiKey, $titleKeyWord = null){

	if($titleKeyWord == null){
		$url = 'https://newsapi.org/v2/top-headlines?country=us&apiKey=' . $apiKey;
	}else{
		$url = 'https://newsapi.org/v2/top-headlines?country=us&apiKey=' . $apiKey . '&q=' . urlencode($titleKeyWord);
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	curl_close($ch);

	$obj = json_decode($result, true);
	
	if($obj['status'] == 'ok'){

		return $obj;

	}else{

		return null;

	}

}


function displayNewsFromAPI(){

	$apiKeyOption = get_option('news_api_key','');

	if($apiKeyOption == ''){

		echo '<p>Please check your API Key.</p>';

	}else{

		if($_GET['noni-news-search']!=null){
		
			//post has a search of title
			$titleFilter = $_GET['noni-news-search'];

			if(parseNews($apiKeyOption, $titleFilter)!=null){
				$news = parseNews($apiKeyOption, $titleFilter);
			}else{
				echo '<p>Please check your API Key.</p>';
				return;
			}

			unset($_GET['noni-news-search']);

			

		}else{

			if(parseNews($apiKeyOption)!=null){
				$news = parseNews($apiKeyOption);
			}else{
				echo '<p>Please check your API Key.</p>';
				return;
			}

			
		}

		$newsArticles = $news['articles'];

		echo '<div id="noni-news">';

			echo '<div class="noni-search">';

				echo '<h3>Can\'t find what you\'re looking for?</h3>';

				echo '<form style="margin-bottom: 10px;" method="get">';
					echo '<input style="width: 32%;" type="text" name="noni-news-search" placeholder="Enter title to filter search">';
					echo '<input type="submit" value="SUBMIT">';
				echo '</form>';

			echo '</div>';

		if(count($newsArticles) == 0){
			echo '<p>The search returned no results.</p>';
		}


		foreach($newsArticles as $singleArticle){

			echo '<div class="noni-news-article">';

				echo '<div class="noni-news-thumbnail">';

					echo '<a href="' . $singleArticle['url'] . '">';
						echo '<img src="'. $singleArticle['urlToImage'] .'">';
					echo '</a>';	
				
				echo '</div>';

				echo '<div class="noni-news-text">';

					echo '<a href="' . $singleArticle['url'] . '">';
						echo '<h3>'. $singleArticle['title']  .'</h3>';
						echo '<h4>'. $singleArticle['author'] .'</h4>';
						echo '<p class="snippet">'. $singleArticle['description'] .'</p>';
					echo '</a>';	

				echo '</div>';

			echo '</div>';

		}

		echo '</div>';

	}

}

add_shortcode('noni-wordpress-api', 'displayNewsFromAPI');

function part2Contents(){
	echo '<div class="wrap">';
	?>

			<h2>These are the addresses that have been entered within the form.</h2>
			<p>Please use the shortcode to display the form: [noni-wordpress-form]</p>
			<br/>

			<table>
			<tbody>
				<tr>
					<th>ID</th>
					<th>Address 1</th>
					<th>City</th>
					<th>Province</th>
					<th>Postal Code</th>
					<th>Country</th>
				</tr>

		<?php 
		
				$addresses = retrieveAddresses(); 
				
				foreach($addresses as $singleAddress){

				echo '<tr>';
					echo '<td>' . $singleAddress->id . '</td>';
					echo '<td>' . $singleAddress->address1 . '</td>';
					echo '<td>' . $singleAddress->city . '</td>';
					echo '<td>' . $singleAddress->province . '</td>';
					echo '<td>' . $singleAddress->postal_code . '</td>';
					echo '<td>' . $singleAddress->country . '</td>';
				echo '</tr>';	

				}
		
		?>

			</tbody>
			</table>	


		<?php
	echo '</div>';
}

function retrieveAddresses(){

	global $wpdb;

	$table_name = $wpdb->prefix . 'noni_addresses'; 

	$sql = 'SELECT *  FROM ' . $table_name;

	return $wpdb->get_results($sql);
}

add_shortcode('noni-wordpress-form', 'displayForm');

function displayForm(){
	echo '<div id="noni-address-form">';

		if(array_key_exists('noni-form-submit', $_POST)){
			echo '<h3>Thank you for submitting your address.</h3>';
			echo '<h4> This will be shown in our Form Validation plugin.</h4>';
		}	

		echo '<form method="post">';

			echo '<label for="address1">Address 1: </label>';
			echo '<br/>';
			echo '<input type="text" required="required" name="address1" placeholder="Address 1">';
			echo '<br/>';

			echo '<label for="city">City: </label>';
			echo '<br/>';
			echo '<input type="text" required="required" name="city" placeholder="City">';
			echo '<br/>';

			echo '<label for="province">Province: </label>';
			echo '<br/>';
			echo '<input type="text" required="required" name="province" placeholder="Province">';
			echo '<br/>';

			echo '<label for="postalCode">Postal Code: </label>';
			echo '<br/>';
			echo '<input type="number" required="required" name="postalCode" placeholder="Postal Code">';
			echo '<br/>';

			echo '<label for="country">Country: </label>';
			echo '<br/>';
			echo '<input type="text" required="required" name="country" placeholder="Country">';
			echo '<br/>';
			echo '<br/>';

			echo '<input type="submit" required="required" name="noni-form-submit" value="SUBMIT">';

		echo '</form>';
	echo '</div>';
}

add_action('wp_head', 'noniFormCapture');

function noniFormCapture(){
	if(array_key_exists('noni-form-submit', $_POST)){

		$address = $_POST['address1'];
		$city = $_POST['city'];
		$province = $_POST['province'];
		$postalCode = (int)$_POST['postalCode'];
		$country = $_POST['country'];

		insertAddress($address, $city, $province, $postalCode, $country);	
	}
}

function insertAddress($givenAddress, $givenCity, $givenProvince, $givenPostalCode, $givenCountry){
	global $wpdb;

	$table_name = $wpdb->prefix . 'noni_addresses'; 

	$insertData = $wpdb->get_results('INSERT INTO ' . $table_name . ' (address1, city, province, postal_code, country) VALUES ("' . $givenAddress . '", "' . $givenCity . '", "' . $givenProvince . '", ' . $givenPostalCode . ' , "' . $givenCountry . '")');
}
