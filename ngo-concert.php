<?php
/*
* Plugin Name: NGO-concert
* Plugin URI: https://ngo-portal.org
* Description: Tillägg för att kunna skapa event av typen musikkonsert. Skapar en custom post type concert med musiker taxonomy m.m. Behöver bara vara aktiv på de föreningssidor som behöver den.
* Version: 1.2.1
* Author: George Bredberg
* Author URI: https://datagaraget.se
* Text Domain: ngo-concert
* Domain Path: /languages
* License   GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	echo 'This file should not be accessed directly!';
	exit; // Exit if accessed directly
}

// Load translation
add_action( 'plugins_loaded', 'ngoc_load_plugin_textdomain' );
 function ngoc_load_plugin_textdomain() {
   load_plugin_textdomain( 'ngo-concert', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// Check if mother-plugin is installed and activated. If not, show a warning message.
add_action( 'admin_init', 'ngoc_plugin_has_parent_plugin' );
function ngoc_plugin_has_parent_plugin() {
	$req_plugin = 'wp-custom-taxonomy-image/wp-custom-taxonomy-image.php';
	if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( $req_plugin ) ) {
		add_action( 'admin_notices', 'ngoc_plugin_notice' );
	}
}

function ngoc_plugin_notice(){
	?><div class="error notice is-dismissable"><p><?php _e('Tillägget Konserter behöver tillägget "WP Custom Taxonomy Image" för att visa bilder till musiker och platser.', 'ngo-concert');?></p></div><?php
}
// Done complaining about missing plugin...

// + ACTIONS AND FILTERS
// ===========================================================
register_activation_hook( __FILE__, 'ngoc_pluginprefix_install' );			// Flush rewrite rules when posttype is instanceated (plugin activated)
register_deactivation_hook( __FILE__, 'ngoc_pluginprefix_deactivation' );	// Flush rewrite rules when posttype is unregistered (plugin deactivated)
add_action( 'init', 'ngoc_custom_post' );							// Creates custom post type concerts
add_action( 'contextual_help', 'ngoc_contextual_help', 10, 3 );	// Adds contextual help to custom post type concerts
add_action( 'init', 'ngoc_taxonomies', 0 );					// Adds custom "category style" taxonomies to concerts
add_action( 'add_meta_boxes', 'ngoc_concert_meta_box' );			// Adds a box in post type concerts to add meta info
add_action( 'save_post', 'ngoc_meta_box_save' );			// We need to save this...
add_action( 'add_meta_boxes', 'ngoc_remove_metaboxes' );	// Removes comments field in edit concert.

add_filter( 'post_updated_messages', 'ngoc_updated_messages' );	// Adds custom messages to post type concerts

// Refresh WordPress permalinks when the plugin registers custom post type. This gets rid of those nasty 404 errors.
function ngoc_pluginprefix_install() {
	 // Trigger our function that registers the custom post type
	ngoc_custom_post();

	// Clear the permalinks after the post type has been registered
	flush_rewrite_rules();
}

// Refresh WordPress permalinks when the plugin unregisters custom post type. To get rid of custom post type rewrite rules.
function ngoc_pluginprefix_deactivation() {
	// Our post type will be automatically removed, so no need to unregister it

	// Clear the permalinks to remove our post type's rules
	flush_rewrite_rules();
}

// Creates the custom post type concert, meant to be used for events regarding music concerts.
// menu_position - Defines the position of the custom post type menu in the back end. Setting it to “5” places it below the “posts” menu; the higher you set it, the lower the menu will be placed.
function ngoc_custom_post() {
	$labels = array(
		'name'               => _x( 'Konserter', 'post type general name' ),
		'singular_name'      => _x( 'Konsert', 'post type singular name' ),
		'add_new'            => _x( 'Lägg till ny', 'book' ),
		'add_new_item'       => __( 'Lägg till ny konsert' ),
		'edit_item'          => __( 'Ändra konsert' ),
		'new_item'           => __( 'Ny konsert' ),
		'all_items'          => __( 'Alla konserter' ),
		'view_item'          => __( 'Visa konsert' ),
		'search_items'       => __( 'Sök konsert' ),
		'not_found'          => __( 'Hittade inga konserter' ),
		'not_found_in_trash' => __( 'Hittade inga konserter i papperskorgen' ),
		'parent_item_colon'  => '',
//	'featured_image'	=> __( 'Affish' ), //could be used if we don't want to move the image form in backoffice
		'menu_name'          => 'Konserter'
	);
	$args = array(
		'labels'        => $labels,
		'description'   => 'Skapar post typ för konserter och konsertspecifika data',
		'public'        => true,
		'menu_position' => 5,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ), // Could add 'comments' if it's going to be used.
		'menu_icon'     => 'dashicons-format-audio',
		'has_archive'   => true,
	);
	register_post_type( 'concert', $args );
}

// Change name for "Thumbnail" to "Affish", and move the widget from side to middle
add_action('do_meta_boxes', 'ngoc_change_image_box');
function ngoc_change_image_box() {
	remove_meta_box( 'postimagediv', 'concert', 'side' );
	add_meta_box('postimagediv', __('Affish'), 'post_thumbnail_meta_box', 'concert', 'normal', 'high');
}

// Adds custom messages to post type concerts
function ngoc_updated_messages( $messages ) {
	global $post, $post_ID;
	$messages['concert'] = array(
		0 => '',
		1 => sprintf( __('Konsertern är uppdaterad. <a href="%s">Visa konsert</a>'), esc_url( get_permalink($post_ID) ) ),
		2 => __('Anpassat fält är uppdaterat.'),
		3 => __('Anpassat fält är borttaget.'),
		4 => __('Konserten uppdaterad.'),
		5 => isset($_GET['revision']) ? sprintf( __('Konserten är återställd till revision från %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Konserten är publicerad. <a href="%s">Visa konsert</a>'), esc_url( get_permalink($post_ID) ) ),
		7 => __('Konserten sparad.'),
		8 => sprintf( __('Konserten är tillagd. <a target="_blank" href="%s">Förhandsvisa konsert</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Konserten är schemalagd för: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Förhandsvisa konsert</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Utkastet till konserten är uppdaterat. <a target="_blank" href="%s">Förhandsvisa konsert</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);
	return $messages;
}

// Remove Comments metaboxes from custom post type concert
//FIX: Add a check, if Comments are allowed? (See ngo-disable-comments)
function ngoc_remove_metaboxes(){
	// Remove comments fields
	remove_meta_box( 'commentstatusdiv' , 'concert' , 'normal' ); //removes comments status
	remove_meta_box( 'commentsdiv' , 'concert' , 'normal' ); //removes comments
}

// Adds contextual help to custom post type concerts
function ngoc_contextual_help( $contextual_help, $screen_id, $screen ) {
	if ( 'edit-concert' == $screen->id ) {

		$contextual_help = '<h2>' . __('Konserter', 'ngo-concert') . '</h2>
		<p>' . __('Konserter visar information om musikkonserter som är upplagda på din webbsida. Du kan se en lista över dem på denna sida, i omvänd ordning - den senast tillagda visas först.', 'ngo-concert') . '</p>
		<p>' . __('Du kan visa /ändra informationen om konserterna genom att klicka på dess namn, eller genom att göra en massändring genom att välja flera konserter och sedan använda drop listen "Välj åtgärd...".', 'ngo-concert');

	} elseif ( 'concert' == $screen->id ) {

		$contextual_help = '<h2>' . __('Ändra konsert', 'ngo-concert') . '</h2>
		<p>' . __('På denna sida kan du visa  /ändra information om musikkonserter. Vänligen se till att du fyller i formulär med lämplig information (konsertbild, musiker, övriga medverkande, lokal) och att <strong>inte</strong> lägga till detta till beskrivningen av konserten. (Informationen kommer i så fall att visas dubbelt på sidan.)', 'ngo-concert') . '</p>
		<p>' . __('Under "Övrig information" kan du lägga till information om körledare, Scenografi, Rekvisita etc.', 'ngo-concert') . '<br />' . 
		__('Snyggast blir det om du skriver "Rubrik: Information" och sedan en ny rad. T.ex. Dirigent: Viktig Person', 'ngo-concert') . '</p>
		<p>' . __('Glöm inte bort att lägga till en bild under "Utvald bild". Den kommer att visas som "Miniatyrbild" och kan till exempel vara en kopia av konsertens reklamaffish, för igenkänning.', 'ngo-concert') . '</p>';

	}
	return $contextual_help;
}

// Adds custom "category style" taxonomies to concerts called "Genre"
function ngoc_taxonomies() {
	$labels = array(
		'name'              => _x( 'Genre för konserter', 'taxonomy general name' ),
		'singular_name'     => _x( 'Genre för konsert', 'taxonomy singular name' ),
		'search_items'      => __( 'Sök genre för konserter' ),
		'all_items'         => __( 'Alla Konsertgenrer' ),
		'parent_item'       => __( 'Förälder konsertgenre' ),
		'parent_item_colon' => __( 'Förälder konsertgenre:' ),
		'edit_item'         => __( 'Ändra konsertgenre' ),
		'update_item'       => __( 'Uppdatera konsertgenre' ),
		'add_new_item'      => __( 'Lägg till konsertgenre' ),
		'new_item_name'     => __( 'Ny konsertgenre' ),
		'menu_name'         => __( 'Konsertgenre' ),
	);
	$args = array(
		'labels' => $labels,
		'hierarchical' => true,
		//'with_front' => false, turns off possibility to add items in front of slug, ie year/month/day/
	);
	register_taxonomy( 'concert_category', 'concert', $args );

		// Adds custom "tags style" taxonomies to concerts called "consert_scenes"
	$labels = array(
		'name'                       => _x( 'Platser', 'taxonomy general name' ),
		'singular_name'              => _x( 'Plats', 'taxonomy singular name' ),
		'search_items'               => __( 'Platser' ),
		'all_items'                  => __( 'Alla Platser' ),
		'parent_item'                => __( 'Förälder Plats' ),
		'parent_item_colon'          => __( 'Förälder Plats:' ),
		'edit_item'                  => __( 'Ändra Plats' ),
		'update_item'                => __( 'Uppdatera Plats' ),
		'add_new_item'               => __( 'Lägg till Plats' ),
		'new_item_name'              => __('Nytt namn på Plats' ),
		'menu_name'                  => __( 'Platser' ),
	);

	$args = array(
		'labels'                => $labels,
		'hierarchical'          => true,
		'show_ui'               => true,
		'show_admin_column'     => true,
	);

	register_taxonomy( 'concert_scenes', 'concert', $args );

// Adds custom "tags style" taxonomies to concerts called "concert_musicians"
	$labels = array(
		'name'                       => _x( 'Musiker', 'taxonomy general name' ),
		'singular_name'              => _x( 'Musiker', 'taxonomy singular name' ),
		'search_items'               => __( 'Musiker' ),
		'popular_items'              => __( 'Populära Musiker' ),
		'all_items'                  => __( 'Alla Musiker' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Ändra Musiker' ),
		'update_item'                => __( 'Uppdatera Musiker' ),
		'add_new_item'               => __( 'Lägg till Musiker' ),
		'new_item_name'              => __( 'Nytt namn på Musiker' ),
		'separate_items_with_commas' => __( 'Separera Musiker med kommatecken' ),
		'add_or_remove_items'        => __( 'Lägg till eller ta bort Musiker' ),
		'choose_from_most_used'      => __( 'Välj från mest använda Musiker' ),
		'not_found'                  => __( 'Hittade inga Musiker. (Kanske du ska skapa några?)' ),
		'menu_name'                  => __( 'Musiker' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'rewrite'								=> array( 'slug' => 'musiker' ),
	);

	register_taxonomy( 'concert_musicians', 'concert', $args );
}

// Adds a box in post type concerts to add meta info.
function ngoc_concert_meta_box() {
	add_meta_box(
		'concert_meta_box',
		__( 'Övrig information', 'ngo-concert' ),
		'ngoc_meta_content',
		'concert',
		'side',
		'high'
	);
}

// Load jQuery datepicker
function ngoc_load_datepicker_scripts() {
	// Enqueue Datepicker + jQuery UI CSS
	wp_enqueue_script( 'jquery-ui-datepicker' );
	//wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
	wp_enqueue_style( 'jquery-ui-style', plugins_url( '/css/jquery-ui.css', __FILE__ ), true);
}

function ngoc_meta_content( $post ) {
	wp_nonce_field( plugin_basename( __FILE__ ), 'ngoc_meta_content_nonce' );

	// Get the data if its already been entered
	global $post;
	$pMetaInfo = get_post_meta($post->ID, 'ngoc_meta_info', true);
	$pFirstPerf = get_post_meta( $post->ID, 'ngoc_first_performance', true  );
	$pLastPerf = get_post_meta( $post->ID, 'ngoc_last_performance', true  );
	$pNoPerf = get_post_meta($post->ID, 'ngoc_performances', true);
	$pTicket = get_post_meta($post->ID, 'ngoc_ticket_url', true);

	// Create the custom info box
	echo '<label for="ngoc_meta_info"></label>';

	// Co workers
	?><strong> <?php _e( 'Medarbetare, utöver musiker', 'ngo-concert' );?>:</strong><br/>
	<textarea name="ngoc_meta_info" id="ngoc_meta_info" cols="32" rows="6" placeholder="<?php _e( 'Ange metainfo, som roddare, producent, dirigent, körledare.&#10;&#10;Dirigent: Någon Person&#10;Text och musik: Någon Annan', 'ngo-concert' );?>"><?php echo $pMetaInfo; ?></textarea><?php

	// First performance
	?><p><strong><?php _e( 'Datum första spelningen', 'ngo-concert' );?>:</strong><br/><?php
	// Enqueue Datepicker + jQuery UI CSS
	ngoc_load_datepicker_scripts(); ?>
	<script>
	jQuery(document).ready(function(){
		jQuery('#ngoc_first_performance').datepicker({
		autoSize: true,
		dateFormat : 'yy-mm-dd',
		appendText: " (&aring;&aring;&aring;&aring;-mm-dd)",
		firstDay : 1,
		dayNamesMin: [ "S&ouml;", "M&aring;", "Ti", "On", "To", "Fr", "L&ouml;" ],
		monthNames: [ "Januari", "Februari", "Mars", "April", "Maj", "Juni", "Juli", "Augusti", "September", "Oktober", "November", "December" ],
		constrainInput: true,
		showOn: "button",
		changeMonth: true,
		changeYear: true,
		yearRange: "-1:+3",
		showWeek: true,
		weekHeader: "v."
		});
	});
	</script>
	<input type="text" name="ngoc_form_first_performance" id="ngoc_first_performance" value="<?php echo $pFirstPerf; ?>" /></p>
	<?php

	// Last performance
	?><p><strong><?php _e( 'Datum sista spelningen', 'ngo-concert' );?></strong><br/>(<?php _e('lämna tomt vid endast en spelning', 'ngo-concert');?>)<br/><?php
	// Enqueue Datepicker + jQuery UI CSS
	ngoc_load_datepicker_scripts(); ?>
	<script>
	jQuery(document).ready(function(){
		jQuery('#ngoc_last_performance').datepicker({
		autoSize: true,
		dateFormat : 'yy-mm-dd',
		appendText: " (&aring;&aring;&aring;&aring;-mm-dd)",
		firstDay : 1,
		dayNamesMin: [ "S&ouml;", "M&aring;", "Ti", "On", "To", "Fr", "L&ouml;" ],
		monthNames: [ "Januari", "Februari", "Mars", "April", "Maj", "Juni", "Juli", "Augusti", "September", "Oktober", "November", "December" ],
		constrainInput: true,
		showOn: "button",
		changeMonth: true,
		changeYear: true,
		yearRange: "-1:+3",
		showWeek: true,
		weekHeader: "v."
		});
	});
	</script>
	<input type="text" name="ngoc_form_last_performance" id="ngoc_last_performance" value="<?php echo $pLastPerf; ?>" /></p>
	<?php

	// Total no of performances
	?><p><strong><?php _e( 'Antal spelningar', 'ngo-concert' );?>:</strong><br/><?php
	echo '<input type="text" name="ngoc_performances" value="' . $pNoPerf  . '" class="widefat" /></p>';

	// url to tickets
	?><p><strong><?php _e( 'Köp biljetter här url', 'ngo-concert' )?>:</strong><br/><?php
	echo '<input type="text" name="ngoc_ticket_url" value="' . $pTicket  . '" class="widefat" /></p>';
}

// We need to save this...
// Sec: Check for submission, then nonce, then user privileges
function ngoc_meta_box_save( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( ( !empty(  $_POST['ngoc_meta_content_nonce'] ) ) && ( !wp_verify_nonce( $_POST['ngoc_meta_content_nonce'], plugin_basename( __FILE__ ) ) ) )
		return;

	if ( ( !empty($_POST['post_type']) ) && ( 'page' == $_POST['post_type'] ) ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return;
	}
	// FIX: It would be nicer with an array...
	if ( !empty($_POST['ngoc_meta_info']) ) {
		//Sanitize, but keep linebreaks
		$ngoc_meta_info = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['ngoc_meta_info'] ) ) );
		update_post_meta( $post_id, 'ngoc_meta_info', $ngoc_meta_info );
	}
	if ( !empty($_POST['ngoc_form_first_performance']) ) {
		$pFirstPerf = sanitize_text_field($_POST['ngoc_form_first_performance']);
				$split = array();
		if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $pFirstPerf, $split))
		{
			if(wp_checkdate($split[2],$split[3],$split[1],$pFirstPerf))
			{
				update_post_meta( $post_id, 'ngoc_first_performance', $pFirstPerf );
			}
		}
		//update_post_meta( $post_id, 'ngoc_first_performance', $pFirstPerf );
	} else {
		update_post_meta( $post_id, 'ngoc_first_performance', '' );
	}
	if ( !empty($_POST['ngoc_form_last_performance']) ) {
		$pLastPerf = sanitize_text_field($_POST['ngoc_form_last_performance']);
		$split = array();
		if (preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $pLastPerf, $split))
		{
			if(wp_checkdate($split[2],$split[3],$split[1],$pLastPerf))
			{
				update_post_meta( $post_id, 'ngoc_last_performance', $pLastPerf );
			}
		}
		//update_post_meta( $post_id, 'ngoc_last_performance', $pLastPerf );
	} else {
		update_post_meta( $post_id, 'ngoc_last_performance', '' );
	}
	if ( !empty($_POST['ngoc_performances']) ) {
		$pNoPerf = absint($_POST['ngoc_performances']);
		update_post_meta( $post_id, 'ngoc_performances', $pNoPerf );
	}
	if ( !empty($_POST['ngoc_ticket_url']) ) {
		$pTicket = esc_url($_POST['ngoc_ticket_url']);
		update_post_meta( $post_id, 'ngoc_ticket_url', $pTicket );
	}
}
?>
