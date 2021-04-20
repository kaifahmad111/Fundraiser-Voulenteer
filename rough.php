<?php

//* Create Golf Courses custom post type
add_action( 'init', 'golf_courses_post_type' );
function golf_courses_post_type() {

	register_post_type( 'golf-courses',
		array(
			'labels' => array(
				'name'          => __( 'Golf Courses', 'genesis' ),
				'singular_name' => __( 'Golf Course', 'genesis' ),
			),
			'has_archive'  => true,
			'hierarchical' => true,
			'menu_icon'    => 'dashicons-flag',
			'public'       => true,
			'rewrite'      => array( 'slug' => 'golf-course/%country-type%/%region-type%', 'with_front' => false ),
			'query_var'    => true,
			'supports'     => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'revisions', 'page-attributes', 'genesis-seo', 'genesis-cpt-archives-settings' ),
			'taxonomies'   => array( 'country-type', 'region-type' ),

		)
	);
	
}


//* Create golf course custom taxonomy
add_action( 'init', 'golf_courses_taxonomy' );
function golf_courses_taxonomy() {

	register_taxonomy( 'country-type', 'golf-courses',
		array(
			'labels' => array(
				'name'          => _x( 'Country', 'taxonomy general name', 'genesis' ),
				'add_new_item'  => __( 'Add New Country', 'genesis' ),
				'new_item_name' => __( 'New Country', 'genesis' ),
			),
			'exclude_from_search' => true,
			'has_archive'         => true,
			'hierarchical'        => true,
			'rewrite'             => array( 'slug' => 'country-type', 'with_front' => false ),
			'show_ui'             => true,
			'show_tagcloud'       => false,
		)
	);
	
	register_taxonomy( 'region-type', 'golf-courses',
		array(
			'labels' => array(
				'name'          => _x( 'Region', 'taxonomy general name', 'genesis' ),
				'add_new_item'  => __( 'Add New Region', 'genesis' ),
				'new_item_name' => __( 'New Region', 'genesis' ),
			),
			'exclude_from_search' => true,
			'has_archive'         => true,
			'hierarchical'        => true,
			'rewrite'             => array( 'slug' => 'region-type', 'with_front' => false ),
			'show_ui'             => true,
			'show_tagcloud'       => false,
		)
	);

}

add_filter( 'post_type_link', 'golf_courses_country_post_type_link', 10, 2 );
function golf_courses_country_post_type_link( $link, $post ) {

  if ( $post->post_type == 'golf-courses' ) {
  
    if ( $cats = get_the_terms( $post->ID, 'country-type' ) ) {
      $link = str_replace( '%country-type%', current($cats)->slug, $link );
    }
  }
  return $link;
}

add_filter( 'post_type_link', 'golf_courses_region_post_type_link', 10, 2 );
function golf_courses_region_post_type_link( $link, $post ) {

if ( $post->post_type == 'golf-courses' ) {
  
if ( $cats = get_the_terms( $post->ID, 'region-type' ) ) {
      $link = str_replace( '%region-type%', current($cats)->slug, $link );
    }
  }
  return $link;
}
///////////////////////////////////////////////////////////////////////////////////////
/*
 * Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
 */
