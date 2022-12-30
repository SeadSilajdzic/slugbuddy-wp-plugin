<?php

/**
 * Plugin Name:       SlugBuddy
 * Description:       Plugin is fixing the issue where the media is older than the post and has the same slug.
 * Text Domain:       slugbuddy
 * Domain Path:       /slugbuddy
 * Version:           1.0
 * Author:            Sead Silajdzic
 */

 /*
 * REQUIRED INFORMATIONS PLUGIN NEEDS:
 * 1) media table name
 * 2) root path to the config file
 * 
 * 
 *
 * TODO:    MAKE CHECK IF THE PUBLISH ON POST RELATED PAGE IS CLICKED AN NOWHERE ELSE
 *          MAKE SETTINGS PAGE FOR THE PLUGIN   
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