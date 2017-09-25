<?php
global $wpdb;

// Discovers last options used.


$post_columns = $this->get_table_fieldnames("wp_posts");

$this->get_wpengine_installation();
$this->get_overwrite_posts();
$this->get_overwrite_postmeta();

$settings['chunk']	 		= 500;
$settings['count_posts']		= count($this->overwrite_posts);
$settings['count_postmeta']	= count($this->overwrite_postmeta);
// Number of chunks needed
$settings['chunks_posts']	= ceil($settings['count_posts'] / $settings['chunk']);
$settings['chunks_postmeta']	= ceil($settings['count_postmeta'] / $settings['chunk']);



?>	

<div class="wrap wpe-pcc-wrap">
	<h1><?php _e( 'BM WooCommerce Order Synchronizer' ) ?></h1>
	<div class="wpe-pcc-main">
		<p>This tool is designed to help keep WooCommerce Synchronized on the WP Engine Hosting.</p>
		<p>It works by checking the WooCommerce Order POSTS on Production, and then checks to see if
		there are any conflicting POSTS on the Staging Site. If any are found, the script will then reset
		those POSTS IDs. Once that completes, the script then copies the Order POSTS to the Staging along
		with the POSTMETA and the corresponding WooCommerce Database Tables. This is all done using 
		REPLACE INTO.</p>
		
		<hr>
		<h3>There are <?php echo count($this->overwrite_posts);?> potential wp_post conflicts</h3>
		<h3>There are <?php echo count($this->overwrite_postmeta);?> potential wp_postmeta conflicts</h3>
		
		<input <?php if( count($this->overwrite_posts) <= 0 & count($this->overwrite_postmeta) <= 0 ) {echo "disabled='disabled'"; }?> class="button button-primary button-large" id="synchronize" 	name="synchronize" 	value="Remove Post and Postmeta Conflicts" 		type="submit">
		<input <?php if(count($this->overwrite_posts) > 0  || count($this->overwrite_postmeta) > 0) {echo "disabled='disabled'"; }?> class="button button-primary button-large" id="import" 		name="import" 		value="Import orders from Production to Staging" 	type="submit">
		
	</div>	
</div>



<div id="progress_bar" style="heigh:40px;width:100%;margin-top:20px;"><div class="status_level" style="background-color:#093;height:40px;width:0%;"></div></div>
<div id="message_box"></div>

<script>
jQuery(document).ready( function() {
	var i = 1;
	var posts_chunk_counter = <?php echo $settings['chunks_posts']; ?>;
	var posts_chunker_unit = (1/<?php echo $settings['chunks_posts']; ?>)*100;
	//alert(chunk_counter + " " + chunker_unit);
	var postmeta_chunk_counter = <?php echo $settings['chunks_postmeta']; ?>;
	var postmeta_chunker_unit = (1/<?php echo $settings['chunks_postmeta']; ?>)*100;
	
	jQuery("#synchronize").click( function(e) {
		e.preventDefault(); 
		ajax_chunker('posts');
	});
	jQuery("#import").click( function(e) {
		e.preventDefault(); 
		ajax_chunker('posts');
	});
	function ajax_chunker(type) {
		jQuery("#message_box").html(type + " ajax chunker started<br/>" + jQuery("#message_box").html() );
		jQuery.ajax({
			type : 		"post",
			url : 		'admin-ajax.php',
			data : {
				action: 		"remove_post_conflicts", 
				batch : 		i,
				size:		<?php echo $settings['chunk']; ?>,
				type:		type
			},
			success: function(response) {
				
				if(type=='posts'){
					percent 		= posts_chunker_unit * i;
					chunk_counter 	= posts_chunk_counter;
				}
				else if(type=='postmeta') {
					percent 		= postmeta_chunker_unit * i;
					chunk_counter 	= postmeta_chunk_counter;
				}
				
				jQuery("#progress_bar .status_level").css("width", percent+"%");
				if( i<chunk_counter ) {
					//alert(response);
					i++;
					ajax_chunker(type);
				}
				else {
					//jQuery("#message_box").html( jQuery("#message_box").html() + "Ajax " + type " Chunker Completed<br/>");
					jQuery("#message_box").html("I ran "+ i +" times<br/>" + jQuery("#message_box").html());
					jQuery("#progress_bar .status_level").css("width", "0%");
					jQuery("#progress_bar .status_level").css("background-color", "#0085ba"); 
					if(type=='posts'){
						i = 1;
						ajax_chunker('postmeta');
					}
					if(type=='postmeta'){
						jQuery("#synchronize").attr("disabled", true);
						jQuery("#import").removeAttr("disabled");
					}
				}
			}
		}); 
	}
	function import_woocommerce_orders(type) {
		jQuery("#message_box").html("Imorting WooCommerce Orders" + jQuery("#message_box").html() );
		jQuery.ajax({
			type : 		"post",
			url : 		'admin-ajax.php',
			data : {
				action: 		"import_woocommerce_orders"
			},
			success: function(response) {
				
				
			}
		}); 
	}
});
</script>