function rd_duplicate_post_as_draft(){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
	  wp_die('No post to duplicate has been supplied!');
	}
   
	/*
	 * Nonce verification
	 */
	if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
	  return;
   
	/*
	 * get the original post id
	 */
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );
   
	/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;
   
	/*
	 * if post data exists, create the post duplicate
	 */
	if (isset( $post ) && $post != null) {
   
	  /*
	   * new post data array
	   */
	  $args = array(
		'comment_status' => $post->comment_status,
		'ping_status'    => $post->ping_status,
		'post_author'    => $new_post_author,
		'post_content'   => $post->post_content,
		'post_excerpt'   => $post->post_excerpt,
		'post_name'      => $post->post_name,
		'post_parent'    => $post->post_parent,
		'post_password'  => $post->post_password,
		'post_status'    => 'draft',
		'post_title'     => $post->post_title,
		'post_type'      => $post->post_type,
		'to_ping'        => $post->to_ping,
		'menu_order'     => $post->menu_order
	  );
   
	  /*
	   * insert the post by wp_insert_post() function
	   */
	  $new_post_id = wp_insert_post( $args );
   
	  /*
	   * get all current post terms ad set them to the new post draft
	   */
	  $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
	  foreach ($taxonomies as $taxonomy) {
		$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
		wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
	  }
   
	  /*
	   * duplicate all post meta just in two SQL queries
	   */
	  $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
	  if (count($post_meta_infos)!=0) {
		$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
		foreach ($post_meta_infos as $meta_info) {
		  $meta_key = $meta_info->meta_key;
		  if( $meta_key == '_wp_old_slug' ) continue;
		  $meta_value = addslashes($meta_info->meta_value);
		  $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
		}
		$sql_query.= implode(" UNION ALL ", $sql_query_sel);
		$wpdb->query($sql_query);
	  }
   
   
	  /*
	   * finally, redirect to the edit post screen for the new draft
	   */
	  wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
	  exit;
	} else {
	  wp_die('Post creation failed, could not find original post: ' . $post_id);
	}
  }
  add_action( 'admin_action_rd_duplicate_post_as_draft', 'rd_duplicate_post_as_draft' );
   
  /*
   * Add the duplicate link to action list for post_row_actions
   */
  function rd_duplicate_post_link( $actions, $post ) {
	if (current_user_can('edit_posts')) {
	  $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=rd_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
	}
	return $actions;
  }
   
  add_filter( 'post_row_actions', 'rd_duplicate_post_link', 10, 2 );

  //////////////////////////////////////////////////////////////////////////////////////////////
  /* With my Edits */
  /*
 * Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
 */
function rd_duplicate_post_as_draft(){
	global $wpdb;
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
	  wp_die('No post to duplicate has been supplied!');
	}
   
	/*
	 * Nonce verification
	 */
	if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
	  return;

	/*
	 * get the original post id
	 */
	$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
	/*
	 * and all the original post data then
	 */
	$post = get_post( $post_id );
	$cpt =  get_post_type( $post_id );	//this will give me the name of the custom post type

	//Now I only want it to duplicate (into voulenteer CPT) only when the post type is "give_forms" 
	//of All forms on Admin Backend

    if( 1/*$cpt == "give_forms"*/) {
		/*
	 * if you don't want current user to be the new post author,
	 * then change next couple of lines to this: $new_post_author = $post->post_author;
	 */
		// $current_user = wp_get_current_user();
		// $new_post_author = $current_user->ID;
		$new_post_author = $post->post_author;
   
		/*
		 * if post data exists, create the post duplicate
		 */
		if (isset( $post ) && $post != null) {
	   
		  /*
		   * new post data array
		   */
		  $args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $post->post_parent,
			'post_password'  => $post->post_password,
			'post_status'    => 'publish',
			'post_title'     => $post->post_title,
			'post_type'      => 'voulunteer',
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		  );
	   
		  /*
		   * insert the post by wp_insert_post() function
		   */
		  $new_post_id = wp_insert_post( $args );
		  	//This is the post Id of our newly created
		  	/*
		  	* duplicate all post meta just in two SQL queries
		    * This is something that i am interested in
		    * making two variables for country and state 
		    * I have to retrieve meat key values and use it as a taxonomy 
		   	*/
			$country = "";
			$state = "";	

		  $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		  
		  if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
			  $meta_key = $meta_info->meta_key;

			  if( $meta_key == '_wp_old_slug' ) continue;

			  //Checking out for country metakey that I set in the custom form plugin
			  if( $meta_key == 'countries' ) {
				$country = addslashes($meta_info->meta_value);
			  }
			  
			  //Checking out for state metakey that I set in the custom form plugin
			  if( $meta_key == 'states' ) {
				$state = addslashes($meta_info->meta_value);
			  }

			  $meta_value = addslashes($meta_info->meta_value);
			  $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);

			//Lets set our taxonomy for country and states straight
			wp_set_object_terms( $new_post_id, $country, 'countries' );
    		wp_set_object_terms( $new_post_id, $state, 'states' );    
			}
	   
	   
		  /*
		   * finally, redirect to the edit post screen for the new draft
		   */
		wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		  exit;
		} else {
		  wp_die('Post creation failed, could not find original post: ' . $post_id);
		}
	}
}
add_action( 'admin_action_rd_duplicate_post_as_draft', 'rd_duplicate_post_as_draft' );
 
  


