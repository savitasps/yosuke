<?php 


/*
Plugin Name: WP Viewed User Widget
Description: This plugin helps to track the user viewed history ont he website and also able to extract the history in the csv file.
Version: 1.0
Author: Softprodigy
*/

class trackerWidget extends WP_Widget {
	// Constructor  
	function __construct() {
		parent::__construct( 'viewed_posts', // Base ID
			'Posts Viewed ', // Name
			array(
				'description' => __( 'This plugin helps to track the user viewed history ont he website and also able to extract the history in the csv file.', 'posts-viewed-recently' )
			) // Args
		);
	}

	// Widget form creation
	function form( $init ) {
		
		$widgetID = str_replace( 'recent_viewed_posts-', '', $this->id );
		// Check values
		$title              = isset( $init['title'] ) ? esc_attr( $init['title'] ) : '';
		$numberofposts      = isset( $init['numberofposts'] ) ? absint( $init['numberofposts'] ) : 5;
		$show_date          = isset( $init['show_date'] ) ? (bool) $init['show_date'] : false;
		$showthumbnail      = isset( $init['showthumbnail'] ) ? (bool) $instance['showthumbnail'] : false;
		$width              = isset( $init['width'] ) ? absint( $init['width'] ) : '';
		$height             = isset( $init['height'] ) ? absint( $init['height'] ) : '';
		$alternateImg       = isset( $init['alternateImg'] ) ? esc_url( $init['alternateImg'] ) : '';
		$selected_posttypes = isset( $init['selected_posttypes'] ) && is_array( $init['selected_posttypes'] ) ? $init['selected_posttypes'] : array();
		
		$custom_posts  = get_post_types( array(
			'public'   => true,
			'_builtin' => false
		), 'names', 'and' );
		$default_posts = array(
			'post' => 'post',
			'page' => 'page'
		);
		$post_types = array_merge( $custom_posts, $default_posts );
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'posts-viewed-recently' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
        </p>
        <p class="typeholder">
            <label><?php _e( 'Select Categories:', 'posts-viewed-recently' ); ?></label><br/>
			<p>Hold down the Ctrl (windows)/Command (Mac) button to select multiple options.</p>
			<select  id="FT_checkbox_<?php echo $post_type; ?>" name="<?php echo $this->get_field_name( 'selected_posttypes' ) . '[]'; ?>" multiple size="5" style="width:100%"> 
			<?php 
			
			foreach ( $post_types as $post_type ) {
				$obj         = get_post_type_object( $post_type );
				$postName    = $obj->name;
				$is_selected = '';
				if ( in_array( $post_type, $selected_posttypes ) ) {
					$is_selected = "selected='selected'";
				}
				?>
                <!-- <input type="checkbox" class="checkbox" id="FT_checkbox_<?php echo $post_type; ?>" name="<?php echo $this->get_field_name( 'selected_posttypes' ) . '[]'; ?>" value="<?php echo $post_type; ?>" <?php checked( $is_selected ); ?>/>
                <label><?php echo $postName; ?></label><br/> -->
				 <option  <?php echo $is_selected; ?>  value= "<?php echo $postName;?>" ><?php echo $postName; ?> </option> 
				<?php
			}
			?>
			 </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'numberofposts' ); ?>"><?php _e( 'Number of posts to show:', 'posts-viewed-recently' );	?></label>
            <input id="<?php echo $this->get_field_id( 'numberofposts' ); ?>" name="<?php echo $this->get_field_name( 'numberofposts' ); ?>" type="text" size="3" value="<?php echo $numberofposts; ?>"/>
        </p>
        <p>
            <input class="checkbox showthumbnail" id="<?php echo $this->get_field_id( 'showthumbnail' ); ?>" name="<?php echo $this->get_field_name( 'showthumbnail' ); ?>" type="checkbox" <?php checked( $showthumbnail ); ?> />
            <label for="<?php echo $this->get_field_id( 'showthumbnail' ); ?>"><?php _e( 'Show Image ?', 'posts-viewed-recently' ); ?></label>
        </p>
        <div class="thumbnailAttr">
            <p>
                <label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width: ', 'posts-viewed-recently' ); ?></label>
                <input size="3" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>"/> px
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height: ', 'posts-viewed-recently' ); ?></label>
                <input size="3" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>"/> px
            </p>
            <!-- <p>
                <label for="<?php //echo $this->get_field_id( 'alternateImg' ); ?>"><?php _e( 'Alternate image URL:', 'posts-viewed-recently' ); ?></label>
                <input class="widefat" id="<?php //echo $this->get_field_id( 'alternateImg' ); ?>" name="<?php// echo $this->get_field_name( 'alternateImg' ); ?>" type="text" value="<?php// echo $alternateImg; ?>"/>
            </p> -->
        </div>
        <p>
        	<input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>"/>
        	<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display Viewed date?', 'posts-viewed-recently' ); ?></label>
        </p>
	<?php if ( $widgetID != "__i__" ) { ?>
		<p style="font-size: 11px; opacity:0.6">
			<span class="shortcodeTtitle">Shortcode:</span>
			<span class="shortcode">[userviewed widget_id="<?php echo $widgetID; ?>"]</span>
            <span class="shortcodeTtitle">Shortcode for user history page:</span>
			<span class="shortcode">[userviewed]</span>
		</p>
        
	<?php
		} // End widget id check
	}

	// Widget update
	function update($new,$old) {
		$old['title']              = isset( $new['title'] ) ? $new['title'] : '';
		$old['selected_posttypes'] = isset( $new['selected_posttypes'] ) && is_array( $new['selected_posttypes'] ) ? $new['selected_posttypes'] : array();
		$old['numberofposts']      = isset( $new['numberofposts'] ) ? absint( $new['numberofposts'] ) : '';
		$old['showthumbnail']      = isset( $new['showthumbnail'] ) ? (bool) $new['showthumbnail'] : false;
		$old['width']              = isset( $new['width'] ) ? absint( $new['width'] ) : '';
		$old['height']             = isset( $new['height'] ) ? absint( $new['height'] ) : '';
		$old['alternateImg']       = isset( $new['alternateImg'] ) ? $new['alternateImg'] : '';
		$old['show_date']          = isset( $new['show_date'] ) ? (bool) $new['show_date'] : false;
		return $old;
	}

	// Widget display
	function widget( $args, $instance1 ) {
		
		global $wpdb;
		// Check if user has visted earlier ornot and along with is user logged in or not
		$user = wp_get_current_user();
			if (is_user_logged_in()){
				$user_id = get_current_user_id();
				$tables_name = $wpdb->prefix . 'history_tracker';
				
				$widgetID           = $args['widget_id'];
				$widgetID           = str_replace( 'viewed_posts-', '', $widgetID );
				$widgetOptions      = get_option( $this->option_name );
				$instance1          = $widgetOptions[ $widgetID ];
				$title              = ( ! empty( $instance1['title'] ) ) ? $instance1['title'] : __( 'Recently Visited Posts' );
				$title              = apply_filters( 'widget_title', $title, $instance1, $this->id_base );
				$number             = ( ! empty( $instance1['numberofposts'] ) ) ? absint( $instance1['numberofposts'] ) : 5;
				$showthumbnail      = isset( $instance1['showthumbnail'] ) ? $instance1['showthumbnail'] : false;
				$width_image        = empty( $instance1['width'] ) ? '50' : absint( $instance1['width'] );
				$height_image       = empty( $instance1['height'] ) ? '50' : absint( $instance1['height'] );
				$alternateImg       = ! empty( $instance1['alternateImg'] ) ? esc_url( $instance1['alternateImg'] ) : '';
				$show_date          = isset( $instance1['show_date'] ) ? $instance1['show_date'] : false;
				$selected_posttypes = isset( $instance1["selected_posttypes"] ) && is_array( $instance1["selected_posttypes"] ) ? $instance1["selected_posttypes"] : array();
				extract( $args, EXTR_SKIP );
				$getUserTrackData = $wpdb->get_results("Select * from $tables_name where user_id = $user_id AND post_type IN ("."'".implode("','", $selected_posttypes)."'".") order by created_at Desc limit $number");
				//					if ( count( $getUserTrackData ) > 0 ){
					$count = 0;
					// Loop through posts in the cookie array
					foreach ( $getUserTrackData as $key=>$value) {
						if ( $count >= $number || absint( $value->content_id) == 0 ) {
							
							break;
						}
						$ft_post = get_post( absint($value->content_id)); // Get the post
						// Condition to display a post
						
						//print_r($selected_posttypes);
						if ( isset( $ft_post ) && in_array( $ft_post->post_type, $selected_posttypes ) ) {
							$count ++;
							if ( $count == 1 ) {    
								echo $before_widget;
								echo $before_title . $title . $after_title;
								echo '<ul class="recentviewed_post 123456">';
							}
							$permalink = esc_url( get_permalink( $ft_post->ID ) );
							$excerpt = $ft_post->post_content;
							$excerpt = strip_tags($excerpt);
							$excerpt = substr($excerpt, 0, 40);
							$excerpt = substr($excerpt, 0, strripos($excerpt, " "));
							$excerpt = '<p>'.$excerpt.'... <a href="'.$permalink.'"></a></p>';


							?>
							<li>
								<?php if ( $showthumbnail ): ?>
									<div class="recentviewed_left" style="width:<?php echo $width_image; ?>px;height:<?php echo $height_image; ?>px;">
										<?php if ( has_post_thumbnail( $ft_post->ID ) ) { ?>
											<a href="<?php echo $permalink; ?>">
												<?php echo get_the_post_thumbnail( $ft_post->ID, array($width_image, $height_image ) ); ?>
											</a>
											<p><?php echo $excerpt?></p>
											<?php 
										} elseif ( $alternateImg != '' ) {
											?>
											<a href="<?php echo $permalink; ?>">
												<img src="<?php echo $alternateImg; ?>" width="<?php echo $width_image; ?>" height="<?php echo $height_image; ?>" class="wp-post-image"/>
											</a>
											<p><?php echo $excerpt ;?></p>
											<?php
										}
										?>
									</div>
								<?php
								endif;
								?>
								<div class="recentviewed_right">
									<a href="<?php echo $permalink; ?>">
										<?php
										echo apply_filters( 'the_title', $ft_post->post_title, $ft_post->ID );
										?>
									</a>
									<?php
									if ( $show_date ):
										?>
										<br/><span class="post-date"><small><?php echo date( get_option( 'date_format' ), strtotime( $value->created_at ) ); ?></small></span>
									<?php
									endif;
									?>
								</div>
							</li>
							<?php
						} //End condition to display a post
					} // End foreach
					if ( $count > 0 ) {
						echo '</ul>' . $after_widget;
					}
//				}
			}
			
	}
		
}		
		
	// Register widget js file
	add_action( 'admin_enqueue_scripts', 'adminscriptcallback' );
	function adminscriptcallback() {
		wp_register_script( 'adminscript', plugins_url( 'js/tracker.js', __FILE__ ) );
		wp_enqueue_script( 'adminscript' );
	}

	// Register the widget
	add_action( 'widgets_init', 'widgetcallback' );
	function widgetcallback() {
		register_widget( 'trackerWidget' );
	}

	/* Register the style sheet */
	add_action( 'wp_enqueue_scripts', 'frontstylecallback' );
	function frontstylecallback(){
		global $post;
		wp_register_style( 'stylesheet', plugins_url( 'css/trackerStyle.css', __FILE__ ) );
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'userhistorycode') ) {
			wp_register_style( 'styleshee1t', "https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css");
			wp_register_script( 'stylesh28eet', "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js");
			wp_register_script( 'stylesh2eet', "https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js");
			wp_enqueue_style( 'styleshee1t' );
			wp_enqueue_style( 'stylesh28eet' );
			wp_enqueue_style( 'stylesh2eet' );
        }
		wp_enqueue_style( 'stylesheet' );
	}
	
      add_action( 'admin_enqueue_scripts', 'adminstylefilesCallback' );
      function adminstylefilesCallback() {
        if (isset($_SERVER['QUERY_STRING'])  && $_SERVER['QUERY_STRING'] == 'page=wp-user-viewed-post-history-track'){
            wp_enqueue_style( 'admin_css_foo', plugins_url( 'css/export.css', __FILE__ ), false, '1.0.0' );
        }
      } 


		function elmcallback(){ ?>
			<script>
					function handleSelect(elm){
						window.location = elm;
					}
			</script>
	<?php } 
	add_action('wp_footer', 'elmcallback'); 


	add_action( 'template_redirect', 'visitedUserCallback' );
	function visitedUserCallback() {
		if ((is_single() || is_page()) && is_user_logged_in()){
			$user = wp_get_current_user();  
			   // Stuff here for allowed roles
			   	global $wpdb;
				$id = get_the_id();
				$post_type = get_post_type($id);
				$user_id = get_current_user_id();
				$te = get_option('widget_viewed_posts');
                foreach($te as $keyst=>$tvalue){
                    $selected_posttypes = $te[$keyst]['selected_posttypes'];
                    if(in_array($post_type,$selected_posttypes) && $keyst != '_multiwidget'){
                        $tables_name = $wpdb->prefix . 'history_tracker';
                        $checkPostOfSameUser = $wpdb->get_results("select * from $tables_name where content_id = $id and user_id = $user_id ");
                        if(!empty($checkPostOfSameUser)){
                            $deleteRow = $wpdb->query("Delete  from $tables_name where content_id = $id and user_id = $user_id");
                            $insertNewRow = $wpdb->query("INSERT INTO $tables_name (user_id,content_id,post_type) VALUES ($user_id,$id,'$post_type')");
                        }else{
                            $insertNewRow = $wpdb->query("INSERT INTO $tables_name (user_id,content_id,post_type) VALUES ($user_id,$id,'$post_type')");
                        }
                    }
                }
                	
			
			
		}
	}
	
	add_action ('wp_loaded', 'deleteCallback');
	function deleteCallback() {
		if (isset($_POST['delete'])){
			global $wpdb;
			$page_url =   get_permalink(get_the_ID());
			$user_id = get_current_user_id();
			$tables_name = $wpdb->prefix . 'history_tracker';
			$deleteallRow = $wpdb->query("Delete  from $tables_name where user_id = $user_id");
			wp_redirect( $page_url );
		}
	}
	
	add_action('wp_footer','cookieconsent');
	function cookieconsent(){
		if ($textc = get_option('cookieconsent')){
			 $text=  $textc['text'];
			 $url =  $textc['url']; ?>
			<p id="cookie-notice"><?php echo $text; ?><a href="<?php echo $textc['url']; ?>">Learn More</a><br><button onclick="acceptCookie();">Accept</button></p>
			<script>var date = new Date();var days =  365;date.setTime(+ date + (days * 86400000));function acceptCookie(){document.cookie="cookieaccepted=1; expires="+date.toGMTString()+"; path=/",document.getElementById("cookie-notice").style.visibility="hidden"}document.cookie.indexOf("cookieaccepted")<0&&(document.getElementById("cookie-notice").style.visibility="visible");</script>
		<?php
		}
	}
	
	
	 register_activation_hook( __FILE__, 'create_plugin_database_table' );
	 function create_plugin_database_table(){
			/*Actions to be performed when plugin is activated.*/
		global $wpdb;
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( ! current_user_can( 'activate_plugins' ) ) {
			// Deactivate the plugin.
			deactivate_plugins( plugin_basename( __FILE__ ) );
			$error_message = __( 'You do not have proper authorization to activate a plugin!', 'woo-solo-api' );
			die( esc_html( $error_message ) );
		}
		$tables_name = array("history"=>$wpdb->prefix . 'history_tracker');
		foreach ($tables_name as $key => $table_name) {
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				//table not in database. Create new table
				$charset_collate = $wpdb->get_charset_collate();
				$sql = "CREATE TABLE $table_name (
					id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					user_id BIGINT(11) NOT NULL,
					content_id BIGINT(11) NOT NULL,
					post_type VARCHAR(255) NOT NULL,
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY id (id)
				) $charset_collate;";  

				if(!function_exists('dbDelta')) {
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				}
				dbDelta( $sql );
			}
		}
	}	
	
	add_action('admin_menu', 'create_tools_submenu');
	function create_tools_submenu() {
    add_management_page( 'Export History', 'Export History', 'manage_options', 'wp-user-viewed-post-history-track', 'generate_page_content' );
    function generate_page_content() {
		require_once( plugin_dir_path( __FILE__ ) . 'export_cookie.php' );
	}
	
}

	
require_once( plugin_dir_path( __FILE__ ) . 'shortcode.php' );
?>
