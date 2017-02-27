<?php
/* Plugin Name: code-up functions
*/

/* Add a filter to the post or page title to turn it into uppercase */
add_filter( 'the_title', 'my_title_filter' );
function my_title_filter( $title ) {
    return strtoupper( $title );
}

/* turn off WordPress' own make paragraph tags function */
//remove_filter( 'the_content', 'wpautop' );

/* add my own little message to the html head on the page */
add_action( 'wp_head', 'my_head_action', 99 );
function my_head_action() {
    echo '<!-- Mike Woz ere -->';
}
