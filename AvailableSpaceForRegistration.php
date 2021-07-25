<?php
/*
*Plugin Name: Available space For Registration
*Version:     1.1.0
* Author:     Ibrar Ayoub
*Description: This plugin display the available space for the registration in the event list display in the admin panel.
*/
require 'plugin-update-checker-master/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/manager-wiseTech/AvailableSpaceForRegistration/',
	__FILE__,
	'event-expresso-available-space-for-registration-addon'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication('your-token-here');


 if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
         $url = "https://";   
    else  
         $url = "http://";   
    // Append the host(domain name, ip) to the URL.   
    $url.= $_SERVER['HTTP_HOST'];   
    
    // Append the requested resource location to the URL   
    $url.= $_SERVER['REQUEST_URI'];    
      
     
//This code adds an additional column 'Regs sold/total'.
function tw_ee_add_registrations_sold_total_column( $columns, $screen ) {
	$custom_attendees_column = '<span class="dashicons dashicons-groups ee-icon-color-ee-green ee-icon-size-20">'
                                 . '<span class="screen-reader-text">'
                                 . esc_html__('Approved Registrations', 'event_espresso')
                                 . '</span>'
                                 . '</span>';
    // Add our own custom attendees column.
	$columns = EEH_Array::insert_into_array(
        $columns,
        array( 'custom_attendees_column' => $custom_attendees_column ), 
        'attendees'
    );
    // Remove the default approved attendee column.
	unset($columns['attendees']);
    return $columns;
}
if (strpos($url,'action=category_list') == false){
        add_filter( 'FHEE_manage_toplevel_page_espresso_events_columns', 'tw_ee_add_registrations_sold_total_column', 10, 2 );        
    }


function tw_ee_add_registrations_sold_total_column_data( $item, $screen ) {
    $attendees_query_args = array(
        'action'   => 'default',
        'event_id' => $item->ID(),
    );
    $attendees_link = EE_Admin_Page::add_query_args_and_nonce($attendees_query_args, REG_ADMIN_URL);
    $registered_attendees = EEM_Registration::instance()->get_event_registration_count($item->ID());
    $total_spaces = $item->total_available_spaces() == EE_INF ? '&infin;' : $item->total_available_spaces();
    echo EE_Registry::instance()->CAP->current_user_can(
        'ee_read_event',
        'espresso_registrations_view_registration',
        $item->ID()
    )
           && EE_Registry::instance()->CAP->current_user_can(
               'ee_read_registrations',
               'espresso_registrations_view_registration'
           )
        ? '<a href="' . $attendees_link . '">' . $registered_attendees . '/' . $total_spaces . '</a>'
        : $registered_attendees . '/' . $total_spaces;
}
if (strpos($url,'action=category_list') == false){
    add_action( 'AHEE__EE_Admin_List_Table__column_custom_attendees_column__toplevel_page_espresso_events', 'tw_ee_add_registrations_sold_total_column_data', 10, 2 );
}


add_action( 'admin_enqueue_scripts', 'tw_ee_custom_events_columns', 20 );
function tw_ee_custom_events_columns() {
    $css = 
        '#Extend_Events_Admin_List_Table-table-frm .column-custom_attendees_column {
            width: 10%;
        }';
    wp_add_inline_style('events-admin-css', $css);
}

/* Stop Adding Functions */
