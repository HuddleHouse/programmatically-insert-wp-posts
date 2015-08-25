<?php


/**
 * Show insert posts button on backend
 */
add_action( "admin_notices", function() {
    echo "<div class='updated'>";
    echo "<p>";
    echo "To insert the posts into the database, click the button to the right.";
    echo "<a class='button button-primary' style='margin:0.25em 1em' href='{$_SERVER["REQUEST_URI"]}&insert_sitepoint_posts'>Insert Posts</a>";
    echo "</p>";
    echo "</div>";
});
/**
 * Create and insert posts from CSV files
 */
add_action( "admin_init", function() {
	global $wpdb;

	if ( ! isset( $_GET["insert_sitepoint_posts"] ) ) {
		return;
	}

	// Change these to whatever you set
	$sitepoint = array(
		"custom-post-type" => "map-post",
	);

	// Get the data from all those CSVs!
	$posts = function() {
		$data = array();
		$errors = array();
		
		// Get array of CSV files
		$file = "http://tcwn.245tech.com/wp-content/uploads/2015/08/fountains.csv";
			// Attempt to change permissions if not readable

			// Check if file is writable, then open it in 'read only' mode
			if (  $_file = fopen( $file, "r" ) ) {
				// To sum this part up, all it really does is go row by
				//  row, column by column, saving all the data
				$post = array();
				// Get first row in CSV, which is of course the headers
		    	$header = fgetcsv( $_file );
		        while ( $row = fgetcsv( $_file ) ) {
		            foreach ( $header as $i => $key ) {
	                    $post[$key] = $row[$i];
	                }
	                $data[] = $post;
		        }
				fclose( $_file );
			} else {
				$errors[] = "File '$file' could not be opened. Check the file's permissions to make sure it's readable by your server.";
			
		}
		if ( ! empty( $errors ) ) {
			// ... do stuff with the errors
		}
		return $data;
	};
	// Simple check to see if the current post exists within the
	//  database. This isn't very efficient, but it works.
	$post_exists = function( $title ) use ( $wpdb, $sitepoint ) {
		// Get an array of all posts within our custom post type
		$posts = $wpdb->get_col( "SELECT post_title FROM {$wpdb->posts} WHERE post_type = '{$sitepoint["custom-post-type"]}'" );
		// Check if the passed title exists in array
		return in_array( $title, $posts );
	};
	foreach ( $posts() as $post ) {

		// Insert the post into the database
		$post["id"] = wp_insert_post( array(
			"post_title" => $post["partner"],
			"post_type" => $sitepoint["custom-post-type"],
			"post_category" => array(7), //this adds a category to the post
			"post_status" => "publish"
		));
		
		// Update post's custom fields with data
		update_field("x-axis", 30, $post["id"] );
		update_field("y-axis", 70, $post["id"] );
		
		$address = $post["num"] . " " . $post["street"]	. " " . $post["dr"] . " " . $post["city"]. ", TN";	

		update_field("partner", $post["partner"], $post["id"] );
		update_field("address", $address, $post["id"] );
	}
});
