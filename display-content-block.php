<?php	
/*
 Plugin Name: Display Content Block
 
 Description: Show the content of a custom post of the type 'content_block' or 'page' in a widget with a featured image.
 Version: 1.0
 Author: Dave Yankowiak
 Author URI: http://liftdevelopment.com
 Text Domain: display-content-block
 License: GPL2

 Copyright 2017 Dave Yankowiak

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 2, as
 published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/	
		

/* Add our function to the widgets_init hook. */
add_action( 'widgets_init', 'DisplayPageInit' );

/* Function that registers our widget. */
function DisplayPageInit() {
	register_widget( 'Display_Content_Block' );
}

class Display_Content_Block extends WP_Widget {

	function Display_Content_Block() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'display-content-block', 'description' => 'Display a block or page.' );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'display-content-block' );

		/* Create the widget. */
		$this->WP_Widget( 'display-content-block', 'Display Content Block', $widget_ops, $control_ops );
	}
	
	function widget($args, $instance) {		
        
        extract( $args );
		global $wpdb;
		
		$title = $instance['title'];
		
		$subtitle = $instance['subtitle'];
		$page_id = $instance['page_id'];
		$imagesize = $instance['image_size'];	
		
		$show_custom_post_title  = isset( $instance['show_custom_post_title'] ) ? $instance['show_custom_post_title'] : true;
			
        ?>
              <?php echo $before_widget; ?>
			  

				<div class="page-content">
				
				<?php				
                $querystr = "
                SELECT wposts.* 
                FROM $wpdb->posts wposts
                WHERE wposts.ID = $page_id                
                AND (wposts.post_type = 'page' 
                OR wposts.post_type = 'content_block')
                ORDER BY wposts.post_date DESC
                 ";
            
                $result = $wpdb->get_results($querystr, OBJECT);
				
                if ($result) {					 
					foreach ($result as $pagecontent) {
						
						
						$title = '';
						if (isset( $instance['show_custom_post_title'] )) {
							$title = get_the_title($pagecontent->ID);
						} else {
							$title = '';
						}
						
						$blockurl =  get_post_meta($pagecontent->ID,'ecpt_blockurl', 'True');
						           
							
						$src = wp_get_attachment_image_src( get_post_thumbnail_id($pagecontent->ID), $imagesize, true );		 
							 
						
						
						
						echo '<div class="page-details">';
						
						if($title) {
							echo $before_title
							. $title
							. $after_title; 
							 } 
							 
						if($subtitle) {
							echo '<h4 class="jm-subtitle">'
							. $subtitle
							. '</h4>'; 
							 } 
						
						if ($imagesize && has_post_thumbnail($pagecontent->ID)) {
							echo '<div class="block-thumb"><img src="' . $src[0] . '" /></div>';	
						}
						
						$content = apply_filters("the_content", $pagecontent->post_content);
												
						echo '<div class="page-text">' . do_shortcode($content) . '</div>';	
						
						echo '</div>';
										
					}
                 } ?>
                </div><!--/ .page-content -->        
                
                                   
               <?php echo $after_widget;
    }
	
	function update($new_instance, $old_instance) {						
        return $new_instance;
    }
	
	function form($instance) {				
        $title = esc_attr($instance['title']);
        $subtitle = esc_attr($instance['subtitle']);
		$page_id = esc_attr($instance['page_id']);
		$text = esc_attr($instance['text']);
		$selected_size = esc_attr($instance['image_size']);
		
		$show_custom_post_title  = isset( $instance['show_custom_post_title'] ) ? $instance['show_custom_post_title'] : true;
		
        ?>
	        
	        
            
            <p><label for="<?php echo $this->get_field_id('page_id'); ?>"><?php _e('Page',liftdev); ?>
            <select name="<?php echo $this->get_field_name('page_id'); ?>" class="widefat">
            <?php
            
            $args = array(
            	'posts_per_page'  => 1000,
            	'orderby'         => 'title',
            	'order'           => 'ASC',
			    'post_type'       => array('pagexxx','content_block'),
			    'post_status'     => 'publish');
			 
            foreach (get_posts($args) as $tj_page) echo '<option value="'.$tj_page->ID.'" '.(( $tj_page->ID == $page_id)? 'selected':'').'>'.$tj_page->post_title.' (' . $tj_page->post_type . ')</option>';
            ?>
            </select></label></p>
            
            
            <p>
				<input class="checkbox" type="checkbox" <?php checked( (bool) isset( $instance['show_custom_post_title'] ), true ); ?> id="<?php echo $this->get_field_id( 'show_custom_post_title' ); ?>" name="<?php echo $this->get_field_name( 'show_custom_post_title' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_custom_post_title' ); ?>"><?php echo __( 'Show Post Title', 'custom-post-widget' ) ?></label>
			</p>
            
            
            <p>
            <label for="<?php echo $this->get_field_id('image_size'); ?>"><?php _e('Image Size',liftdev); ?>
            	<?php             	
	            	
	            	global $_wp_additional_image_sizes;
					$sizes = array();
					$get_intermediate_image_sizes = get_intermediate_image_sizes(); 
					
				?>
				<select name="<?php echo $this->get_field_name('image_size'); ?>">
					<option value="">-none-</option>
				  <?php foreach ($get_intermediate_image_sizes as $size_name ): ?>
				    <option value="<?php echo $size_name ?>" <?php if($size_name == $selected_size){ echo 'selected'; } ?>><?php echo $size_name ?></option>
				  <?php endforeach; ?>
				</select>
            </label>
            </p>
            
            
        <?php 
    }
}

?>
