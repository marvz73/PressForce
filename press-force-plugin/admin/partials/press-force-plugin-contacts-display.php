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

<div class="wrap">
	<h1><?php echo $title ?></h1>
	
<?php 
	$contact_list_table->search_box('search', 'search_id');
	echo '<form id="wpse-list-table-form" method="post">';
	// $contact_list_table->prepare_items();
	$contact_list_table->display(); 
	echo '</form>';
?>
	
</div>