<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$query  = "UPDATE `wp_postmeta` ";
$query .= "SET `meta_value` = ".$auto_increment." ";
$query .= "WHERE meta_value = ".$val->ID." AND meta_key LIKE '%tile_background_image%' ";
$wpdb->query( $query );
echo "<li>".$wpdb->last_query."</li>";
?>