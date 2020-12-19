<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$postparent_result = $wpdb->update( 
	"wp_posts"
	, array( 'post_parent' => $auto_increment ) 
	, array( 'post_parent' => $val->ID ) 
	, array( '%d' )
);
echo "<li>".$wpdb->last_query."</li>";
?>