<?php

/**
 * Plugin Name:       SlugBuddy
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Plugin is fixing the issue where the media is older than the post and has the same slug.
 * Text Domain:       slugbuddy
 * Domain Path:       /slugbuddy
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sead Silajdzic
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 */

 /*
 * REQUIRED INFORMATIONS PLUGIN NEEDS:
 * 1) media table name
 * 2) root path to the config file
 * 
 * 
 *
 * TODO:     MAKE CHECK IF THE PUBLISH ON POST RELATED PAGE IS CLICKED AN NOWHERE ELSE
 * 
*/

function slugbuddy($post_id, $data) {
        // REQUIRED PROPERTIES FOR PLUGIN TO WORK
        $posts_table = 'clk_9951ea7ee0_wp_posts';    
        // Include wp config
        include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');


        // Shorthands
        $post_title = $data['post_title'];
        $request_method = $_SERVER['REQUEST_METHOD'] == 'POST';
        $unique_title = $post_title . time();
        // Make slug out of the post title
        $slug = sluggy($post_title);


        // Global variables
        global $wpdb;


        // Check if the request method is post - Without this check, code executes even on get methods
        if($request_method) {
            // Check if attachment has taken the slug
            $attachment = $wpdb->get_results("SELECT * FROM `$posts_table` where (post_title = '$slug' or post_name = '$slug') and post_type = 'attachment'");
            $wpdb->update($posts_table, ['post_title' => $unique_title, 'post_name' => $unique_title], ['ID' => $attachment[0]->ID]);
        }
}


function sluggy($title) {
    return preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $title));
}

add_action('pre_post_update', 'slugbuddy', 10, 2);