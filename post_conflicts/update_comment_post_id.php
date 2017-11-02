<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$result = $wpdb->update( 
	"wp_comments"
	, array( 'comment_post_ID' => $auto_increment ) 
	, array( 'comment_post_ID' => $val->ID ) 
	, array( '%d' )	
);		
echo "<li>".$wpdb->last_query."</li>";
?>