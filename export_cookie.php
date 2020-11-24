<?php
if(isset($_POST['exportUser'])){
  if(empty($_POST['columns'])){
	echo("You didn't select any feilds.");
  }else{
	 	global $wpdb;
	    $wpdb->hide_errors();
        @set_time_limit(0);
        if (function_exists('apache_setenv'))
            @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ob_end_clean();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=Customer-Export-' . date('Y_m_d_H_i_s', current_time('timestamp')) . ".csv");
        header('Pragma: no-cache');
        header('Expires: 0');

        $fp = fopen('php://output', 'w');
        
        // Variable to hold the CSV data we're exporting
        $row = array();
        
		$csv_columns = array(
			'ID' => 'ID',
			'user_nicename' => 'user_nicename',
			'user_email' => 'user_email',
			'user_url' => 'user_url',
			'display_name' => 'display_name',
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'billing_address_1' => 'billing_address_1',
			'billing_address_2' => 'billing_address_2',
			'billing_city' => 'billing_city',
			'billing_postcode' => 'billing_postcode',
			'billing_phone' => 'billing_phone',
			'roles' => 'roles'
		);

        // Export header rows
        foreach ($csv_columns as $column => $value) {
            if (in_array($column, $_POST['columns']))
                $row[] = $column;
        }

		// send the column headers
        fwrite($fp, implode(',',$row) . "\n");
        unset($row);
		
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', -1);
        
        $args = array(
            'fields' => 'ID', // exclude standard wp_users fields from get_users query -> get Only ID##
            'role__in' => array(), //An array of role names. Matched users must have at least one of these roles. Default empty array.
            'number' => 99999, // number of users to retrieve
            //~ 'offset' => $export_offset // offset to skip from list
        );
        
        $users = get_users($args);
        // Loop users
        foreach ($users as $user) {
			$userd = get_user_by('id', $user);
			$customer_data = array();
			$special = array('/',',','&','*');
			foreach ($csv_columns as $key) {
				$customer_data[$key] = !empty($userd->{$key}) ? maybe_serialize($userd->{$key}) : '';
				if($key == 'billing_address_1'){
					$customer_data[$key] = str_replace($special , " " , get_user_meta( $user, 'billing_address_1', true ));
				}
				if($key == 'billing_address_2'){
					$customer_data[$key] = str_replace($special , " " ,get_user_meta( $user, 'billing_address_2', true ));
				}
				if($key == 'billing_city'){
					$customer_data[$key] = get_user_meta( $user, 'billing_city', true );
				}
				if($key == 'billing_postcode'){
					$customer_data[$key] = get_user_meta( $user, 'billing_postcode', true );
				}
				if($key == 'billing_phone'){
					$customer_data[$key] = get_user_meta( $user, 'billing_phone', true );
				}
				
			}
			$user_roles = (!empty($userd->roles)) ? $userd->roles : array();
			$customer_data['roles'] = implode(',', $user_roles);

			foreach ($customer_data as $key => $value) {
				if (in_array($key,$_POST['columns'])) {
					// need to modify code
				} else {
					unset($customer_data[$key]);
				}
			}
			$row = $customer_data;
            fwrite($fp, implode(",", $row) . "\n");
            unset($row);
        }
        fclose($fp);
        exit;
  }
} 
	$columns = array(
			'ID' => 'ID',
			'user_nicename' => 'user_nicename',
			'user_email' => 'user_email',
			'user_url' => 'user_url',
			'display_name' => 'display_name',
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'billing_address_1' => 'billing_address_1',
			'billing_address_2' => 'billing_address_2',
			'billing_city' => 'billing_city',
			'billing_postcode' => 'billing_postcode',
			'billing_phone' => 'billing_phone',
			'roles' => 'roles'
	);
?>
<div class="container">
  <h2>Select the feilds as header columns in csv</h2>
	<form action="" method="post">
		<?php foreach($columns as $key=>$value){ ?>
		<div class="custom-control custom-checkbox">
				<label class="custom-control-label"    for="defaultUnchecked"><?php echo $key; ?></label>
				<input type="checkbox" class="custom-control-input" name="columns[]" value="<?php echo $value; ?>" id="defaultUnchecked" >
		</div>
		<?php }?>
		<input type="submit" name="exportUser" value="Export User">	
	</form>	
