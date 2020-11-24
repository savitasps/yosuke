<?php
function shortcodeuserviewed( $atts ) {
	// Configure defaults and extract the attributes into variables

	$args = array(
		'widget_id'    => $atts['widget_id'],
		'by_shortcode' => 'shortcode_',
	);

	ob_start();
	the_widget( 'trackerWidget', '', $args );
	$output = ob_get_clean();

	return $output;
}

add_shortcode( 'userviewed', 'shortcodeuserviewed' );

function shortcodecallback(){
	global $wpdb;
	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    if (FALSE === get_option('userfeilds') && FALSE === update_option('userfeilds',FALSE)){
        $content .= "<p>Please add feilds first at admin to show the data. </p>";            
        return $content;
    }
    
	$user = wp_get_current_user();
	$page_url =   get_permalink(get_the_ID());
		if (is_user_logged_in()){
			$user_id = get_current_user_id();
			$tables_name = $wpdb->prefix . 'history_tracker';
			$total_pages_sql = $wpdb->get_results("SELECT COUNT(*) as count FROM $tables_name where user_id = $user_id");
						
			if(isset($_GET['i'])){
				$content_id = $_GET['i'];
				$deleteRow = $wpdb->query("Delete  from $tables_name where content_id = $content_id and user_id = $user_id");
			}
			
			if(isset($_GET['t']) && $_GET['t'] != 'all'){
				$post_type = $_GET['t'];
				$getAllUserTrackData = $wpdb->get_results("Select * from $tables_name where user_id = $user_id and post_type = '$post_type' order by created_at Desc");
			}else if(isset($_GET['t']) && $_GET['t'] == 'all'){
				$getAllUserTrackData = $wpdb->get_results("Select * from $tables_name where user_id = $user_id order by created_at Desc");
			}else{
				$getAllUserTrackData = $wpdb->get_results("Select * from $tables_name where user_id = $user_id order by created_at Desc");	
			}
			
			$te = get_option('widget_viewed_posts');
			$selected_posttypes =  $te[2]['selected_posttypes'];
				if ( count($getAllUserTrackData) > 0 ){
					//pagination functionality
					$pageno = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
					$no_of_records_per_page = 10;
					$offset = ($pageno-1) * $no_of_records_per_page; 
					$total_rows =  count($getAllUserTrackData);
					$total_pages = ceil($total_rows / $no_of_records_per_page);
					
					if(isset($_GET['t']) && $_GET['t'] != 'all'){
						$post_type = $_GET['t'];
						$getAllUserTrackData = $wpdb->get_results("Select * from $tables_name where user_id = $user_id and post_type = '$post_type' order by created_at Desc LIMIT $offset, $no_of_records_per_page");
					}else if(isset($_GET['t']) && $_GET['t'] == 'all'){
						$getAllUserTrackData = $wpdb->get_results("Select * from $tables_name where user_id = $user_id order by created_at Desc LIMIT $offset, $no_of_records_per_page");
					}else{
						$getAllUserTrackData = $wpdb->get_results("Select * from $tables_name where user_id = $user_id order by created_at Desc LIMIT $offset, $no_of_records_per_page");	
					}
					
					// if current page is greater than total pages...
					if ($pageno > $total_pages) {
					   // set current page to last page
					   $pageno = $total_pages;
					} // end if
					// if current page is less than first page...
					if ($pageno < 1) {
					   // set current page to first page
					   $pageno = 1;
					} // end if
					
					
					$content = "";
					$content .= "<div class='form-group'>
								  <label for='sel1'>Select list:</label>
								  <select class='form-control' onchange='handleSelect(this.value)' id='sel1'>";
						$content .= "<option value=''>Select Post type </option>";		  
						$content .= "<option value='".$page_url."?t=all'><a href= '#'>All</a></option>";			  
									foreach($selected_posttypes as $post){
						$content .= "<option value='".$page_url."?t=".$post."'>$post</option>";
									}		
					$content .= "</select>
								</div>";
					$content .=  "<form method='POST' action=''>
									<input type='checkbox' class='chk' />
									<button class='btn' type='submit' name='delete' >Delete All</button>
								  </form>";			
					$content .="<div class='container-one'>
									<div class='table-responsive'>          
										<table class='table'>
											<thead>
												<tr>
													<th>#</th>";
                                                    $getfeilds = get_option('userfeilds');
                                                    if(in_array('post_img',$getfeilds)){
                                                        $content .="<th>Post IMG</th>";
                                                    }                        
                                                    if(in_array('post_type',$getfeilds)){
                                                        $content .="<th>Post Type</th>";
                                                    }                        
                                                    if(in_array('post_title',$getfeilds)){
                                                        $content .="<th>Post Title</th>";
                                                    }                        
                                                    if(in_array('custom_post',$getfeilds)){
                                                        $content .="<th>Custom Post</th>";
                                                    }                        
                                                    if(in_array('post_price',$getfeilds)){
                                                        $content .="<th>Post Price</th>";
                                                    }                        
                                                    if(in_array('post_url',$getfeilds)){
                                                        $content .="<th>Post URL</th>";
                                                    }      
                                                    if(in_array('post_excerpt',$getfeilds)){
                                                        $content .="<th>Post Excerpt</th>";
                                                    }       
                                                        
								    $content .="	<th>Viewed At</th>
                                                    <th>Delete</th>
												</tr>
											</thead>
											<tbody>";

					$x = 1;
                    $custom_post_types  = get_post_types( array(
                        'public'   => true,
                        '_builtin' => false
                    ), 'names', 'and' );
					foreach ( $getAllUserTrackData as $key=>$value) {
						$ft_post = get_post( absint($value->content_id));
						$permalink = esc_url( get_permalink( $ft_post->ID ) );
							$excerpt = $ft_post->post_content;
							$excerpt = strip_tags($excerpt);
							$excerpt = substr($excerpt, 0, 100);
							$excerpt = substr($excerpt, 0, strripos($excerpt, " "));
							$excerpt = '<p>'.$excerpt.'... <a href="'.$permalink.'"></a></p>';
							if(isset($_GET['t'])){
								$pieces = parse_url($actual_link);
								$query = [];
								if ($pieces['query']) {
									parse_str($pieces['query'], $query);
									unset($query['i']);
									$pieces['query'] = http_build_query($query);
								}
								$actual_link=  http_build_url($pieces);
								$delurl = '&i=';
							}else{
								if(isset($_GET['i'])){
									$pieces = parse_url($actual_link);
									$query = [];
									if (isset($pieces['query'])) {
										parse_str($pieces['query'], $query);
										unset($query['i']);
										$pieces['query'] = http_build_query($query);
									}
									$actual_link=  http_build_url($pieces);
									$delurl = '?i=';
								}else{
									$delurl = '?i=';
								}
								
							}	
						$content .="<tr>
										<td>$x</td>";
                                        if(in_array('post_img',$getfeilds)){
                                            $content .="<td>". get_the_post_thumbnail( $ft_post->ID, array(50,50))."</td>";
                                        }                        
                                        if(in_array('post_type',$getfeilds)){
                                            $content .="<td>".$value->post_type."</td>";
                                        }                        
                                        if(in_array('post_title',$getfeilds)){
                                            $content .="<td>". apply_filters( 'the_title', $ft_post->post_title, $value->content_id )."</td>";
                                        }                        
                                        if(in_array('custom_post',$getfeilds)){
                                            if(in_array($value->post_type,$custom_post_types)){
                                                $content .="<td>Yes</td>";
                                            }else{
                                                $content .="<td>NO</td>";
                                            }
                                            
                                        }                        
                                        if(in_array('post_price',$getfeilds)){
                                            if($value->post_type == 'product'){
                                                global $woocommerce;
                                                $currency = get_woocommerce_currency_symbol();
                                                $product = wc_get_product( $value->content_id );
                                                $content .="<td>".$currency.$product->get_regular_price()."</td>";
                                            }else{
                                                $content .="<td>N/A</td>";
                                            }
                                            
                                        }                        
                                        if(in_array('post_url',$getfeilds)){                                   
                                            $content .="<td><a href = '".get_permalink($value->content_id)."'><span>Click</span></a></td>";
                                        }      
                                        if(in_array('post_excerpt',$getfeilds)){
                                            $content .="<td>".$excerpt."</td>";
                                        }    
								$content .="<td>". date( get_option( 'date_format' ), strtotime( $value->created_at ))."</td>";
                                            
				        $content .="	<td><a href = '".$actual_link.$delurl.$value->content_id."'><span>Delete</span></a></td>
									</tr>";
						$x++;			
					}
					$content .="    		</tbody>
								      </table>
								   </div>
							   </div>";
					$content .= "<div class='pagination'>";
					
					$range = 3;
					if ($pageno > 1) {
					   // show << link to go back to page 1
					   if(isset($_GET['t'])){
						   $t = $_GET['t'];
						   $content .= " <a href='".$page_url."?paged=1&t=".$t."'><<</a> ";
					   }else{
						  $content .= " <a href='".$page_url."?paged=1'><<</a> ";
					   } 
					   // get previous page num
					   $prevpage = $pageno - 1;
					   // show < link to go back to 1 page
					   if(isset($_GET['t'])){
						   $t = $_GET['t'];
						   $content .= " <a href='".$page_url."?paged=$prevpage&t=".$t."'><</a> ";
					   }else{
						  $content .= " <a href='".$page_url."?paged=$prevpage'><</a> ";
					   }
					   
					}
					  for ($x = ($pageno - $range); $x < (($pageno + $range) + 1); $x++) {
						   // if it's a valid page number...
						   if (($x > 0) && ($x <= $total_pages)) {
							  // if we're on current page...
							  if ($x == $pageno) {
								 // 'highlight' it but don't make a link
								 $content .= "<div class='active'> <b>$x</b></div> ";
							  // if not current page...
							  } else {
								 // make it a link
								  if(isset($_GET['t'])){
									  $t = $_GET['t'];
									  $content .= " <a href='".$page_url."?paged=$x&t=".$t."'>$x</a> ";
								   }else{
									  $content .= " <a href='".$page_url."?paged=$x'>$x</a> ";
								   }
								 
							  } // end else
						   } // end if 
						} // end for
						if ($pageno != $total_pages) {
						   // get next page
						   $nextpage = $pageno + 1;
							// echo forward link for next page 
						   if(isset($_GET['t'])){
							   $t = $_GET['t'];
							  $content .= " <a href='".$page_url."?paged=$nextpage&t=".$t."'><i class='fa fa-angle-right'></i>>></a> ";
						   }else{
							 $content .= " <a href='".$page_url."?paged=$nextpage'><i class='fa fa-angle-right'></i>>></a> ";
						   }
						   
						   // echo forward link for lastpage
						   if(isset($_GET['t'])){
							   $t = $_GET['t'];
							  $content .= " <a href='".$page_url."?paged=$total_pages&t=".$t."'><i class='fa fa-angle-double-right'></i>></a> ";
						   }else{
							 $content .= " <a href='".$page_url."?paged=$total_pages'><i class='fa fa-angle-double-right'></i>></a> ";
						   }
						   
						} // end if
					$content .= "</div>";							   
					return 	$content;	   
				}else{
                    $content = "";
					$content .= "<div class='form-group'>
								  <label for='sel1'>Select list:</label>
								  <select class='form-control' onchange='handleSelect(this.value)' id='sel1'>";
						$content .= "<option value=''>Select Post type </option>";		  
						$content .= "<option value='".$page_url."?t=all'><a href= '#'>All</a></option>";			  
									foreach($selected_posttypes as $post){
						$content .= "<option value='".$page_url."?t=".$post."'>$post</option>";
									}		
					$content .= "</select>";
					$content .= "<p>No Record Found</p>";
                    
                    return $content;
                }		
		}	
			
}
add_shortcode( 'userhistorycode', 'shortcodecallback' );

 if(!function_exists('http_build_url')){
            // Define constants
            define('HTTP_URL_REPLACE',          0x0001);    // Replace every part of the first URL when there's one of the second URL
            define('HTTP_URL_JOIN_PATH',        0x0002);    // Join relative paths
            define('HTTP_URL_JOIN_QUERY',       0x0004);    // Join query strings
            define('HTTP_URL_STRIP_USER',       0x0008);    // Strip any user authentication information
            define('HTTP_URL_STRIP_PASS',       0x0010);    // Strip any password authentication information
            define('HTTP_URL_STRIP_PORT',       0x0020);    // Strip explicit port numbers
            define('HTTP_URL_STRIP_PATH',       0x0040);    // Strip complete path
            define('HTTP_URL_STRIP_QUERY',      0x0080);    // Strip query string
            define('HTTP_URL_STRIP_FRAGMENT',   0x0100);    // Strip any fragments (#identifier)

            // Combination constants
            define('HTTP_URL_STRIP_AUTH',       HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS);
            define('HTTP_URL_STRIP_ALL',        HTTP_URL_STRIP_AUTH | HTTP_URL_STRIP_PORT | HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT);

            /**
             * HTTP Build URL
             * Combines arrays in the form of parse_url() into a new string based on specific options
             * @name http_build_url
             * @param string|array $url     The existing URL as a string or result from parse_url
             * @param string|array $parts   Same as $url
             * @param int $flags            URLs are combined based on these
             * @param array &$new_url       If set, filled with array version of new url
             * @return string
             */
            function http_build_url(/*string|array*/ $url, /*string|array*/ $parts = array(), /*int*/ $flags = HTTP_URL_REPLACE, /*array*/ &$new_url = false)
            {
                // If the $url is a string
                if(is_string($url))
                {
                    $url = parse_url($url);
                }

                // If the $parts is a string
                if(is_string($parts))
                {
                    $parts  = parse_url($parts);
                }

                // Scheme and Host are always replaced
                if(isset($parts['scheme'])) $url['scheme']  = $parts['scheme'];
                if(isset($parts['host']))   $url['host']    = $parts['host'];

                // (If applicable) Replace the original URL with it's new parts
                if(HTTP_URL_REPLACE & $flags)
                {
                    // Go through each possible key
                    foreach(array('user','pass','port','path','query','fragment') as $key)
                    {
                        // If it's set in $parts, replace it in $url
                        if(isset($parts[$key])) $url[$key]  = $parts[$key];
                    }
                }
                else
                {
                    // Join the original URL path with the new path
                    if(isset($parts['path']) && (HTTP_URL_JOIN_PATH & $flags))
                    {
                        if(isset($url['path']) && $url['path'] != '')
                        {
                            // If the URL doesn't start with a slash, we need to merge
                            if($url['path'][0] != '/')
                            {
                                // If the path ends with a slash, store as is
                                if('/' == $parts['path'][strlen($parts['path'])-1])
                                {
                                    $sBasePath  = $parts['path'];
                                }
                                // Else trim off the file
                                else
                                {
                                    // Get just the base directory
                                    $sBasePath  = dirname($parts['path']);
                                }

                                // If it's empty
                                if('' == $sBasePath)    $sBasePath  = '/';

                                // Add the two together
                                $url['path']    = $sBasePath . $url['path'];

                                // Free memory
                                unset($sBasePath);
                            }

                            if(false !== strpos($url['path'], './'))
                            {
                                // Remove any '../' and their directories
                                while(preg_match('/\w+\/\.\.\//', $url['path'])){
                                    $url['path']    = preg_replace('/\w+\/\.\.\//', '', $url['path']);
                                }

                                // Remove any './'
                                $url['path']    = str_replace('./', '', $url['path']);
                            }
                        }
                        else
                        {
                            $url['path']    = $parts['path'];
                        }
                    }

                    // Join the original query string with the new query string
                    if(isset($parts['query']) && (HTTP_URL_JOIN_QUERY & $flags))
                    {
                        if (isset($url['query']))   $url['query']   .= '&' . $parts['query'];
                        else                        $url['query']   = $parts['query'];
                    }
                }

                // Strips all the applicable sections of the URL
                if(HTTP_URL_STRIP_USER & $flags)        unset($url['user']);
                if(HTTP_URL_STRIP_PASS & $flags)        unset($url['pass']);
                if(HTTP_URL_STRIP_PORT & $flags)        unset($url['port']);
                if(HTTP_URL_STRIP_PATH & $flags)        unset($url['path']);
                if(HTTP_URL_STRIP_QUERY & $flags)       unset($url['query']);
                if(HTTP_URL_STRIP_FRAGMENT & $flags)    unset($url['fragment']);

                // Store the new associative array in $new_url
                $new_url    = $url;

                // Combine the new elements into a string and return it
                return
                     ((isset($url['scheme'])) ? $url['scheme'] . '://' : '')
                    .((isset($url['user'])) ? $url['user'] . ((isset($url['pass'])) ? ':' . $url['pass'] : '') .'@' : '')
                    .((isset($url['host'])) ? $url['host'] : '')
                    .((isset($url['port'])) ? ':' . $url['port'] : '')
                    .((isset($url['path'])) ? $url['path'] : '')
                    .((isset($url['query'])) ? '?' . $url['query'] : '')
                    .((isset($url['fragment'])) ? '#' . $url['fragment'] : '')
                ;
            }
        }
