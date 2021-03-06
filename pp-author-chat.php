<?php
/*
Plugin Name: Author Chat Plugin
Plugin URI: http://smartfan.pl/
Description: Plugin that gives your authors an easy way to communicate through back-end UI (admin panel).
Author: Piotr Pesta
Version: 1.3.0
Author URI: http://smartfan.pl/
License: GPL12
*/

include 'pp-process.php';

add_action('admin_menu', 'pp_author_chat_setup_menu');
add_action('wp_dashboard_setup', 'pp_wp_dashboard_author_chat');
add_action('admin_enqueue_scripts', 'pp_scripts_admin_chat');
register_activation_hook(__FILE__, 'pp_author_chat_activate');
register_uninstall_hook(__FILE__, 'pp_author_chat_uninstall');

// create author_chat table
function pp_author_chat_activate() {
	global $wpdb;
	$author_chat_table = $wpdb->prefix . 'author_chat';
	$wpdb->query("CREATE TABLE IF NOT EXISTS $author_chat_table (
		id BIGINT(50) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		nickname TINYTEXT NOT NULL,
		content TEXT NOT NULL,
		date DATETIME)
		CHARACTER SET utf8 COLLATE utf8_bin
		;");
	add_option('author_chat_settings', 30);
	add_option('author_chat_settings_access_editor', 1);
	add_option('author_chat_settings_access_author', 1);
	add_option('author_chat_settings_access_contributor', 1);
	add_option('author_chat_settings_access_subscriber', 1);
}

// delete author_chat table
function pp_author_chat_uninstall() {
	global $wpdb;
	$author_chat_table = $wpdb->prefix . 'author_chat';
	$wpdb->query( "DROP TABLE IF EXISTS $author_chat_table" );
	delete_option('author_chat_settings');
	delete_option('author_chat_settings_delete');
	delete_option('author_chat_settings_access_editor');
	delete_option('author_chat_settings_access_author');
	delete_option('author_chat_settings_access_contributor');
	delete_option('author_chat_settings_access_subscriber');
}

function pp_scripts_admin_chat(){
	wp_enqueue_script('chat-script', plugins_url('chat.js', __FILE__ ), array('jquery'));
	wp_enqueue_style('author-chat-style', plugins_url('author-chat-style.css', __FILE__));
}

function pp_author_chat_setup_menu(){
	include 'pp-options.php';
	add_dashboard_page('Author Chat', 'Author Chat', 'read', 'author-chat', 'pp_author_chat');
	add_menu_page('Author Chat Options', 'Author Chat Options', 'administrator', 'acset', 'author_chat_settings', 'dashicons-carrot');
	add_action( 'admin_init', 'register_author_chat_settings' );
}

function pp_wp_dashboard_author_chat(){
	wp_add_dashboard_widget('author-chat-widget', 'Author Chat', 'pp_author_chat');
}

function register_author_chat_settings() {
	register_setting( 'author_chat_settings_group', 'author_chat_settings');
	register_setting( 'author_chat_settings_group', 'author_chat_settings_delete');
	register_setting( 'author_chat_settings_group', 'author_chat_settings_access_editor');
	register_setting( 'author_chat_settings_group', 'author_chat_settings_access_author');
	register_setting( 'author_chat_settings_group', 'author_chat_settings_access_contributor');
	register_setting( 'author_chat_settings_group', 'author_chat_settings_access_subscriber');
}

function pp_author_chat(){
	global $current_user;
	get_currentuserinfo();
	if((get_option('author_chat_settings_access_subscriber') == '1' && $current_user->user_level == '0') || (get_option('author_chat_settings_access_contributor') == '1' && $current_user->user_level == '1') || (get_option('author_chat_settings_access_author') == '1' && $current_user->user_level == '2') || (get_option('author_chat_settings_access_editor') == '1' && $current_user->user_level == '3') || (get_option('author_chat_settings_access_editor') == '1' && $current_user->user_level == '4') || (get_option('author_chat_settings_access_editor') == '1' && $current_user->user_level == '5') || (get_option('author_chat_settings_access_editor') == '1' && $current_user->user_level == '6') || (get_option('author_chat_settings_access_editor') == '1' && $current_user->user_level == '7') || $current_user->user_level == '8' || $current_user->user_level == '9' || $current_user->user_level == '10'){
?>
	
	<script type="text/javascript">
		var chat =  new Chat();
		jQuery(window).load(function(){
			chat.initiate();
			setInterval(function(){
				chat.getState();
			}, 2000);
		});

	</script>

    <div id="page-wrap">
    
        <h2>Author Chat</h2>
        
        <p id="name-area"></p>
        
        <div id="chat-wrap"><div id="chat-area"></div></div>
        
        <form id="send-message-area">
            <textarea id="sendie" maxlength = "1000" placeholder="Your message..."></textarea>
		</form>

    </div>
	
    <script type="text/javascript">

        // shows current user name as name
        var name = "<?php echo "$current_user->user_login"; ?>";

    	// display name on page
    	jQuery("#name-area").html("You are: <span>" + name + "</span>");
    	
    	// kick off chat
        var chat =  new Chat();
    	jQuery(function() {
    		
    		 // watch textarea for key presses
			jQuery("#sendie").keydown(function(event) {  
             
                 var key = event.which;

                 //all keys including return.  
                 if (key >= 33) {

                     var maxLength = jQuery(this).attr("maxlength");
                     var length = this.value.length;
                     
                     // don't allow new content if length is maxed out
                     if (length >= maxLength) {  
                         event.preventDefault();  
                     }
                  }
			});
    		 // watch textarea for release of key press
    		 jQuery('#sendie').keyup(function(e) {	
    		 					 
    			  if (e.keyCode == 13) { 
    			  
                    var text = jQuery(this).val();
    				var maxLength = jQuery(this).attr("maxlength");  
                    var length = text.length; 
                     
                    // send 
                    if (length <= maxLength + 1) { 
                     
    			        chat.send(text, name);	
    			        jQuery(this).val("");
    			        
                    }else {
                    
    					jQuery(this).val(text.substring(0, maxLength));
    					
    				}
    			  }
             });
    	});
    </script>
	
	<?php
	}
	pp_author_chat_clean_up_chat_history();
	
	if (get_option('author_chat_settings_delete') == 1){
		pp_author_chat_clean_up_database();
	}
}

function pp_author_chat_clean_up_chat_history(){
	global $wpdb;
	$daystoclear = get_option('author_chat_settings');
	$author_chat_table = $wpdb->prefix . 'author_chat';
	$wpdb->query("DELETE FROM $author_chat_table WHERE date <= NOW() - INTERVAL $daystoclear DAY");
}

function pp_author_chat_clean_up_database(){
	global $wpdb;
	$author_chat_table = $wpdb->prefix . 'author_chat';
	$wpdb->query("TRUNCATE TABLE $author_chat_table");
	$update_options = get_option('author_chat_settings_delete');
	$update_options = '';
	update_option('author_chat_settings_delete', $update_options);
}

?>