</div>	
<?php
if(isset($_POST['exportHistory'])){
	global $wpdb;
	$wpdb->hide_errors();
	@set_time_limit(0);
	if (function_exists('apache_setenv'))
		@apache_setenv('no-gzip', 1);
	@ini_set('zlib.output_compression', 0);
	@ob_end_clean();

	header('Content-Type: text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename=Customer-Export-' . date('Y_m_d_H_i_s', current_time('timestamp')) . ".csv");
	header('Pragma: no-cache');
	header('Expires: 0');
	$fp = fopen('php://output', 'w');
	$row = array();
	$row = $csv_columns = array('User Name','User Email','Post Type','Content Name','Content Url');
	// send the column headers
	fwrite($fp, implode(',',$row) . "\n");
	unset($row);
	
	ini_set('max_execution_time', -1);
	ini_set('memory_limit', -1);
	$tables_name = $wpdb->prefix . 'history_tracker';
	$getUserTrackData = $wpdb->get_results("Select * from $tables_name order by user_id");
	// Loop users
	$customer_data = array();
	foreach ($getUserTrackData as $value) {
		$userd = get_user_by('id', $value->user_id);
	
		$customer_data['User Name'] = $userd->display_name;
		$customer_data['User Email'] = $userd->user_email;
		$customer_data['Post Type'] = $value->post_type;
		$ft_post = get_post( absint($value->content_id));
		$customer_data['Content Name'] = apply_filters( 'the_title', $ft_post->post_title, $ft_post->ID );
		$customer_data['Content Url'] = esc_url( get_permalink( $ft_post->ID ) );
		$row = $customer_data;
		fwrite($fp, implode(",", $row) . "\n");
		unset($row);
	}
	fclose($fp);
	exit;
} 
?>
<div class="container">
  <h2>Export user Tracking Hisrory</h2>
	<form action="" method="post">
		<input type="submit" name="exportHistory" value="Export User">	
	</form>	
</div>	

<?php
if(isset($_POST['cookieconsent'])){
	if (FALSE === get_option('cookieconsent') && FALSE === update_option('cookieconsent',FALSE)){
		 $option = array('text'=>$_POST['textc'],'url'=>$_POST['urlc']);
		 add_option('cookieconsent',$option);
	}else{
		$option = array('text'=>$_POST['textc'],'url'=>$_POST['urlc']);
		update_option( 'cookieconsent', $option);
	}	 
} 
?>
<div class="container">
  <h2>Add content and url for consent cookie</h2>
	<form action="" method="post" class="last-post">
		  <div class="form-group">
			<label class="sr-only" for="textc">Text of Cookie</label>
			<input type="text" name ="textc" class="form-control" value="<?php if ($textc = get_option('cookieconsent')){ echo $textc['text']; } ?>" id="textc">
		  </div>
		  <div class="form-group">
			<label class="sr-only" for="urlc">Url for Terms</label>
			<input type="text" class="form-control" value="<?php if ($textc = get_option('cookieconsent')){ echo $textc['url']; } ?>" name ="urlc" id="urlc">
		  </div>
		<input type="submit" name="cookieconsent" value="Submit">	
	</form>	
</div>	



<?php
    if(isset($_POST['userfeilds'])){
          if(empty($_POST['feilds'])){
            echo("You didn't select any feilds.");
          }else{
                if (FALSE === get_option('userfeilds') && FALSE === update_option('userfeilds',FALSE)){
                     add_option('userfeilds',$_POST['feilds']);
                }else{
                     update_option('userfeilds',$_POST['feilds']);
                } 
                   
          }
    }
    $userfeilds = array(
        'post_img' => 'post_img',
        'post_type' => 'post_type',
        'post_title' => 'post_title',
        'custom_post' => 'custom_post',
        'post_price' => 'post_price',
        'post_url' => 'post_url',
        'post_excerpt' => 'post_excerpt',
	);
?>
<div class="container">
  <h2>Add Feild to show data accordingly at user page</h2>
	<form action="" method="post">
		<?php foreach($userfeilds as $key=>$value){ ?>
		<div class="custom-control custom-checkbox">
				<label class="custom-control-label"  for="defaultUnchecked"><?php echo $key; ?></label>
				<input type="checkbox" class="custom-control-input" name="feilds[]" value="<?php echo $value; ?>" id="defaultUnchecked"  <?php if (in_array($value,get_option('userfeilds'))) { ?> checked <?php }?>>
		</div>
		<?php }?>
		<input type="submit" name="userfeilds" value="Submit">	
	</form>	
</div>	
  


