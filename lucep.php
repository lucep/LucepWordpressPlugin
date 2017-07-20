<?php 
/*
Plugin Name: Lucep Call Now Button
Plugin URI: https://lucep.com/signup/?utm_medium=wordpress&utm_campaign=lucep-plugin&utm_source=wordpress-plugin-uri
Description: Lucep is an instant response call back tool that engages with your website visitors and encourages them to request a call back from your business. Lucep will inform your sales team instantly and provide you with data and analytics on your prospect. 
Version: 1.0
Author: Lucep
Author URI: https://lucep.com/signup/?utm_medium=wordpress&utm_campaign=lucep-plugin&utm_source=wordpress-plugin-author
License: GPLv3
Text Domain: lucep
*/

// create settings menu for account name 
add_action('admin_menu', 'lucep_menu');

function lucep_menu() 
{
  add_menu_page('Lucep Settings', 'Lucep Settings', 'administrator', __FILE__, 'lucep_plugin_settings_page' , plugins_url('/images/lucep_icon.png', __FILE__) );
   //call register settings function
   add_action( 'admin_init', 'register_lucep_plugin_settings_page' );
}

function register_lucep_plugin_settings_page() 
{
	//register our settings
	register_setting( 'lucep_plugin_settings-group', 'lucep_accountname' );
}

function lucep_plugin_settings_page() 
{
    // We check if it is having an action on the page
    if (!empty($_POST['submit'])) {
        // User wants to log-in
        if (
            !empty($_POST['lucep_accountname']) 
            && 
            !empty($_POST['lucep_username']) 
            && 
            !empty($_POST['lucep_password']) 
            ) {
            // We sanitize the input data
            $lucep_accountname = sanitize_key($_POST['lucep_accountname']);
            if ($lucep_accountname != $_POST['lucep_accountname']) {
                $lucep_accountname = "";
                ?><div class="error settings-error"><p>Ooops! For team name, only alphanumeric characters are allowed.</p></div><?php
            }
            $lucep_username = sanitize_key($_POST['lucep_username']);
            if ($lucep_username != $_POST['lucep_username']) {
                $lucep_username = "";
                ?><div class="error settings-error"><p>Uh-oh!! For user name, only alphanumeric characters are allowed.</p></div><?php
            }
        }
        if (
            !empty($lucep_accountname) 
            && 
            !empty($lucep_username) 
            && 
            !empty($_POST['lucep_password']) 
            ) {
            $url = "https://gorilla.lucep.com/api/";
            // The first request is to initialize the handshake
            $args = [
                "body" => [
                    "action" => "login",
                    "account_key" => $lucep_accountname,
                    "login_login" => "Login",
                    "login_username" => $lucep_username,
                    ],
                ];
            $serverFullResponse = wp_remote_post($url,$args);
            if (!is_wp_error($serverFullResponse)) {
                if (wp_remote_retrieve_response_code($serverFullResponse) == 200) {
                    $response = json_decode(wp_remote_retrieve_body($serverFullResponse));
                    if (!empty($response)) {
                        if ($response->status == "login") {
                            // The second request is to create the handshake
                            $args["body"]["login_challenge"] = $response->challenge;
                            $args["body"]["login_response"] = hash_hmac(
                                "sha1",
                                $response->challenge,
                                hash_hmac(
                                    "sha1",
                                    $response->salt.$lucep_username,
                                    $_POST['lucep_password'] // This is used for hashing, hence no sanitation is needed
                                    )
                                );
                            $serverFullResponse = wp_remote_post($url,$args);
                            if (!is_wp_error($serverFullResponse)) {
                                if (wp_remote_retrieve_response_code($serverFullResponse) == 200) {
                                    $response = json_decode(wp_remote_retrieve_body($serverFullResponse));
                                    if (!empty($response)) {
                                        if ($response->status == "ok") {
                                            // Because the user can log-in in his/her account, we store the team name in WP's DB
                                            update_option(
                                                'lucep_accountname',
                                                $lucep_accountname
                                                );
                                            ?><div class="updated"><p>Updates are saved.</p></div><?php
                                        } else {
                                            ?><div class="error settings-error"><p>Your credentials don't seem to be valid. Please check them and try again.</p></div><?php
                                        }
                                    } else {
                                        ?><div class="error settings-error"><p>Whoa! A server somewhere said something that I could not decode... please wait a minute or two and try again!</p></div><?php
                                    }
                                } else {
                                    ?><div class="error settings-error"><p>Argh! The servers seem very busy right now - perhaps try again in a moment.</p></div><?php
                                }
                            } else {
                                ?><div class="error settings-error"><p>Uh-oh! Wordpress said it encountered an error, perhaps try again?</p></div><?php
                            }
                        } else {
                            ?><div class="error settings-error"><p>Hmmm, a server said something unexpected, perhaps try again?</p></div><?php
                        }
                    } else {
                        ?><div class="error settings-error"><p>Hmmm, the server said something unexpected, perhaps try again?</p></div><?php
                    }
                } else {
                    ?><div class="error settings-error"><p>Argh! The server complained there was an error somewhere - this was not your fault though. Perhaps try again?</p></div><?php
                }
            } else {
                ?><div class="error settings-error"><p>Ooops! There was an error in Wordpress, please try again.</p></div><?php
            }
        } else if (!empty($_POST["lucep_disconnect"])) {
            delete_option('lucep_accountname');
        } else {
            ?><div class="error settings-error"><p>Settings were not saved.</p></div><?php
        }
    }
    // Print the credentials form if the user didn't log-in yet, otherwise the disconnect form
?>
<link rel="stylesheet" href="<?php echo plugins_url('/css/admin_style.css', __FILE__); ?>">
<div class="wrap lucep_admin_wrap">
	<div class="main-head">
		<h2>Lucep Settings</h2>
	</div>	
	<div class="main-content">
		<form method="post" action="">
			<?php settings_fields( 'lucep_plugin_settings-group' ); ?>
			<?php do_settings_sections( 'lucep_plugin_settings-group' ); ?>
			<h3>Link up with your Lucep account</h3><hr />
			<p>Use your Lucep account details below to connect your account with your site. If you do not have a Lucep account just go to <a href="https://lucep.com/signup/?utm_medium=wordpress&utm_campaign=lucep-plugin&utm_source=wordpress-plugin-directory" target="_blank">www.lucep.com/signup</a></p>
			<?php
			if (empty(get_option('lucep_accountname'))) { 
                // This is the credentials form
			?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Team Name</th>
					<td><input type="text" required  name="lucep_accountname" value="" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">User Name</th>
					<td><input type="text" required  name="lucep_username" value="" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Password</th>
					<td><input type="password" required  name="lucep_password" value="" /><br /><small><a href="https://lucep.com/set-password" target="_blank">Forgot password?</a></small></td>
				</tr>
                <tr valign="top">
					<td colspan="2"><br/><br/>The Lucep widget will display on your site after your account is linked up.</td>
				</tr>
				<tr valign="top">
					<td colspan="2"><?php submit_button("Link Up"); ?></td>
				</tr>
			 </table>
			 <?php 
			 } else {
                // This is the disconnect form
                ?>Connected using the team '<?php echo esc_attr( get_option('lucep_accountname') ); ?>'. 
                <input type="hidden" name="lucep_disconnect" value="true" />
                <?php 
                submit_button("Disconnect",["red"]); 
			 } ?>
		</form>
	</div>
</div>
<?php } 

