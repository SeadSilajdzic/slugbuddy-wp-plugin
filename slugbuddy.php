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
*/

class SlugBuddy {
    // Automatically called actions
    function __construct() {
        add_action('admin_menu', [$this, 'admin_page']);
        add_action('admin_init', [$this, 'settings']);
        add_action('pre_post_update', [$this, 'slugbuddy_fn'], 10, 2);
    }

    function slugbuddy_fn($post_id, $data) {
        // REQUIRED PROPERTIES FOR PLUGIN TO WORK
        $posts_table = get_option('sbp_media_table_name') ?? 'wp_posts';    
        // Include wp config
        $config_root_path = get_option('sbp_root_path_to_config') ?? $_SERVER['DOCUMENT_ROOT'].'/wp-config.php';
        include_once($config_root_path);

        // Shorthands
        $post_title = $data['post_title'];
        $request_method = $_SERVER['REQUEST_METHOD'] == 'POST';
        $unique_title = $post_title . time();
        // Make slug out of the post title
        $slug = $this->sluggy($post_title);


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


    // Add settings page for slugbuddy
    function admin_page() {
        /* 
        * 1 - Title name
        * 2 - Settings link name
        * 3 - Permission
        * 4 - Slug
        * 5 - Output
        */
        add_options_page('SlugBuddy Settings', 'SlugBuddy Settings', 'manage_options', 'slugbuddy', [$this, 'settings_html']);
    }  
    
    // Main output function
    function settings_html() { ?>
        <div class="wrap">
            <h1>SlugBuddy management</h1>
        </div>

        <form action="options.php" method="POST">
            <?php 
                settings_fields('slugbuddyplugin');
                do_settings_sections('slugbuddy');
                submit_button();
            ?>
        </form>
    <?php } 
    
    function settings() {
        /**
         * Name of the section
         * Subtitle text
         * Description of the section
         * Slug
         */
        add_settings_section('sbp_first_section', null, null, 'slugbuddy');

        /** add_settings_field()
         * Name of field
         * Label text
         * Fn to display HTML
         * Page slug for settings page
         * Section where to add this field
         */

         /** register_setting()
         * Setting group name
         * Option name
         * Options
        */
        add_settings_field('sbp_media_table_name', 'Media table name', [$this, 'table_name_html'], 'slugbuddy', 'sbp_first_section');
        register_setting('slugbuddyplugin', 'sbp_media_table_name', ['sanitize_callback' => 'sanitize_text_field', 'default' => 'wp_posts']);
        
        add_settings_field('sbp_root_path_to_config', 'Default config path', [$this, 'config_path_html'], 'slugbuddy', 'sbp_first_section');
        register_setting('slugbuddyplugin', 'sbp_root_path_to_config', ['sanitize_callback' => 'sanitize_text_field', 'default' => $_SERVER['DOCUMENT_ROOT'].'/wp-config.php']);
    }

    function table_name_html() { ?>   
        <input type="text" value="<?php echo esc_attr(get_option('sbp_media_table_name')); ?>" name="sbp_media_table_name" style="width: 500px;" placeholder="Media table name"> 
    <?php }

    function config_path_html() { ?>         
        <input type="text" value="<?php echo esc_attr(get_option('sbp_root_path_to_config')); ?>" name="sbp_root_path_to_config" style="width: 500px;" placeholder="Default config path">
    <?php }    
}

$slugBuddy = new SlugBuddy();