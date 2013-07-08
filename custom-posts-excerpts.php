<?php
/*Add front end CSS **/
    add_action( 'wp_enqueue_scripts', 'pp_cpe_add_stylesheet' );

    /**
     * Enqueue plugin style-file
     */
    function pp_cpe_add_stylesheet() {
  	$style = plugins_url() . '/pintop/css/style.css';
	
		
        // Respects SSL, Style.css is relative to the current file
        wp_register_style( 'pp-cpe-style', $style );
        wp_enqueue_style( 'pp-cpe-style' );
    }



/*Add a thumbnail size for the widget*/

function pp_add_image_size() {

if (function_exists('add_image_size')) {

	add_image_size('pp-widget-thumbnail', 48, 48, true);

}

}

add_action('template_redirect', 'pp_add_image_size', 0);



//change excerpt length for just this widget

function new_excerpt($charlength) {

	   $excerpt = get_the_excerpt();

	   global $post;

	   $charlength++;

	   if(strlen($excerpt)>$charlength) {

		   $subex = substr($excerpt,0,$charlength-5);

		   $exwords = explode(" ",$subex);

		   $excut = -(strlen($exwords[count($exwords)-1]));

		   if($excut<0) {

				echo substr($subex,0,$excut);

		   } else {

				echo $subex;

		   }

		   echo '<a href="'. get_permalink($post->ID) . '">(Read More...)</a>';

	   } else {

		   echo $excerpt;

	   }

	}

	
	
	

	
	
/*Widget for Recent posts. */

if( !class_exists("Custom_Posts_Excerpts_Widget")){

class Custom_Posts_Excerpts_Widget extends WP_Widget{

		
	// constructor

		function Custom_Posts_Excerpts_Widget() {

			$widget_ops = array(

			'description' => 'A widget that displays a list of custom post types with excerpts.'

			);

			$this->WP_Widget('custom_posts_excerpts', 'Custom Posts Excerpts', $widget_ops);

			

			add_action( 'save_post', array(&$this, 'flush_widget_cache') );

			add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );

			add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );

		}

		

		function form($instance) {

			$title = isset($instance['title']) ? esc_attr($instance['title']) : '';

			if ( !isset($instance['number']) || !$number = (int) $instance['number'] )

				$number = 2;

				

			$posttypes = get_post_types(null, 'objects');

			$posttypes_opt = array();

			

			foreach( $posttypes as $id => $obj ) {

				if(!$obj->_builtin || $obj->labels->name == 'Pages' || $obj->labels->name == 'Posts')

					$posttypes_opt[$id] = $obj->labels->name;

			}

			

?>

			<!--title field-->

			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>

			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>



			<!--number of posts to display-->

			<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>

			<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

			

			

			<!--Dropdown of post types-->

			<p><label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type:'); ?></label>

			<select id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">

				<?php foreach($posttypes_opt as $id => $post_type){ ?>

						<option value="<?php echo $id?>" <?php echo selected($id, $instance['post_type'])?>><?php echo $post_type?></option>

				<?php } ?>

			</select></p>



<?php

	}

	

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);

		$instance['number'] = (int) $new_instance['number'];

		$instance['post_type'] = $new_instance['post_type'];

		$this->flush_widget_cache();



		$alloptions = wp_cache_get( 'alloptions', 'options' );

		if ( isset($alloptions['widget_recent_entries_custom']) )

			delete_option('widget_recent_entries_custom');



		return $instance;

	}

	

	function flush_widget_cache() {

		wp_cache_delete('custom_posts_excerpts_widget', 'widget');

	}

	

	function widget($args, $instance) {
		$plugdir = plugins_url();
		$pluginimages = $plugdir . '/pintop/images';
		$cache = wp_cache_get('widget_recent_posts_custom', 'widget');



		if ( !is_array($cache) )

			$cache = array();



		if ( isset($cache[$args['widget_id']]) ) {

			echo $cache[$args['widget_id']];

			return;

		}



		ob_start();

		extract($args);

		

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts') : $instance['title'], $instance, $this->id_base);

		if ( !$number = (int) $instance['number'] )

			$number = 10;

		else if ( $number < 1 )

			$number = 1;

		else if ( $number > 15 )

			$number = 15;

		

		if( !$post_type = $instance['post_type'] )

			$post_type = 'post';

		

		$cpst = new WP_Query(array('post_type' => $post_type, 'showposts' => $number, 'nopaging' => false, 'post_status' => 'publish'));

		

		if ($cpst->have_posts()) :

?>

		<?php echo $before_widget; ?>

		<?php if ( $title ) echo $before_title . $title . $after_title; ?>

		<ul class="pp-postsli">

		<?php  while ($cpst->have_posts()) : $cpst->the_post(); ?>

		<li>

			<div class="pp-wrapper">

				<?php if (has_post_thumbnail()) { ?>

					<div class="pp-thumbnail">			

						<?php the_post_thumbnail('pp-widget-thumbnail'); ?>		

					</div>		
			
				 <?php }else { ?>
					<div class="pp-thumbnail">		
						<img class="attachment-pp-widget-thumbnail wp-post-image" src="<?php echo $pluginimages ?>/default-thumb.jpg" alt="<?php the_title(); ?>" />
					</div>	
				<?php } ?>
				<div class="pp-wcontent">

					<div class="pp-title">

					<?php the_title(); ?>

					</div>

					<div class="pp-excerpt">

					<?php new_excerpt(57); ?>

					</div>

				</div>			

			</div>	

		</li>

		<?php endwhile; ?>

		<div class="pp-older"><?php previous_post_link_plus(array('link'=> 'View Previous')); ?></div> 

		</ul>

		<?php echo $after_widget; ?>

<?php

		// Reset the global $the_post as this query will have stomped on it

		wp_reset_postdata();



		endif;



		$cache[$args['widget_id']] = ob_get_flush();

		wp_cache_set('widget_recent_posts_custom', $cache, 'widget');

	}

	

}}



function myplugin_register_widgets() {

	register_widget( 'Custom_Posts_Excerpts_Widget' );

}



add_action( 'widgets_init', 'myplugin_register_widgets' );



?>