// add JavaScript code to the footer of every Wordpress page 
function add_lucep_script_footer()
{ 
$lucep_accountname = esc_attr( get_option('lucep_accountname') );
?>
<script type='text/javascript'>
    window.$gorilla || ( (window._gorilla={
    no_ui: false,
    mobile: false,
    cdn: "https://8d69a4badb4c0e3cd487-efd95a2de0a33cb5b6fcd4ec94d1740c.ssl.cf2.rackcdn.com/",
    domain: "<?php echo $lucep_accountname; ?>",id: 1,lang: "eng"}) 
    & ( function ( l, u, c, e, p ){ var g = document.createElement(e); g.src = l; g.onload=u; document.getElementsByTagName(c)[p].appendChild(g);})
    ("https://8d69a4badb4c0e3cd487-efd95a2de0a33cb5b6fcd4ec94d1740c.ssl.cf2.rackcdn.com/js/L.SalesGorilla.stable.latest.min.js", null, "head", "script", 0) )
</script>
<noscript><a href="https://lucep.com/client-enquiry/<?php echo $lucep_accountname; ?>">Click to get a call from our team!</a> <a href="https://lucep.com">Click to call</a> by Lucep</noscript>
<?php } 

// check user enter account name or not 

$accoutname = get_option('lucep_accountname');
if(!empty($accoutname)) 
{
  add_action('wp_footer', 'add_lucep_script_footer');
}
else if (empty($_POST['lucep_accountname']))
{
  add_action( 'admin_notices', 'my_lucep_notice' );
}

function my_lucep_notice() 
{
?>
  <div class="error">
      <p><?php _e( 'Please log-in with your account in Lucep Call Now Button Settings (left side menu under setting menu), it is required for Lucep plugin to work properly!', 'my_plugin_textdomain' ); ?></p>
  </div>
<?php
}

