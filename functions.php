<?php
/*
 * Plugin Name: code-up functions
 * GitHub Plugin URI: https://github.com/CodeUpStockport/mike-codeup-functionality-plugin
*/

/* Add a filter to the post or page title to turn it into uppercase */
add_filter( 'the_title', 'my_title_filter' );
function my_title_filter( $title ) {
	if ( is_admin() ) {
		return $title;
	}
	return strtoupper( $title );
}

/* turn off WordPress' own make paragraph tags function */
//remove_filter( 'the_content', 'wpautop' );

/* Add Some additional CSS to the html head on the page */
add_action( 'wp_head', 'my_head_action', 99 );
function my_head_action() {
	echo '<style>
	.post-subtitle {
		font-size: 150%;
		font-weight: bold;
		text-align: center;
		margin-bottom: 1em;}
	</style>';

}

/* Add a filter to retrieve and display the Post Subtitle field we added */
add_filter( 'the_content', 'add_post_subtitle' );

function add_post_subtitle( $content ) {

	if ( function_exists( 'get_field' ) ) {
		$sub_title = get_field( 'sub_title' );
		if ( ! empty( $sub_title ) ) {
			return '<div class="post-subtitle">' . esc_html( $sub_title ) . '</div>' . $content;
		}
	}
	return $content;
}
