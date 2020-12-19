<?php
/**
 * Plugin Name: WooCommerce Order Synchronizer
 * Version:     0.1.0
 * Plugin URI:  
 * Author:      Trevor Bice
 * Author URI:  http://burningman.org/
 * Text Domain: bm_woocommerce_ordersync
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Description: This tool is designed to help keep WooCommerce Orders Synchronized between Producion and Staging on the WP Engine Hosting.
 ****/

if ( ! defined( 'ABSPATH' ) ) exit;

class bm_woocommerce_ordersync {
	
	private static $instance = false;
	private $wpengine;
	private $page;
 	private $overwrite_posts;
	
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
			self::$instance->init();
		}
		return self::$instance;
	}

	function init() {
		add_action( 'admin_menu', array( self::instance(), 'create_menu' ) );
			
		add_action('wp_ajax_remove_post_conflicts', 		array( self::instance(), 'remove_post_conflicts' ) );  
		add_action('wp_ajax_import_woocommerce_orders', 	array( self::instance(), 'import_woocommerce_orders' ) );  
		add_action('wp_ajax_checkOrderSynchronization', 	array( self::instance(), 'checkOrderSynchronization' ) );  
		
	}
	function checkOrderSynchronization() {
		global $wpdb;
		$this->get_wpengine_installation();
		$query  = "SELECT * FROM wp_".$this->wpengine.".wp_posts ";
		$query .= "WHERE post_type = 'shop_order' ";	
		$wpdb->query( $query );
		$results = $wpdb->get_results( $query );
		$production_string = json_encode($results);
		$production_string = md5($production_string);
		
		$query  = "SELECT * FROM snapshot_".$this->wpengine.".wp_posts ";
		$query .= "WHERE post_type = 'shop_order' ";	
		$wpdb->query( $query );
		$results = $wpdb->get_results( $query );
		$staging_string = json_encode($results);
		$staging_string = md5($staging_string);
		if($production_string==$staging_string) {
			
			return true;
		}	
		return false;
	}
	function import_woocommerce_orders() {
		global $wpdb;
		
		$this->get_wpengine_installation();
		
		$post_columns = $this->get_table_fieldnames('wp_posts');
				
		$query  = "REPLACE INTO snapshot_".$this->wpengine.".wp_posts ";
		$query .= $post_columns;
		$query .= "  SELECT * FROM wp_".$this->wpengine.".wp_posts  ";
		$query .= "WHERE post_type = 'shop_order' ";
		$wpdb->query( $query );
		echo $query."<br/>";
		
				
		$query  = "REPLACE INTO snapshot_".$this->wpengine.".wp_postmeta ";
		$query .= "(meta_id,post_id,meta_key,meta_value) ";
		$query .= "  (SELECT DISTINCT	 pm.* FROM wp_".$this->wpengine.".wp_postmeta  AS pm ";
		$query .= " LEFT JOIN wp_".$this->wpengine.".wp_posts AS p ON p.ID = pm.post_id ";
		$query .= "WHERE p.post_type = 'shop_order' ) ";
		$wpdb->query( $query );
		echo $query."<br/>";

		$query  = "SELECT pm.* FROM wp_".$this->wpengine.".wp_postmeta AS pm  ";
		$query .= "LEFT JOIN wp_".$this->wpengine.".wp_posts AS p ON p.ID = pm.post_id   ";
		$query .= "WHERE p.post_type = 'shop_order' ";
		echo $query."<br/>";
		$postmeta = $wpdb->get_results( $query );
		
		foreach($postmeta as $index=>$val) {

			$query  = "SELECT * FROM ".$dbname['staging'].".wp_postmeta ";
				$query .= "WHERE 	post_id 	= '".$val->post_id."' ";
				$query .= "AND 	meta_key 	= '".$val->meta_key."' ";
				echo $query."<br/>";
				$ifexist = $wpdb->get_results( $query );
				// If we don't find an entry we can copy this record over
				
				if( count($ifexist)<=0) {
					$query  = "REPLACE INTO ".$dbname['staging'].".wp_postmeta ";
					$query .= $get_postmeta_columns." ";
					$query .= "(SELECT post_id,meta_key,meta_value FROM ".$dbname['production'].".wp_postmeta ";
					$query .= "WHERE 	post_id	= '".$val->post_id."'  ";
					$query .= "AND 	meta_key 	= '".$val->meta_key."' )";
					echo $query."<br/>";
					$wpdb->query( $query );
				}
				
				
		}
		
		$query  = "REPLACE INTO snapshot_".$this->wpengine.".wp_woocommerce_order_items ";
		$query .= $this->get_table_fieldnames('wp_woocommerce_order_items')." ";
		$query .= "SELECT * FROM wp_".$this->wpengine.".wp_woocommerce_order_items   ";
		$wpdb->query( $query );
		echo $query."<br/>";
		
		$query  = "REPLACE INTO snapshot_".$this->wpengine."wp_woocommerce_order_itemmeta ";
		$query .= $this->get_table_fieldnames('wp_woocommerce_order_itemmeta')." ";
		$query .= "SELECT * FROM wp_".$this->wpengine.".wp_woocommerce_order_itemmeta   ";
		$wpdb->query( $query );
		echo $query."<br/>";
		
		wp_die();
	}
	function remove_post_conflicts(){
			global $wpdb;
			$this->get_wpengine_installation();
			
			$chunk['batch']	= $_REQUEST["batch"];
			$chunk['size']		= $_REQUEST['size'];
			$chunk['type']		= $_REQUEST['type'];
			
			$item_index	= $chunk['size'] * ($chunk['batch']-1);
			
			if($chunk['type'] == "posts") {
				$this->get_overwrite_posts();	
				$chunk_array = array_chunk($this->overwrite_posts, $chunk['size']);
				// Batch 1 needs to get index 0
				$this_chunk = $chunk_array[0];
				foreach($this_chunk as $key=>$val) {
					$auto_increment = $this->get_autoincrement('wp_posts');
					echo "Auto Increment is ".$auto_increment."<br/>";
					
					$posts_result = $wpdb->update( 
						"wp_posts"
						, array( 'ID' => $auto_increment ) 
						, array( 'ID' => $val->ID ) 
						, array( '%d' )
					);					
					echo $wpdb->last_query."<br/>";
					
					if($posts_result) {
						echo "Re-setting the Staging wp_posts <b>post_id</b> from ".$val->ID." to ".$auto_increment."<br/>"	;
						echo $val->ID."\n\r";
						
						$fixit = scandir( 	plugin_dir_path(__FILE__)."post_conflicts");
						
						echo "<ul>";
						foreach($fixit as $index=>$file) {
							if( preg_match("#[.]php$#", $file)){
								require( "post_conflicts/".$file);
								
							}
						}
						echo "</ul>";
						
						$query  = "ALTER TABLE wp_".$this->wpengine.".wp_posts AUTO_INCREMENT = ".($auto_increment+1)." ";
						//echo $query;
						$wpdb->query( $query );
						echo "Autoincrement amount increased from ".$auto_increment." to ".($auto_increment+1)."<br/>";
						//echo "<hr/>";
					}
						
					echo "<hr/>";
				
				}
			
			}
			else if($chunk['type'] == "postmeta") {				
				$this->get_overwrite_postmeta();	
				$chunk_array = array_chunk($this->overwrite_postmeta, $chunk['size']);
				// Turns out we don't actually need the chunk index since each time this loads the overwrite_postmeta is reset
				// It just works out in the end, like magic
				$this_chunk = $chunk_array[0];
				
				// print_r($chunk_array);
				// die();
				
				foreach($this_chunk as $key=>$val) {
				
					//if($i>10) {break;}
					$auto_increment = $this->get_autoincrement('wp_postmeta');
					
					$fixit = scandir( 	plugin_dir_path(__FILE__)."postmeta_conflicts");
					echo "<ul>";
					foreach($fixit as $index=>$file) {
						if( preg_match("#[.]php$#", $file)){
							require( "postmeta_conflicts/".$file);
							
						}
					}
					echo "</ul>";
						
					$query  = "ALTER TABLE wp_".$this->wpengine.".wp_postmeta AUTO_INCREMENT = ".($auto_increment+1)." ";
					echo "Autoincrement amount increased from ".$auto_increment." to ".($auto_increment+1)."<br/>";
					//echo "<hr/>";
					$wpdb->query( $query );
				}
			}
			
			
			wp_die();
		}
	function create_menu() {
		// Create Tools sub-menu.
		$this->page = add_submenu_page( 'tools.php', __( 'WooCommerce Order Sync', 'bm-woocommerce-ordersync' ), __( 'WooCommerce Order Sync', 'bm-woocommerce-ordersync' ), 'manage_options', 'bm-woocommerce-ordersync', array( $this, 'settings_page' ) );
	}
	function get_wpengine_installation() {
		global $wpdb;
		
		if(preg_match("#^snapshot_(.*)$#",$wpdb->dbname, $tmp)) {
			// This must be getting loaded from the 'Staging' context
			$wpengine = $tmp[1];
			// Check that both the 'Production' and 'Staging' databases exist
			$query  = "SHOW DATABASES LIKE 'wp_".$wpengine."' ";
			if( count($wpdb->get_results( $query ))>0) {
				$this->wpengine = $wpengine;
				return $wpengine;
			}
		}
		else if(preg_match("#^wp_(.*)^#",$wpdb->dbname, $tmp)) {
			// This must be getting loaded from the 'Production' context
			$wpengine = $tmp[1];
			// Check that both the 'Production' and 'Staging' databases exist
			$query  = "SHOW DATABASES LIKE 'snapshot_".$wpengine."' ";
			if( count($wpdb->get_results( $query ))>0) {
				$this->wpengine = $wpengine;
				return $wpengine;
			}
		}
		return false;
		
	}
	function get_table_fieldnames($tablename) {
		global $wpdb;
		$query  = "
		SELECT DISTINCT column_name
		FROM information_schema.columns
		WHERE table_name='".$tablename."'; 
		";
		//echo $query."<hr/>";
		$columns = $wpdb->get_results( $query );
		foreach($columns as $index=>$val) {
			$field_names.= ",".$val->column_name;
		}
		
		
		
		$field_names = "(".substr($field_names,1).")";
		
		return $field_names;
	}
    public function getPostConflicts($post_type='page'){
        global $wpdb;
        $query  = "";
		$query .= "SELECT ID FROM wp_".$this->wpengine.".wp_posts ";
		$query .= "WHERE ID IN ( ";
			$query .= "SELECT ID FROM snapshot_".$this->wpengine.".wp_posts ";
			$query .= "WHERE ID IN( ";
				$query .= "SELECT ID FROM wp_".$this->wpengine.".wp_posts ";
				$query .= "WHERE post_type = '$post_type' ";
			$query .= ") ";
			$query .= "AND post_type != '$post_type' ";
			$query .= "ORDER BY ID ASC ";
		$query .= ") ";
		$query .= "ORDER BY ID ASC ";
        echo $query;
        die();
		//$query .= "AND wp_".$this->wpengine.".post_type != 'shop_order'  ";
		$overwrite_posts = $wpdb->get_results( $query );
        return $overwrite_posts;
    }
	function get_overwrite_posts() {
		global $wpdb;
		// This batch needs new IDs
		// This SQL statement might be a tad confusing, so here is a quick explanation
		// We want to get a list of all the posts in the 'Production' site whose Post IDs
		// have maches in the 'Staging' site.
		// Then get a list of all posts
		
		
		$query  = "";
		$query .= "SELECT * FROM wp_".$this->wpengine.".wp_posts ";
		$query .= "WHERE ID IN ( ";
			$query .= "SELECT ID FROM snapshot_".$this->wpengine.".wp_posts ";
			$query .= "WHERE ID IN( ";
				$query .= "SELECT ID FROM wp_".$this->wpengine.".wp_posts ";
				$query .= "WHERE post_type = 'shop_order' ";
			$query .= ") ";
			$query .= "AND post_type != 'shop_order' ";
			$query .= "ORDER BY ID ASC ";
		$query .= ") ";
		$query .= "ORDER BY ID ASC ";
		//$query .= "AND wp_".$this->wpengine.".post_type != 'shop_order'  ";
		$overwrite_posts = $wpdb->get_results( $query );
		//echo $query."<br/>";
		//die();
		
		$this->overwrite_posts = $overwrite_posts;
		
		//echo $this->wpengine."<br/>";
		
		//die();
		
		return $overwrite_posts;
	}
	function get_overwrite_postmeta() {
		global $wpdb;
		// This batch needs new IDs
		
		$query  = "
		SELECT * FROM wp_".$this->wpengine.".wp_postmeta 
		WHERE meta_id IN (
			SELECT pm.meta_id FROM snapshot_".$this->wpengine.".wp_postmeta AS pm
			LEFT JOIN snapshot_".$this->wpengine.".wp_posts AS p ON p.ID = pm.post_id
			";
			$query .= "WHERE meta_id IN( ";
				$query .= "SELECT pm.meta_id FROM wp_".$this->wpengine.".wp_posts AS p ";
				$query .= "LEFT JOIN wp_".$this->wpengine.".wp_postmeta AS pm ON p.ID = pm.post_id ";
				$query .= "WHERE p.post_type = 'shop_order' ";
			$query .= ") ";
			$query .= "AND p.post_type != 'shop_order' ";
		$query .= "
		)
		
		";
		// echo $query."<hr/>";
		//die();
		//die();
		$overwrite_postmeta = $wpdb->get_results( $query );
		$this->overwrite_postmeta = $overwrite_postmeta;
		
		
		return $overwrite_postmeta;
	}
	function get_autoincrement($tablename, $environment='production') {
		global $wpdb;
		if($environment == 'production'){
			$table_prefix = "wp_";	
		}
		else if($environment == 'staging'){
			$table_prefix = "snapshot_";	
		}
		
		$query  = "SELECT `AUTO_INCREMENT` FROM  information_schema.TABLES ";
		$query .= "WHERE TABLE_SCHEMA = '".$table_prefix.$this->wpengine."' ";
		$query .= "AND   TABLE_NAME   = '".$tablename."' ";
		// echo $query."<br/>";
		$auto_increment = $wpdb->get_results( $query );
		return $auto_increment[0]->AUTO_INCREMENT;
	}
	function set_wpengine_databases() {
		
	}
	function settings_page() {
		global $wpdb;
		ob_start();
		include("tmpl/admin.php");
		$output = ob_get_contents(); // Put ob content in a variable
		$ob_end_clean();
		echo $output;
	}
}

$bm_woocommerce_ordersync = new bm_woocommerce_ordersync;
$bm_woocommerce_ordersync->init();



?>