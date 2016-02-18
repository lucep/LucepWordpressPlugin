<?php 
/*
Plugin Name: lucep 
Plugin URI: http://lucep.com/api-docs
Description: Plugin provide lucep script, just enter your account name in Lucep Settings (left side menu under setting menu).
Version: 1.0
Author: lucep
Author URI: http://lucep.com/api-docs
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
?>
<link rel="stylesheet" href="<?php echo plugins_url('/css/admin_style.css', __FILE__); ?>">
<div class="wrap lucep_admin_wrap">
	<div class="main-head">
		<h2>Lucep Settings</h2>
	</div>	
	<div class="main-content">
		<form method="post" action="options.php">
			<?php settings_fields( 'lucep_plugin_settings-group' ); ?>
			<?php do_settings_sections( 'lucep_plugin_settings-group' ); ?>
			<h3>Script Account Details</h3><hR>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Account Name</th>
					<td><input type="text" required  name="lucep_accountname" value="<?php echo esc_attr( get_option('lucep_accountname') ); ?>" /></td>
				</tr>
				 <tr valign="top">
					<th scope="row">&nbsp;</th>
					<td><?php submit_button(); ?></td>
				</tr>
			 </table>
		</form>
	</div>
</div>
<?php } 

// add JavaScript code to the footer of every Wordpress page 
function add_lucep_script_footer()
{ ?>
<script type='text/javascript'>
		window.$gorilla || ( (window._gorilla={
		no_ui: false,
		mobile: false,
		cdn: "https://8d69a4badb4c0e3cd487-efd95a2de0a33cb5b6fcd4ec94d1740c.ssl.cf2.rackcdn.com/",
		domain: "<?php echo esc_attr( get_option('lucep_accountname') ); ?>",id: 1,lang: "eng"}) 
		& ( function ( l, u, c, e, p ){ var g = document.createElement(e); g.src = l; g.onload=u; document.getElementsByTagName(c)[p].appendChild(g);})
		("https://8d69a4badb4c0e3cd487-efd95a2de0a33cb5b6fcd4ec94d1740c.ssl.cf2.rackcdn.com/js/L.SalesGorilla.stable.latest.min.js", null, "head", "script", 0) )
</script>
<?php } 

// check user enter account name or not 

$accoutname = get_option('lucep_accountname');
if(!empty($accoutname)) 
{
  add_action('wp_footer', 'add_lucep_script_footer');
}
else
{
  add_action( 'admin_notices', 'my_lucep_notice' );
}

function my_lucep_notice() 
{
?>
  <div class="updated">
      <p><?php _e( 'Please enter your account name in Lucep Settings (left side menu under setting menu),it is required for Lucep plugin to work properly!', 'my_plugin_textdomain' ); ?></p>
  </div>
<?php
}
?>
