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
    echo '<style>.post-subtitle { font-size: 150%; font-weight: bold; text-align: center; margin-bottom: 1em;} </style>';
}


/* Add a filter to retrieve and display the Post subtitle */
add_filter( 'the_content', 'add_post_subtitle' );
function add_post_subtitle( $content ) {
    error_log( "sub_title=". $sub_title );
    if ( function_exists( 'get_field' ) ) {
        $sub_title = get_field( 'sub_title' );
        error_log( "sub_title=". $sub_title );
        return '<div class="post-subtitle">' . esc_html( $sub_title ) . '</div>' . $content;
    }
    return $content;
}

