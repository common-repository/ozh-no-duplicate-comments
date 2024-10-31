<?php
/*
Plugin Name: Ozh' No Duplicate Comments
Plugin URI: http://planetozh.com/blog/
Description: Marks as spam attempts to duplicate a comment but with another commenter name/URI
Author: Ozh
Version: 1.0
Author URI: http://ozh.org/
*/

function ozh_no_duplicate_comments ( $comment_text ) {
	global $wpdb, $commentdata;
	
	// search for same $commentdata['comment_content'] in the same post
	$sql = "SELECT * from {$wpdb->comments}
		WHERE comment_content = '{$commentdata['comment_content']}'
		AND comment_post_ID = '{$commentdata['comment_post_ID']}'
		ORDER BY `comment_ID` DESC LIMIT 0,1;";
	$check = $wpdb->get_row( $sql );
	
	if( $check ) {
		// same comment content, but author name, email or URL different?
		if( $commentdata['comment_author'] != $check->comment_author
			OR
			$commentdata['comment_author_url'] != $check->comment_author_url
			OR
			$commentdata['comment_author_email'] != $check->comment_author_email			
		) {
			// omg this is spam!
			add_filter('pre_comment_approved', create_function('$a', 'return \'spam\';'));
			// while we're at it, don't redirect to post page
			add_filter('comment_post_redirect', 'ozh_no_duplicate_reject_screen');
		}
	}

	return $comment_text;
}

function ozh_no_duplicate_reject_screen() {
	@header('HTTP/1.1 403 Forbidden');
	wp_die('Sorry, your comment was considered as spam and rejected');
}

add_filter('pre_comment_content', 'ozh_no_duplicate_comments');

