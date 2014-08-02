<?php

require_once(plugin_dir_path(__FILE__) . 'fpw_functions.php');

function fpw_add_cron_schedules($schedules) {
    // It should already include hourly, twicedaily and daily

    // add a 'bihourly' schedule
    $schedules['bihourly'] = array(
        'interval' => 7200, // 2 hours * 60 minutes * 60 seconds
        'display' => __('Every Other Hour')
    );

    // add a 'twiceweekly'' schedule
    $schedules['twiceweekly'] = array(
        'interval' => 302400, // 3.5 days * 24 * 60 minutes * 60 seconds
        'display' => __('Twice a week')
    );

    // add a 'weekly' schedule
    $schedules['weekly'] = array(
        'interval' => 604800, // 7 days * 24 hours * 60 minutes * 60 seconds
        'display' => __('Once Weekly')
    );

    // Add a 'biweekly' schedule
    $schedules['biweekly'] = array(
        'interval' => 1209600, // 2 weeks * 7 days * 24 hours * 60 minutes * 60 seconds
        'display' => __('Every Other Week')
    );

    // Add a monthly schedule
    $schedules['monthly'] = array(
        'interval' => 2592000, // 30 days * 24 hours * 60 minutes * 60 seconds
        'display' => __('Every 30 days')
    );

    // Add a yearly schedule
    $schedules['yearly'] = array(
        'interval' => 31536000, // 365 days * 24 hours * 60 minutes * 60 seconds
        'display' => __('Every Year')
    );

    return $schedules;
}
add_filter('cron_schedules', 'fpw_add_cron_schedules'); 
