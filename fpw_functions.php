<?php

function fpw_update_featured_image($post_id, $img_url) {
    // Get upload directory
    $uploads = wp_upload_dir();
    $upload_dir = $uploads['path'];
    $upload_url = $uploads['url'];

    // Download to the proper place
    $file_base = basename($img_url);
    $downloaded = "$upload_dir/$file_base";

    $already_downloaded = file_exists($downloaded);
    if ($already_downloaded) {
        $curr_id = get_post_thumbnail_id($post_id);
        $curr_file = get_attached_file($curr_id);

        if ($curr_file != $downloaded) {
            // If there is already a file where we want to save it but it
            // belongs to something else, we need to save it elsewhere
            $pi = pathinfo($img_url);
            $extra = "";
            while (file_exists($downloaded)) {
                $curr_id = get_post_thumbnail_id($post_id);
                $curr_file = get_attached_file($curr_id);
                if ($curr_file == $downloaded) {
                    $already_downloaded = true;
                    break;
                }
                
                $extra .= "-{$post_id}";
                $file_base = "{$pi['filename']}{$extra}.{$pi['extension']}";
                $downloaded = "$upload_dir/$file_base";
                $already_downloaded = false;
            }
        }
        // But if the file exists, but it's the current featured image,
        // it's ok to overwrite it
    }

    $downloaded_url = "$upload_url/$file_base";
    copy($img_url, $downloaded);

    // Check the type of tile. We'll use this as the 'post_mime_type'.
    $filetype = wp_check_filetype($downloaded, null);

    if (!$already_downloaded) {
        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid'           => $downloaded_url, 
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Insert the attachment, if not already
        $attach_id = wp_insert_attachment($attachment, $downloaded, $post_id);
    } else $attach_id = $curr_id;

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attach_id, $downloaded);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // Now set as the Featured Image (aka Post Thumbnail)
    set_post_thumbnail($post_id, $attach_id);
}

function fpw_add_feed_error($url, $error_msg) {
    $feeds = get_option('feed-post-writer-feeds');
    foreach($feeds as &$f) if ($f['url'] == $url) $f['error'] = $error_msg;
    update_option('feed-post-writer-feeds', $feeds);
}

function return_3600($seconds) { return 3600; }

function fpw_update_post($url, $post_id, $args = array()) {
    $r = array();
    if (wp_is_post_revision($post_id)) return false;

    add_filter('wp_feed_cache_transient_lifetime', 'return_3600');    // Only cache for one hour, instead of twelve
    $feed = fetch_feed($url);
    remove_filter('wp_feed_cache_transient_lifetime', 'return_3600'); // Remove feed cache lifetime

    if (is_wp_error($feed)) {
        fpw_add_feed_error($url, $feed->get_error_message());
        return false;
    }

    $entry = $feed->get_item();
    $post = get_post($post_id);

    if (empty($post)) {
        fpw_add_feed_error($url, 'Invalid post ID.');
        return false;
    }

    $content = $entry->get_content();
    if (!empty($args['use_header_footer'])) {
        $r['adding_header_footer'] = true;
        if (!empty($args['header'])) $content = $args['header'] . "\n\n" . $content;
        if (!empty($args['footer'])) $content .= "\n\n" . $args['footer'];
    } else $r['adding_header_footer'] = false;
    $post->post_content = $content;
    $post->post_modified_gmt = $entry->get_gmdate('Y-m-d H:i:s');
    $post->post_modified = get_date_from_gmt($post->post_modified_gmt);
    if (!empty($args['update_title'])) $post->post_title = $entry->get_title();
    $r['content'] = $content;
    $r['post_modified'] = $post->post_modified;

    $r['update'] = wp_update_post($post, true);
    
    if (is_wp_error($r)) {
        fpw_add_feed_error($url, $r->get_error_message());
        return false;
    }

    if (!empty($args['update_featured_image'])) {
        $e = $entry->get_enclosure();
        if (!is_null($e->get_link())) $r['update_image'] = fpw_update_featured_image($post_id, $e->get_link());
    }
    return $r;
}

function fpw_update_on_schedule($url) {
    $feeds = get_option('feed-post-writer-feeds');
    foreach($feeds as $f) {
        if ($f['url'] == $url) {
            $r = fpw_update_post($url, $f['pid'], $f);
            $f['success'] = $r;
            return($f);
        }
    }
}
add_action('fpwupdateonschedulehook', 'fpw_update_on_schedule');

function fpw_update_feed_crons($oldfeeds, $newfeeds) {
    $schedules = wp_get_schedules();
    
    $oldfeed_array = array();
    $newfeed_array = array();
    if (is_array($oldfeeds)) foreach($oldfeeds as $f) if (!empty($f['url']) && !empty($f['pid'])) $oldfeed_array[$f['url']] = $f;
    if (is_array($newfeeds)) foreach($newfeeds as $f) if (!empty($f['url']) && !empty($f['pid'])) $newfeed_array[$f['url']] = $f;
    $no_change = array();

    foreach($oldfeed_array as $url => $f) {
        if (!isset($newfeed_array[$url])) {
            // Remove cron job, as this feed is no longer used
            $ts = wp_next_scheduled('fpwupdateonschedulehook', array($url));
            wp_unschedule_event($ts, 'fpwupdateonschedulehook', array($url));
        } else {
            // Check that the schecule hasn't changed.
            $old = $f;
            $new = $newfeed_array[$url];
            if ($old['schedule'] != $new['schedule']) {
                // Remove old cron job, and create new one
                $ts = wp_next_scheduled('fpwupdateonschedulehook', array($url));
                wp_unschedule_event($ts, 'fpwupdateonschedulehook', array($url));
                if (array_key_exists($new['schedule'], $schedules))
                    wp_schedule_event(time(), $new['schedule'], 'fpwupdateonschedulehook', array($url));
                else fpw_add_feed_error($url, "Invalid update schedule.");
            } else $no_change[] = $f['url'];
        }
    }

    foreach($newfeed_array as $url => $f) {
        if (!isset($oldfeed_array[$url])) {
            // Add new cron job for additional feed
            if (array_key_exists($f['schedule'], $schedules))
                wp_schedule_event(time(), $f['schedule'], 'fpwupdateonschedulehook', array($url));
            else fpw_add_feed_error($url, "Invalid update schedule.");
        }
    }

    // Returns all feeds that didn't change (and therefore, didn't run)
    return $no_change;
}
