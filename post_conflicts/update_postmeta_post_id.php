<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$postmeta_result = $wpdb->update( 
	"wp_postmeta"
	, array( 'post_id' => $auto_increment ) 
	, array( 'post_id' => $val->ID ) 
	, array( '%d' )
);		
echo "<li>".$wpdb->last_query."</li>";
?>