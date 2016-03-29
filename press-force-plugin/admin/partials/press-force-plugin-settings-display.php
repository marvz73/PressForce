<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://alphasys.com.au
 * @since      1.0.0
 *
 * @package    Press_Force_Plugin
 * @subpackage Press_Force_Plugin/admin/partials
 */



?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php
	if(isset($_GET['error']) && $_GET['error'] == 1)
	{
		echo '<div class="error"><p>Invalid authorization code!</p></div>';
	}
	if(isset($_GET['success']) && $_GET['success'] == 1)
	{
?>
		<div id="message" class="updated notice is-dismissible">
			<p>Token successfully save!</p>
		</div>
<?php
	}
?>


<div class="wrap">

	<h1><?php echo $title ?></h1>
	<div class="card" style="width: 100%;max-width: auto">
	<h3>Salesforce Settings</h3>



<form method="post" action="<?php echo get_site_url() ?>/wp-admin/admin-post.php ">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="client_id">Consumer Key</label></th>
				<td>
					<input ="" type="text" name="client_id" id="client_id" class="regular-text" value="<?php echo get_option('salesforce_settings')['client_id'] ?>" >
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="client_secret">Consumer Secret</label></th>
				<td>
					<input ="" type="text" id="client_secret" name="client_secret" class="regular-text" value="<?php echo get_option('salesforce_settings')['client_secret'] ?>" >
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="redirect_uri">Redirect URI</label></th>
				<td>
					<input ="" type="url" id="redirect_uri" name="redirect_uri" class="regular-text" value="<?php echo get_option('salesforce_settings')['redirect_uri'] ?>" >
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="login_uri">Login URL</label></th>
				<td>	
					<input ="" type="url" id="login_uri" name="login_uri" class="regular-text" value="<?php echo get_option('salesforce_settings')['login_uri'] ?>" >
					 
					
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="url">Salesforce URL</label></th>
				<td>
					<input ="" type="url" id="url" name="url" class="regular-text" value="<?php echo get_option('salesforce_settings')['url'] ?>" >
					 
					
				</td>
			</tr>
		</tbody>
	</table>

	<p class="submit">
		<button class="button  button-primary">Save Settings</button>
		<span style="float: right;margin-top: 5px;">Updated on <?php echo human_time_diff( get_option('salesforce_settings')['timestamp'], current_time('timestamp') ) . ' ago' ?></span>
	</p>
<input type="hidden" name="action" value="settings">
</form>
</div>





	
</div>