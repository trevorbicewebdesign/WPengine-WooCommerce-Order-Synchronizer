<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$update_homepage = $wpdb->update( 
	"wp_options"
	, array( 'option_value' => $auto_increment ) 
	, array( 'option_name' => 'page_on_front', 'option_value'=>$val->ID ) 
	, array( '%d' )
);
echo "<li>".$wpdb->last_query."</li>";
?>