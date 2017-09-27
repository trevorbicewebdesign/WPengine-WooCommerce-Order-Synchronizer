<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$postmeta_result = $wpdb->update( 
	"wp_commentmeta"
	, array( 'meta_id' => $auto_increment ) 
	, array( 'meta_id' => $val->meta_id ) 	
	, array( '%d' )	
);		
echo $wpdb->last_query."<br/>";

?>