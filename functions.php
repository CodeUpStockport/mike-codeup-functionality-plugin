<?php
/*
 * Plugin Name: code-up functions
 * Description: Sample working functionality plugin
 * Plugin URI: https://zed1.com/
 * Author: Mike Little
 * Author URI: https://mikelittle.org/
 * Version: 1.0.7
 * GitHub Plugin URI: https://github.com/CodeUpStockport/mike-codeup-functionality-plugin
 * License: LGPLv2
 */


/* Add a filter to the post or page title to turn it into uppercase */
add_filter( 'the_title', 'my_title_filter' );
function my_title_filter( $title ) {

	if ( in_the_loop() && is_main_query() ) {
		return strtoupper( $title );
	}

	return $title;
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
		margin-bottom: 1em;
	}

    body.page-blue {
        background-color: #007acc;
    }
    body.page-red {
        background-color: #ff0000;
    }
    body.page-green {
        background-color: #009900;
    }
    body.page-purple {
        background-color: #800080;
    }
    body.page-khaki {
        background-color: #999966;
    }
    body.page-yellow {
        background-color: #ffff00;
    }
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

/* Add filter to retrieve page colour */
add_filter( 'body_class', 'add_page_colour', 10, 2 );

function add_page_colour( $classes, $class ) {

	if ( function_exists( 'get_field' ) ) {
		$page_class = get_field( 'page_colour' );
		if ( ! 0 == $page_class ) {
			$classes[] = $page_class;

			return $classes;
		}
	}

	return $classes;
}

class zed1_child_pages_widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_pages zed1', 'description' => __( "Only show a page's children" ) );
		parent::__construct( 'zed1childpages', __( 'Zed1 Child Pages' ), $widget_ops );
	}

	function form( $instance ) {
		//Defaults
		$instance     = wp_parse_args( (array) $instance, array( 'sortby' => 'post_title', 'title' => '', 'exclude' => '' ) );
		$title        = esc_attr( $instance['title'] );
		$exclude      = esc_attr( $instance['exclude'] );
		$siblings_too = isset( $instance['siblings_too'] ) ? $instance['siblings_too'] : false;
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat"
			       id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>"
			       type="text" value="<?php echo $title; ?>"/></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sortby' ); ?>"><?php _e( 'Sort by:' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'sortby' ); ?>" id="<?php echo $this->get_field_id( 'sortby' ); ?>" class="widefat">
				<option value="post_title"<?php selected( $instance['sortby'], 'post_title' ); ?>><?php _e( 'Page title' ); ?></option>
				<option value="menu_order"<?php selected( $instance['sortby'], 'menu_order' ); ?>><?php _e( 'Page order' ); ?></option>
				<option value="ID"<?php selected( $instance['sortby'], 'ID' ); ?>><?php _e( 'Page ID' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'exclude' ); ?>"><?php _e( 'Exclude:' ); ?></label>
			<input type="text" value="<?php echo $exclude; ?>"
			       name="<?php echo $this->get_field_name( 'exclude' ); ?>"
			       id="<?php echo $this->get_field_id( 'exclude' ); ?>"
			       class="widefat"/>
			<br/>
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'siblings_too' ); ?>"><?php _e( 'Include siblings:' ); ?></label>
			<input class="checkbox" type="checkbox" <?php checked( $siblings_too, true ) ?>
			       id="<?php echo $this->get_field_id( 'siblings_too' ); ?>" name="<?php echo $this->get_field_name( 'siblings_too' ); ?>"/>
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		if ( in_array( $new_instance['sortby'], array( 'post_title', 'menu_order', 'ID' ) ) ) {
			$instance['sortby'] = $new_instance['sortby'];
		} else {
			$instance['sortby'] = 'menu_order';
		}

		$instance['exclude'] = strip_tags( $new_instance['exclude'] );

		$instance['siblings_too'] = isset( $new_instance['siblings_too'] ) ? 1 : 0;

		return $instance;
	}

	function widget( $args, $instance ) {
		global $post;

		if ( ! is_page() ) {
			return;
		}

		$nothing_to_do = true;
		if ( get_children( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $post->ID ) ) ) {
			$nothing_to_do = false;
		}

		if ( isset( $instance['siblings_too'] ) && $instance['siblings_too'] && $post->post_parent ) {
			$nothing_to_do = false;
		}

		if ( $nothing_to_do ) {
			return;
		}

		add_filter( 'widget_pages_args', array( $this, 'widget_pages_args_children' ) );
		add_filter( 'widget_title', array( $this, 'widget_title' ), 10, 3 );

		//extract( $args );

		$title   = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $instance['title'], $instance, $this->id_base );
		$sortby  = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];

		if ( $sortby == 'menu_order' ) {
			$sortby = 'menu_order, post_title';
		}

		$out = wp_list_pages( apply_filters( 'widget_pages_args', array( 'title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude ) ) );

		remove_filter( 'widget_pages_args', array( &$this, 'widget_pages_args_children' ) );
		remove_filter( 'widget_title', array( &$this, 'widget_title' ), 10, 3 );

		if ( ! empty( $out ) ) {
			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			?>
			<ul>
				<?php echo $out; ?>
			</ul>
			<?php
			echo $args['after_widget'];
		}
	}

	function widget_pages_args_children( $args ) {
		global $post;

		if ( get_children( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_parent' => $post->ID ) ) ) {
			$args['child_of'] = $post->ID;
		} elseif ( $post->post_parent ) {
			$args['child_of'] = $post->post_parent;
		}

		$args['depth'] = 2;

		return $args;
	}

	function widget_title( $title, $instance, $id_base ) {
		if ( ( 'zed1childpages' == $id_base ) && ( __( 'Pages' ) == $title ) ) {
			return '';
		}

		return $title;
	}

}

add_action( 'widgets_init', 'zed1_register_zcp_widget' );
function zed1_register_zcp_widget() {
	register_widget( 'zed1_child_pages_widget' );
} // end zed1_register_zcp_widget
