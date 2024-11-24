<?php
/**
 * Plugin Name: Duplicate Posts and Pages
 * Plugin URI: https://github.com/devclaire92/WP-Duplicate-Post
 * Description: A simple plugin to duplicate posts and pages in WordPress.
 * Version: 1.0
 * Author: Claire Ann Bayoda
 * Author URI: https://github.com/devclaire92/WP-Duplicate-Post
 * License: GPL2
 */

 // Hook to add the duplicate button to posts and pages
add_filter('post_row_actions', 'duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'duplicate_post_link', 10, 2);

// Function to add "Duplicate" link
function duplicate_post_link($actions, $post) {
    // Only add the duplicate link for posts and pages (non-draft or non-pending)
    if ($post->post_type == 'post' || $post->post_type == 'page') {
        $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=duplicate_post&post=' . $post->ID, 'duplicate_post_' . $post->ID) . '" title="Duplicate this post/page">Duplicate</a>';
    }
    return $actions;
}

// Action hook to duplicate the post/page
add_action('admin_action_duplicate_post', 'duplicate_post');
function duplicate_post() {
    // Check nonce for security
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'duplicate_post_' . $_GET['post'])) {
        wp_die('Security check failed');
    }

    // Get the original post ID
    $post_id = $_GET['post'];

    // Get the original post object
    $post = get_post($post_id);

    // If post doesn't exist, return
    if (!$post) {
        wp_die('Post not found.');
    }

    // Create a new post object with the same content as the original post
    $new_post = array(
        'post_title'   => $post->post_title . ' (Copy)', // Add a suffix to the title
        'post_content' => $post->post_content,
        'post_status'  => 'draft', // New post is saved as draft
        'post_type'    => $post->post_type,
        'post_author'  => $post->post_author,
        'post_date'    => current_time('mysql'),
        'post_date_gmt'=> current_time('mysql', 1)
    );

    // Insert the new post
    $new_post_id = wp_insert_post($new_post);

    // Copy the post meta
    $meta_data = get_post_meta($post_id);
    foreach ($meta_data as $key => $values) {
        foreach ($values as $value) {
            update_post_meta($new_post_id, $key, $value);
        }
    }

    // Redirect to the edit screen of the new post
    wp_redirect(admin_url('post.php?post=' . $new_post_id . '&action=edit'));
    exit;
}
?>
