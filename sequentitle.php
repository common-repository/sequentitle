<?php
/*
Plugin Name: SequenTitle
Plugin URI: http://wordpress.org/extent/plugins/sequentitle/
Description: Automagically set a sequentially numbered post title if you're too lazy to remember what number you're up to.
Author: Keith Constable
Version: 1.0.0
Author URI: http://kccricket.net/
Generated At: www.wp-fun.co.uk;
*/ 

/*  Copyright 2009  Keith Constable  (email : kccricket@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('SequenTitle')) {
    class SequenTitle {
		
		/**
		* PHP 4 Compatible Constructor
		*/
		function SequenTitle() {$this->__construct();}
		
		/**
		* PHP 5 Constructor
		*/		
		function __construct() {
			add_action('admin_menu', array(&$this,'add_admin_pages'));
			add_action('publish_post', array(&$this,'wp_title_intercept'), 10, 2);

			load_textdomain('sequentitle', dirname(__FILE__) . '/languages/sequentitle-.'. get_locale() .'.mo');
		}
		
		/**
		* Retrieves the options from the database.  Initialize with the defaults if the
		* option isn't set
		*
		* @return array An array of the options.
		*/
		function getOptions() {
			$defaults = array('title' => 'Post #%%', 'index' => 1);
			$options = array();

			foreach ($defaults as $key=>$value) {
				$options[$key] = get_option( "sequentitle_$key" );

				if ( empty($options[$key]) ) {
					add_option( "sequentitle_$key", $value);
					$options[$key] = $value;
				}
			}

			return $options;
		}

		/**
		* Registers the options page.
		*/		
		function add_admin_pages() {
			add_submenu_page('options-general.php', 'SequenTitle', 'SequenTitle', 'manage_options', 'sequentitle', array(&$this,'output_sub_admin_page_0'));
		}

		/**
		* Outputs the HTML for the admin sub page.
		*/
		function output_sub_admin_page_0() {
			$options = $this->getOptions();
			?>
			<div class="wrap">
				<h2><?php _e('SequenTitle Options', 'sequentitle'); ?></h2>
				<form method="post" action="options.php">
				<?php wp_nonce_field('options-options'); ?>
				<input type="hidden" name="action" value="update" />
				<input type='hidden' name='option_page' value='options' />
				<input type="hidden" name="page_options" value="sequentitle_title,sequentitle_index" />

				<p><?php _e("Enter the title you'd like to automatically use.", 'sequentitle'); ?><br/>
				<?php _e( sprintf('%s will be replaced with the current index.', '<code>%%</code>'), 'sequentitle' ); ?><br/>
				<input name="sequentitle_title" type="text" id="sequentitle_title" value="<?php echo attribute_escape($options['title']); ?>" class="regular-text code" /></p>
	
				<p><?php _e('This is the index number that will be used on your next empty post title.', 'sequentitle'); ?><br/>
				<input name="sequentitle_index" type="text" id="sequentitle_index" value="<?php echo attribute_escape($options['index']); ?>" class="regular-text code" /></p>

				<p class="submit"><input type="submit" name="Update" value="<?php _e('Save Changes') ?>" class="button-primary" /></p>
				</form>
			</div>
			<?php
		} 

		/**
		* If a title of a post is empty when it is published, sets our lazy title and,
		* if needed, updates the slug.  Only increments the index if the post was
		* sucessfully updated.
		*
		* @param int $postID ID number of the post to operate on.
		* @param object $post The WP Post object to operate on.
		*/
		function wp_title_intercept($postID, $post) {
			$options = $this->getOptions();
			
			if ($post->post_title === '') {
				$post->post_title = str_replace('%%', $options['index'], $options['title']);

				if ($post->post_name == $postID) {
					$post->post_name = sanitize_title($post->post_title);
				}

				$result = wp_update_post($post);

				if ($result !== 0 && !is_wp_error($result)) {
					update_option('sequentitle_index', $options['index'] + 1);
				}
			}
		}
    }
}

//instantiate the class
if (class_exists('SequenTitle')) {
	$SequenTitle = new SequenTitle();
}


?>