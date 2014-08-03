<?php 
/*
Plugin Name: Online Registration
Description: Online Registration for Events
Version: 1.0
Author: Shivyog Bangalore Team
*/

require_once "db_functions.php";

add_action('admin_menu', 'online_registration_plugin');
function online_registration_plugin() {
    add_menu_page('Online Registration', 'Online Registration', 'edit_posts', 'online_registration', 'online_registration_display');
}
function online_registration_display() {
	wp_enqueue_style('online_registration_style', WP_PLUGIN_URL . '/online_registration/css/online_registration.css', false, '201312092330');
    echo '<h2>Online Registration Admin page</h2>';
	echo '<p>This plugin helps in Online Registration for an Event.</p>';
	echo '<h3>Roles:</h3>
<p>There are two type of Registered Users (kiosk users) (Admin and Contributor roles)</p>
<ol>
<li>Contributor can only Edit, and Approve Registrations</li>
<li>Admin can Edit and Approve Registrations, Create Users (Admin/Contributors), Manage Events, Subevents, Seat Categories, etc.</li></ol>
<h3>SubEvent Type:</h3>
<p>There are two type of SubEvents (Free and Paid). Payment is applicable only for Paid SubEvents</p>
<h3>Registration Flow:</h3>
<ol>
<li>Participants will fill the Registration Form on the site.</li>
<li>On successful registration, a Registration Number is generated, and prompted to the participant.</li>
<li>The Participant will mention this Registration Number behind the DD along with his name and phone number, and send it to nearest Registration Centre.</li>
<li>The kiosk user at the Registration Centre, logs into the Admin Panel, searches for Registration Number/Phone number/DD Number, and verifies the details and approves the registration.</li>
<li>A Search form has been provided for the participant and the kiosk user for searching by Registration Number/Phone number/DD Number.</li>
</ol>
<p>Note: Participants need not be registered users of the site.</p>
<h3>Technicalities:</h3>
<ol>
<li>The entire module is a combination of this Plugin, and 2 templates in the Theme.</li>
<li>Two pages (Online Registration, and Search Registration) based on these two templates need to be published.</li>
<li>The Admin Panel of this module works directly via the plugin.</li>
</ol>
<p>Note: In-case, the same is required in another theme, then the two templates (template-online-registration.php, and template-search-registration.php) needs to be copied, and modified to fit the theme\'s styles.</p>';
}

class SY_Registrations {
	private $ajaxLink;
	function __construct() {
        add_action('admin_menu', array($this, 'registrations_menu'));
		$this->ajaxLink = site_url() . "/wp-admin/admin-ajax.php";
    }

    function registrations_menu() {
        add_submenu_page(
			'online_registration'
			, 'Registrations' // For page title
			, 'Registrations' // For text to be used as menu
			, 'edit_posts' //capability(users) Admin, Editor, Author, Contributor
			, 'registrations' //Slug
			, array($this, 'registrations_page') // Function
        );
		add_submenu_page(
			'online_registration'
			, 'Reports' // For page title
			, 'Reports' // For text to be used as menu
			, 'manage_options' //capability(users) Admin
			, 'reports' //Slug
			, array($this, 'reports_page') // Function
        );
    }

	function registrations_page() {
		wp_enqueue_style('online_registration_style', WP_PLUGIN_URL . '/online_registration/css/online_registration.css', false, '201312092330');
		wp_enqueue_style('jquery-ui_style', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', false);
		wp_enqueue_script('online_registration_js', WP_PLUGIN_URL . '/online_registration/js/online_registration.js', false, '201312092330');
		wp_enqueue_script('jquery-ui_js', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', false);

		echo '<div class="wrap"><h2>Registrations <a href="'.site_url().'/online-registration/" class="add-new-h2 new-reg-btn" target="_blank">Add New</a></h2></div>';
		echo get_overlay();
		echo '<input type="hidden" value="'.$this->ajaxLink.'" id="ajaxLink"/>';
		echo '<input type="hidden" id="admin_page" value="registrations">';
		echo '<div class="online_reg_form">';
		echo $this->search_box();
		
		/* Pagination settings */
		$targetpage = './admin.php?page=registrations';
		$adjacents = 3; // How many adjacent pages should be shown on each side?
		$limit = 20;  //how many items to show per page
		$reg_params = array();
		$reg_condition = '';
		if(current_user_can("manage_options")) {
			$reg_params['reg_centre'] = 0;
		} elseif(current_user_can("edit_posts")) {
			$reg_params['reg_centre'] = get_current_user_id();
			$reg_condition = ' WHERE reg_centre = '.$reg_params['reg_centre'];
		}
        $total_records = get_total_records('sy_event_participation', 'id',$reg_condition);
        $pagination_array = show_pagination($targetpage, $total_records, $limit, $adjacents);
        $from = $pagination_array['start'];
		echo $pagination_array['pagination'];
		$reg_params['limit'] = $from.','.$limit;
		$registrations = get_registrations($reg_params);

		echo $this->display_registrations($registrations);
		echo '</div>';
    }
	
	function search_box($search_type = 'participant') {
		$search_box = '<div class="dd-details">
							<p class="search-box">';
		if(current_user_can("manage_options")){
			if($search_type == 'donor')
				$reg_centres = get_kiosk_users('author');
			else
				$reg_centres = get_kiosk_users();
			$search_box .= '<select id="reg_centre" name="reg_centre" title="Registration Centre">
								<option value="0" >All Centres</option>';
			foreach($reg_centres as $centre) {
				$search_box .= '<option value="'.$centre->ID.'" >'.$centre->display_name.'</option>';
			}
			$search_box .= '</select>';
		} else {
			$search_box .= '<input type="hidden" name="reg_centre" id="reg_centre" value="'.get_current_user_id().'"/>';
		}
		$input_box_type = 'hidden';
		if($search_type == 'donor'){
			$input_box_type = 'text';
		}
		$search_box .= '<select name="card_status" id="card_status" title="Card Status">
							<option value="-1">All Status</option>
							<option value="0">Not Issued</option>
							<option value="1">Issued</option>
						</select>
						<input type="text" name="first_name" id="first_name" placeholder="First Name" title="First Name"/>
						<input type="text" name="last_name" id="last_name" placeholder="Last Name" title="Last Name"/>
						<input type="text" name="reg_no" id="reg_no" placeholder="Registration No." title="Registration No."/>
						<input type="text" name="contact_no" id="contact_no" placeholder="Contact No."  title="Contact No."/>
						<input type="'.$input_box_type.'" name="dd_number" id="dd_number" placeholder="DD No."  title="DD No."/>
						<input type="submit" name="" id="search-submit" class="button js_search_submit" data-search_type="'.$search_type.'" value="Search">
						</p>
					</div>
					<table class="wp-list-table widefat fixed tags" id="search_results">
					</table>';
		return $search_box;
	}
	
	function display_registrations($registrations) {
		//$spl_editable = array(1506, 1507); // Editable
		$html_content = '';
		if(is_array($registrations) && !empty($registrations)){
			$html_content = '<div id="all-registrations">
			<table class="wp-list-table widefat fixed tags">
			<tr><th>Reg No</th><th>Registered Date</th><th>Name</th><th>Contact No</th><th>Card</th><th>Reg-Centre</th><th>Details</th></tr>';
			foreach($registrations as $registered) {
				/*switch($registered->pay_status){
					case 1: $status_text = 'Approved'; break;
					default: $status_text = 'InProcess'; break;
				}
				if($registered->event_type == 'free') $status_text = 'NA';*/
				switch($registered->card_issued){
					case 1: $card_text = '<span class="js_disable link-alt" id="card_'.$registered->id.'" data-id="'.$registered->id.'" data-action="0" data-type="card" title="Change Status" >Issued</span>'; break;
					case 2: $card_text = '<span title="Card Returned" >Returned</span>'; break;
					default: $card_text = '<span class="js_disable link-alt" id="card_'.$registered->id.'" data-id="'.$registered->id.'" data-action="1" data-type="card" title="Change Status" >Not-Issued</span>'; break;
				}
				// in_array($registered->reg_number, $spl_editable)
				if(current_user_can('manage_options')) {
					$view_link = '<a href="'.site_url().'/online-registration/?edit='.$registered->id.'" target="_blank">Edit</a>';
				} else {
					$view_link = '<a href="'.site_url().'/online-registration/?view='.$registered->id.'" target="_blank">View</a>';
				}
				$reg_centre = get_table_field('display_name','wp_users', $registered->reg_centre);
				$html_content .= '<tr><td>'.$registered->reg_number.'</td><td>'.$registered->registered_date.'</td><td>'.stripslashes($registered->first_name).' '.stripslashes($registered->last_name).'</td><td>'.$registered->contact_no.'</td><td>'.$card_text.'</td><td>'.$reg_centre.'</td><td>'.$view_link.'</td></tr>';
			}
			$html_content .= '</table></div>';
		}
		return $html_content;
	}
	
	function reports_page() {
		wp_enqueue_style('online_registration_style', WP_PLUGIN_URL . '/online_registration/css/online_registration.css', false, '201312092330');
		wp_enqueue_style('jquery-ui_style', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', false);
		wp_enqueue_script('online_registration_js', WP_PLUGIN_URL . '/online_registration/js/online_registration.js', false, '201312092330');
		wp_enqueue_script('jquery-ui_js', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', false);

		echo '<div class="wrap"><h2>Reports</h2></div>';
		echo '<input type="hidden" value="'.$this->ajaxLink.'" id="ajaxLink"/>';
		echo '<input type="hidden" id="admin_page" value="reports">';
		$event_detail = get_event(5);
		$reg_centres = get_kiosk_users();
		echo '<div class="online_reg_form">
				<form action="'.WP_PLUGIN_URL.'/online_registration/export_excel.php" method="post" >
				<table class="wp-list-table widefat fixed tags">
					<tr>
						<td>Event: </td>
						<td>
							<select id="js_report_event" name="event_id" >';
							foreach($event_detail as $event) {
								echo '<option value="'.$event->id.'">'.$event->name.'</option>';
							}
				 	echo '</select>
						</td>
					</tr>
					<tr>
						<td>Subevent: </td>
						<td>
							<select id="js_report_subevent" name="subevent_id" >
							</select>
						</td>
					</tr>
					<tr>
						<td>Reg Centre: </td>
						<td>
							<select id="js_report_regcentre" name="regcentre_id" >
								<option value="0">All Centres</option>';
							foreach($reg_centres as $centre) {
								echo '<option value="'.$centre->ID.'">'.$centre->display_name.'</option>';
							}
					echo '</select>
						</td>
					</tr>
					<tr>
						<td>Report Type: </td>
						<td>
							<select id="js_report_reporttype" name="report_type">
								<option value="details">Registration Details</option>
								<option value="summary">Registration Summary</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>From Date: </td>
						<td>
							<input type="text" class="sy_date" id="js_report_date_from" name="from_date" />
						</td>
					</tr>
					<tr>
						<td>To Date: </td>
						<td>
							<input type="text" class="sy_date" id="js_report_date_to" name="to_date" />
						</td>
					</tr>
					<tr>
						<td colspan="2"><button type="submit" >Export</button></td>
					</tr>
				</table>
				</form>
			</div>';
    }
}

class SY_Events {
	private $ajaxLink;
    function __construct() {
        add_action('admin_menu', array($this, 'events_menu'));
		$this->ajaxLink = site_url() . "/wp-admin/admin-ajax.php";
    }

    function events_menu() {
        add_submenu_page(
			'online_registration'
			, 'Events' // For page title
			, 'Events' // For text to be used as menu
			, 'manage_options' //capability(users) Admin
			, 'events' //Slug
			, array($this, 'events_page') // Function
        );
		add_submenu_page(
			'online_registration'
			, 'Sub-Events' // For page title
			, 'Sub-Events' // For text to be used as menu
			, 'manage_options' //capability(users) Admin
			, 'subevents' //Slug
			, array($this, 'subevents_page') // Function
        );
		add_submenu_page(
			'online_registration'
			, 'Seating Category' // For page title
			, 'Seating Category' // For text to be used as menu
			, 'manage_options' //capability(users) Admin
			, 'seats' //Slug
			, array($this, 'seats_page') // Function
        );
    }

    function events_page() {
		wp_enqueue_style('online_registration_style', WP_PLUGIN_URL . '/online_registration/css/online_registration.css', false, '201312092330');
		wp_enqueue_style('jquery-ui_style', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', false);
		wp_enqueue_script('online_registration_js', WP_PLUGIN_URL . '/online_registration/js/online_registration.js', false, '201312092330');
		wp_enqueue_script('jquery-ui_js', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', false);

		echo '<div class="wrap"><h2>Events</h2></div>';
		echo '<input type="hidden" value="'.$this->ajaxLink.'" id="ajaxLink"/>';
		echo '<input type="hidden" id="admin_page" value="events">';
		echo '<div class="event_manage_wrap">';
		echo $this->add_an_event();

		/* Pagination settings */
		$targetpage = './admin.php?page=events';
		$adjacents = 3; // How many adjacent pages should be shown on each side?
		$limit = 20;  //how many items to show per page

        $total_records = get_total_records('sy_event');
        $pagination_array = show_pagination($targetpage, $total_records, $limit, $adjacents);
        $from = $pagination_array['start'];
		echo $pagination_array['pagination'];
		$get_limit = $from.','.$limit;

        $events = get_event(5, $get_limit);
		echo $this->display_events($events);
		echo '</div>';
    }

	function add_an_event() {
		$new_event_content = '<fieldset class="new_link" id="new_link">
									<legend>Add Event</legend>
									<form name="add_event" id="add_event" action="" method="post" >
										<input type="text" placeholder="Event Name" title="Event Name" value="" name="new_event" id="new_event">
										<input type="text" placeholder="Location" title="Location" value="" name="new_location" id="new_location">
										<input type="text" placeholder="Event Code" title="Event Code like BLR13" value="" name="new_event_slug" id="new_event_slug">
										<input type="text" class="sy_date" placeholder="Start Date" title="Start Date" value="" name="new_start_date" id="new_start_date">
										<input type="text" class="sy_date" placeholder="End Date" title="End Date" value="" name="new_end_date" id="new_end_date">
										<input type="hidden" name="new_event_id" id="new_event_id" value="0" >
										<button type="submit" id="save_new_data" class="js_add_data" data-type="event" data-action="add" >Save</button>
								  	</form>
							</fieldset>
							<div class="clearfix"></div>';
		return $new_event_content;
	}

	function display_events($events) {
		foreach($events as $event) {
			if($event->status)
				$btn_disable = '<button class="js_disable" id="disable_'.$event->id.'" data-type="event" data-id="'.$event->id.'" data-action="0">Enable</button>';
			else
				$btn_disable = '<button class="js_disable" id="disable_'.$event->id.'" data-type="event" data-id="'.$event->id.'" data-action="1">Disable</button>';
			$event_list .= '<tr id="row_event_'.$event->id.'">
								<td width="6%">'.$event->id.'</td>
								<td id="row_name_'.$event->id.'">'.$event->name.'</td>
								<td id="row_location_'.$event->id.'">'.$event->location.'</td>
								<td id="row_nickname_'.$event->id.'">'.$event->nickname.'</td>
								<td width="10%" id="row_start_date_'.$event->id.'">'.$event->start_date.'</td>
								<td width="10%" id="row_end_date_'.$event->id.'">'.$event->end_date.'</td>
								<td>
									<button class="js_edit" id="edit_'.$event->id.'" data-type="event" data-id="'.$event->id.'">Edit</button>
									'.$btn_disable.'
									<button class="js_delete" id="delete_'.$event->id.'" data-type="event" data-id="'.$event->id.'">Delete</button>
								</td>
							</tr>';
		}
		$event_content = '<table class="wp-list-table widefat fixed tags">
			<thead>
				<tr><th width="6%">ID</th><th>Event</th><th>Location</th><th>Event Code</th><th width="10%">Start Date</th><th width="10%">End Date</th><th>Action</th></tr>
			</thead>
			<tbody>
			'.$event_list.'
			</tbody>
		</table>';
		return $event_content;
	}

	// Subevents
	function subevents_page() {
		wp_enqueue_style('online_registration_style', WP_PLUGIN_URL . '/online_registration/css/online_registration.css', false, '201312092330');
		wp_enqueue_style('jquery-ui_style', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', false);
		wp_enqueue_script('online_registration_js', WP_PLUGIN_URL . '/online_registration/js/online_registration.js', false, '201312092330');
		wp_enqueue_script('jquery-ui_js', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', false);
		wp_enqueue_script('jquery-ui-timepicker_js', WP_PLUGIN_URL . '/online_registration/js/jquery-ui-timepicker-addon.js', false);
		echo '<div class="wrap"><h2>Sub-Events</h2></div>';
		echo '<input type="hidden" value="'.$this->ajaxLink.'" id="ajaxLink"/>';
		echo '<input type="hidden" id="admin_page" value="subevents">';
		echo '<div class="event_manage_wrap">';
		$events = get_event(0);
		echo $this->add_sub_event($events);

		/* Pagination settings */
		$targetpage = './admin.php?page=subevents';
		$adjacents = 3; // How many adjacent pages should be shown on each side?
		$limit = 20;  //how many items to show per page

        $total_records = get_total_records('sy_subevent');
        $pagination_array = show_pagination($targetpage, $total_records, $limit, $adjacents);
        $from = $pagination_array['start'];
		echo $pagination_array['pagination'];
		$get_limit = $from.','.$limit;

        $subevents = get_subevent(0, 5, $get_limit);
		echo $this->display_subevents($subevents);
		echo '</div>';
    }

	function add_sub_event($events) {
		$event_selector = '<select placeholder="Event Name" id="new_event_name" name="new_event_name">';
		foreach($events as $event){
			$event_selector .= '<option value="'.$event->id.'" id="event_'.$event->id.'">'.$event->name.'</option>';
		}
		$event_selector .= '</select>';
		$new_event_content = '<fieldset class="new_link" id="new_link">
									<legend>Add Sub-Event</legend>
									<form name="add_subevent" id="add_subevent" action="" method="post" >
										<input type="text" placeholder="SubEvent Name" title="SubEvent Name" value="" name="new_subevent" id="new_subevent">
										<input type="text" placeholder="SubEvent Code" title="SubEvent Code like SV1" value="" name="new_subevent_slug" id="new_subevent_slug">
										'.$event_selector.'
										<input type="text" placeholder="DD Favour" title="DD Favour" value="" name="new_dd_favor" id="new_dd_favor">
										<input type="text" class="sy_date" placeholder="Start Date" title="Start Date" value="" name="new_start_date" id="new_start_date">
										<input type="text" class="sy_date" placeholder="End Date" title="End Date" value="" name="new_end_date" id="new_end_date">
										<input type="text" class="sy_time" placeholder="Start Time" title="Start Time" value="" name="new_start_time" id="new_start_time">
										<input type="text" class="sy_time" placeholder="End Time" title="End Time" value="" name="new_end_time" id="new_end_time">
										<select id="new_subevent_type" name="new_subevent_type">
											<option value="paid">Paid</option>
											<option value="free">Free</option>
										</select>
										<input type="hidden" name="new_subevent_id" id="new_subevent_id" value="0" >
										<button type="submit" id="save_new_data" class="js_add_data" data-type="subevent" data-action="add" >Save</button>
								  	</form>
							</fieldset>
							<div class="clearfix"></div>';
		return $new_event_content;
	}

	function display_subevents($subevents) {
		foreach($subevents as $subevent) {
			$event_name = get_table_field('name','sy_event',$subevent->event_id);
			if($subevent->status)
				$btn_disable = '<button class="js_disable" id="disable_'.$subevent->id.'" data-type="subevent" data-id="'.$subevent->id.'" data-action="0">Enable</button>';
			else
				$btn_disable = '<button class="js_disable" id="disable_'.$subevent->id.'" data-type="subevent" data-id="'.$subevent->id.'" data-action="1">Disable</button>';
			$subevent_list .= '<tr id="row_subevent_'.$subevent->id.'">
								<td width="6%">'.$subevent->id.'</td>
								<td id="row_name_'.$subevent->id.'">'.$subevent->name.'</td>
								<td id="row_nickname_'.$subevent->id.'">'.$subevent->nickname.'</td>
								<td id="row_event_name_'.$subevent->id.'" data-eventid="'.$subevent->event_id.'">'.$event_name.'</td>
								<td id="row_dd_favor_'.$subevent->id.'">'.$subevent->dd_favor.'</td>
								<td width="10%" id="row_start_date_'.$subevent->id.'">'.$subevent->start_date.'</td>
								<td width="10%" id="row_end_date_'.$subevent->id.'">'.$subevent->end_date.'</td>
								<td width="10%" id="row_start_time_'.$subevent->id.'">'.$subevent->start_time.'</td>
								<td width="10%" id="row_end_time_'.$subevent->id.'">'.$subevent->end_time.'</td>
								<td width="6%" id="row_subevent_type_'.$subevent->id.'" data-subevent_type="'.$subevent->event_type.'">'.ucfirst($subevent->event_type).'</td>
								<td>
									<button class="js_edit" id="edit_'.$subevent->id.'" data-type="subevent" data-id="'.$subevent->id.'">Edit</button>
									'.$btn_disable.'
									<button class="js_delete" id="delete_'.$subevent->id.'" data-type="subevent" data-id="'.$subevent->id.'">Delete</button>
								</td>
							</tr>';
		}
		$subevent_content = '<table class="wp-list-table widefat fixed tags">
			<thead>
				<tr><th width="6%">ID</th><th>SubEvent</th><th>SubEvent Code</th><th>Parent Event</th><th>DD Favour</th><th width="10%">Start Date</th><th width="10%">End Date</th><th width="10%">Start Time</th><th width="10%">End Time</th><th width="6%">Event type</th><th>Action</th></tr>
			</thead>
			<tbody>
			'.$subevent_list.'
			</tbody>
		</table>';
		return $subevent_content;
	}

	// Seat Category
	function seats_page() {
		wp_enqueue_style('online_registration_style', WP_PLUGIN_URL . '/online_registration/css/online_registration.css', false, '201312092330');
		wp_enqueue_style('jquery-ui_style', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', false);
		wp_enqueue_script('online_registration_js', WP_PLUGIN_URL . '/online_registration/js/online_registration.js', false, '201312092330');
		wp_enqueue_script('jquery-ui_js', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', false);
		echo '<div class="wrap"><h2>Seating Category</h2></div>';
		echo '<input type="hidden" value="'.$this->ajaxLink.'" id="ajaxLink"/>';
		echo '<input type="hidden" id="admin_page" value="seats">';
		echo '<div class="event_manage_wrap">';
		$subevents = get_subevent(0,0);
		echo $this->add_a_seat($subevents);

		/* Pagination settings */
		$targetpage = './admin.php?page=seats';
		$adjacents = 3; // How many adjacent pages should be shown on each side?
		$limit = 20;  //how many items to show per page

        $total_records = get_total_records('sy_seat_category');
        $pagination_array = show_pagination($targetpage, $total_records, $limit, $adjacents);
        $from = $pagination_array['start'];
		echo $pagination_array['pagination'];
		$get_limit = $from.','.$limit;

        $seats = get_seat_category(0, 0, $get_limit);
		echo $this->display_seats($seats);
		echo '</div>';
    }
	
	function add_a_seat($subevents) {
		$subevent_selector = '<select placeholder="SubEvent Name" id="new_subevent_name" name="new_subevent_name">';
		foreach($subevents as $subevent){
			$subevent_selector .= '<option value="'.$subevent->id.'" id="subevent_'.$subevent->id.'">'.$subevent->name.'</option>';
		}
		$subevent_selector .= '</select>';
		$new_event_content = '<fieldset class="new_link" id="new_link">
									<legend>Add Seat Category</legend>
									<form name="add_seat" id="add_seat" action="" method="post" >
										<input type="text" placeholder="Seat Category Name" title="Seat Category Name" value="" name="new_seat" id="new_seat">
										<input type="text" placeholder="Description" title="Description" value="" name="new_description" id="new_description">
										<input type="text" placeholder="Amount" title="Amount" value="" name="new_amount" id="new_amount">
										'.$subevent_selector.'
										<input type="hidden" name="new_seat_id" id="new_seat_id" value="0" >
										<button type="submit" id="save_new_data" class="js_add_data" data-type="seat" data-action="add" >Save</button>
								  	</form>
							</fieldset>
							<div class="clearfix"></div>';
		return $new_event_content;
	}

	function display_seats($seats) {
		foreach($seats as $seat) {
			$subevent_name = get_table_field('name','sy_subevent',$seat->subevent_id);
			$seat_list .= '<tr id="row_seat_'.$seat->id.'">
								<td width="6%">'.$seat->id.'</td>
								<td id="row_name_'.$seat->id.'">'.$seat->name.'</td>
								<td id="row_description_'.$seat->id.'">'.$seat->description.'</td>
								<td id="row_amount_'.$seat->id.'">'.$seat->amount.'</td>
								<td id="row_subevent_name_'.$seat->id.'" data-subeventid="'.$seat->subevent_id.'">'.$subevent_name.'</td>
								<td>
									<button class="js_edit" id="edit_'.$seat->id.'" data-type="seat" data-id="'.$seat->id.'">Edit</button>
									<button class="js_delete" id="delete_'.$seat->id.'" data-type="seat" data-id="'.$seat->id.'">Delete</button>
								</td>
							</tr>';
		}
		$seat_content = '<table class="wp-list-table widefat fixed tags">
			<thead>
				<tr><th width="6%">ID</th><th>Seat Category</th><th>Description</th><th>Amount</th><th>Parent SubEvent</th><th>Action</th></tr>
			</thead>
			<tbody>
			'.$seat_list.'
			</tbody>
		</table>';
		return $seat_content;
	}
}

function save_registration_form() {
	$formdata = $_POST['formdata'];
	$event_nickname =  $_POST['event_nickname'];
	$subevent_nickname =  $_POST['subevent_nickname'];
	$sy_form_type = $_POST['sy_form_type'];
	$content = array();
	foreach($formdata as $data) {
		$content[$data['name']] = $data['value'];
	}
	$ep_id = 0;
	if(!empty($content)) {
		$subevent_type = $content['subevent_type'];
		$part_msg = '';
		$approver = get_current_user_id();
		if($content['p_id']) {
			// Update
			if($sy_form_type == 'donor'){
				update_transaction($content, $approver);
				update_donor($content, $approver);
				if($content['seat_number'])
					$part_msg = ' & Invitee Pass No is <span class="reg_step_sml">'.$content['seat_number'].'</span>';
			} else {
				update_participant($content);
				if($subevent_type == 'paid'){
					update_transaction($content, $approver);
				}
				update_event_participation($content['p_id'], $content, $approver);
			}
			$reg_no = $content['reg_number'];
		} else {
			// Insert
			if($sy_form_type == 'donor'){
				$t_id = 0; $card_issuer = 0;
				if($content['card_issued']){
					$card_issuer = $approver;
				}
				$t_id = save_transaction($content, $approver);
				$p_id = save_donor($content, $t_id, $card_issuer);
				$reg_no = $content['reg_number'];
				if($content['seat_number'])
					$part_msg = ' & Invitee Pass No is <span class="reg_step_sml">'.$content['seat_number'].'</span>';
			} else {
				$already_registered = check_registration($content['subevent_id'],$content['first_name'],$content['contact_no'],$content['contact_type']);
				if(!$already_registered) {
					$p_id = save_participant($content);
					$t_id = 0; $card_issuer = 0;
					if($content['card_issued']){
						$card_issuer = $approver;
					}
					if($subevent_type == 'paid'){
						$t_id = save_transaction($content, $approver);
						$part_msg = 'Send Completed Registration Forms along with Proof of Payment 
Either by Post to : SHIVYOG COLOMBO, C/O A.S.BALRAJ, 2, PALM GROVE, COLOMBO 3, SRI LANKA
Or E-Mail to : shivyogcolombo@gmail.com
Mention your Registration No on the form.';
					} else {
						$part_msg = 'Please note the same on form & card';
					}
					$ep_id = save_event_participation($p_id, $t_id, $content, $card_issuer);
					if($subevent_type == 'paid'){
						$reg_no = set_reg_number($ep_id, $event_nickname.'-'.$subevent_nickname.'-'.$ep_id);
					} elseif($subevent_type == 'free'){
						$reg_no = set_reg_number($ep_id, $ep_id+1000);
					}
				} else {
					die('Duplicate Entry! Entry already exists. Please check!!!');
				}
			}
		}
		$message = 'Registration No is <span class="reg_step_sml">'.$reg_no.'</span>. '.$part_msg;
	}
	die($message);
}
add_action('wp_ajax_nopriv_save_registration_form', 'save_registration_form');
add_action('wp_ajax_save_registration_form', 'save_registration_form');

function search_reg_details() {
	$reg_params = array();
	$reg_params['f_name'] = trim($_POST['f_name']);
	$reg_params['l_name'] = trim($_POST['l_name']);
	$reg_params['reg_no'] = trim($_POST['reg_no']);
	$reg_params['contact_no'] = trim($_POST['contact_no']);
	$reg_params['dd_number'] = trim($_POST['dd_number']);
	$reg_params['reg_centre'] = trim($_POST['reg_centre']);
	$reg_params['card_status'] = trim($_POST['card_status']);
	$search_type = trim($_POST['search_type']);
	if($search_type == 'donor'){
		$result = get_search_donor($reg_params);
		$pass_name = '<th>Invitee Pass No</th>';
		$card_name = 'Invitee Pass';
	} else {
		$result = get_registrations($reg_params);
		$pass_name = '';
		$card_name = 'Card';
		//$spl_editable = array(1506, 1507); // Editable
	}
	$admin_page = $_POST['admin_page'];
	$html_content = '';
	if(is_array($result) && !empty($result)){
		$html_content = '<div class="reg_step_sml">Search Results</div><table>
		<tr><th>Reg No</th>'.$pass_name.'<th>Registered Date</th><th>Name</th><th>Contact No.</th><th>'.$card_name.'</th><th>Reg-Centre</th><th>Details</th></tr>';
		foreach($result as $entry) {
			switch($entry->card_issued){
				case 1: $card_text = '<span class="js_disable link-alt" id="card_'.$entry->id.'" data-id="'.$entry->id.'" data-action="0" data-type="card" title="Change Status" >Issued</span>'; break;
				case 2: $card_text = '<span title="Card Returned" >Returned</span>'; break;
				default: $card_text = '<span class="js_disable link-alt" id="card_'.$entry->id.'" data-id="'.$entry->id.'" data-action="1" data-type="card" title="Change Status" >Not-Issued</span>'; break;
			}
			if($search_type == 'donor') {
				if(current_user_can('manage_options')) {
					$view_link = '<a href="'.site_url().'/donor-registration/?edit='.$entry->id.'" target="_blank">Edit</a>';
				} else {
					$view_link = '<a href="'.site_url().'/donor-registration/?view='.$entry->id.'" target="_blank">View</a>';
				}
				$pass_no = '<td>'.$entry->seat_number.'</td>';
			} else {
				//  in_array($entry->reg_number, $spl_editable)
				if(current_user_can('manage_options')) {
					$view_link = '<a href="'.site_url().'/online-registration/?edit='.$entry->id.'" target="_blank">Edit</a>';
				} else {
					$view_link = '<a href="'.site_url().'/online-registration/?view='.$entry->id.'" target="_blank">View</a>';
				}
				$pass_no = '';
			}
			$reg_centre = get_table_field('display_name','wp_users', $entry->reg_centre);
			$html_content .= '<tr><td>'.$entry->reg_number.'</td>'.$pass_no.'<td>'.$entry->registered_date.'</td><td>'.stripslashes($entry->first_name).' '.stripslashes($entry->last_name).'</td><td>'.$entry->contact_no.'</td><td>'.$card_text.'</td><td>'.$reg_centre.'</td><td>'.$view_link.'</td></tr>';
		}
		$html_content .= '</table>';
	}
	die($html_content);
}
add_action('wp_ajax_nopriv_search_reg_details', 'search_reg_details');
add_action('wp_ajax_search_reg_details', 'search_reg_details');

function update_status(){
	$act_type = $_POST['act_type'];
	$id = $_POST['id'];
	$value = $_POST['value'];
	switch ($act_type) {
		case 'event':$table = 'sy_event';break;
		case 'subevent':$table = 'sy_subevent';break;
		case 'seat':$table = 'sy_seat_category';break;
		case 'card':$table = 'sy_event_participation';break;
		case 'donorcard':$table = 'sy_donors';break;
	}
	if($act_type == 'card' || $act_type == 'donorcard'){
		change_status($table, $value, $id, 'card_issued');
		change_status($table, get_current_user_id(), $id, 'card_issuer');
	} else {
		change_status($table, $value, $id);
	}
	die($value);
}
add_action('wp_ajax_update_status', 'update_status');

function delete_ajax(){
	$act_type = $_POST['act_type'];
	$id = $_POST['id'];
	switch ($act_type) {
		case 'event':delete_event($id);break;
		case 'subevent':delete_subevent($id);break;
		case 'seat':delete_seat_category($id);break;
	}	
	die($id);
}
add_action('wp_ajax_delete_ajax', 'delete_ajax');

function save_ajax(){
	$act_type = $_POST['act_type'];
	$act = $_POST['act'];
	$formdata = $_POST['formdata'];
	$f_data_array = array();
	foreach($formdata as $f_data)
		$f_data_array[$f_data['name']] = $f_data['value'];
	switch ($act_type){
		case 'event':
			if($act == 'add')
				save_event($f_data_array['new_event'], $f_data_array['new_location'], $f_data_array['new_event_slug'], $f_data_array['new_start_date'], $f_data_array['new_end_date']);
			else if($act == 'edit')
				update_event($f_data_array['new_event_id'], $f_data_array['new_event'], $f_data_array['new_location'], $f_data_array['new_event_slug'], $f_data_array['new_start_date'], $f_data_array['new_end_date']);
			break;
		case 'subevent':
			if($act == 'add')
				save_subevent($f_data_array);
			else if($act == 'edit')
				update_subevent($f_data_array);
			break;
		case 'seat':
			if($act == 'add')
				save_seat_category($f_data_array['new_seat'], $f_data_array['new_amount'], $f_data_array['new_subevent_name'], $f_data_array['new_description']);
			else if($act == 'edit')
				update_seat_category($f_data_array['new_seat_id'], $f_data_array['new_seat'], $f_data_array['new_amount'], $f_data_array['new_description'], $f_data_array['new_subevent_name']);
			break;
	}
	die($id);
}
add_action('wp_ajax_save_ajax', 'save_ajax');

function get_subevents_ajax(){
	$event_id = $_POST['event_id'];
	$subevents = get_subevent($event_id, 5);
	$subevent_list = '';
	foreach($subevents as $subevent) {
		$subevent_list .= '<option value="'.$subevent->id.'">'.$subevent->name.'</option>';
	}
	die($subevent_list);
}
add_action('wp_ajax_get_subevents_ajax', 'get_subevents_ajax');

function get_registration_autofill(){
	$reg_params = array();
	$reg_params['reg_no'] = trim($_POST['reg_no']);
	$reg_params['q_type'] = 'fill';
	$result = get_registrations($reg_params);
	if(!empty($result)){
		$fill_rs = json_encode($result[0]);
	} else {
		$fill_rs = 'na';
	}
	die($fill_rs);
}
add_action('wp_ajax_get_registration_autofill', 'get_registration_autofill');

function show_pagination($targetpage, $total_pages, $limit, $adjacents) {
    $page = $_GET['pg'];
    if ($page)
        $start = ($page - 1) * $limit;    //first item to display on this page
    else
        $start = 0;     //if no page var is given, set start to 0

    /* Setup page vars for display. */
    if ($page == 0)
        $page = 1;     //if no page var is given, default to 1.
    $prev = $page - 1;       //previous page is page - 1
    $next = $page + 1;       //next page is page + 1
    $lastpage = ceil($total_pages / $limit);  //lastpage is = total pages / items per page, rounded up.
    $lpm1 = $lastpage - 1;      //last page minus 1

    $pagination = "";
    if ($lastpage > 1) {
        $pagination .= "<div class=\"pagination\" ><ul>";
//previous button
        if ($page > 1)
            $pagination.= "<li  class=\"previous\"><a href=\"$targetpage&pg=1\" class=\" bgIcons pager-first\">&laquo;</a></li>
                    <li class=\"prev\"><a href=\"$targetpage&pg=$prev\" class=\"bgIcons pager-prev\">&lsaquo;</a></li>";
        else
            $pagination.= "<li  class=\"previous disable\"><a class=\" bgIcons pager-first\">&laquo;</a></li>
                    <li class=\"prev disable\"><a href=\"#\" class=\"bgIcons pager-prev\">&lsaquo;</a></li>";

//pages
        if ($lastpage < 7 + ($adjacents * 2)) { //not enough pages to bother breaking it up
            for ($counter = 1; $counter <= $lastpage; $counter++) {
                if ($counter == $page)
                    $pagination.= "<li class=\"current\">$counter</li>";
                else
                    $pagination.= "<li><a href=\"$targetpage&pg=$counter\">$counter</a></li>";
            }
        }
        elseif ($lastpage > 5 + ($adjacents * 2)) { //enough pages to hide some
//close to beginning; only hide later pages
            if ($page < 1 + ($adjacents * 2)) {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                    if ($counter == $page)
                        $pagination.= "<li class=\"current\">$counter</li>";
                    else
                        $pagination.= "<li><a href=\"$targetpage&pg=$counter\">$counter</a></li>";
                }
                $pagination.= "<li class=\"dots\">...</li>";
                $pagination.= "<li><a href=\"$targetpage&pg=$lpm1\">$lpm1</a><li>";
                $pagination.= "<li><a href=\"$targetpage&pg=$lastpage\">$lastpage</a></li>";
            }
//in middle; hide some front and some back
            elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                $pagination.= "<li><a href=\"$targetpage&pg=1\">1</a></li>";
                $pagination.= "<li><a href=\"$targetpage&pg=2\">2</a></li>";
                $pagination.= "<li class=\"dots\">...</li>";
                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                    if ($counter == $page)
                        $pagination.= "<li class=\"current\">$counter</li>";
                    else
                        $pagination.= "<li><a href=\"$targetpage&pg=$counter\">$counter</a></li>";
                }
                $pagination.= "<li class=\"dots\">...</li>";
                $pagination.= "<li><a href=\"$targetpage&pg=$lpm1\">$lpm1</a></li>";
                $pagination.= "<li><a href=\"$targetpage&pg=$lastpage\">$lastpage</a><li>";
            }
//close to end; only hide early pages
            else {
                $pagination.= "<li><a href=\"$targetpage&pg=1\">1</a></li>";
                $pagination.= "<li><a href=\"$targetpage&pg=2\">2</a></li>";
                $pagination.= "<li class=\"dots\">...</li>";
                for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                    if ($counter == $page)
                        $pagination.= "<li class=\"current\">$counter</li>";
                    else
                        $pagination.= "<li><a href=\"$targetpage&pg=$counter\">$counter</a><li>";
                }
            }
        }

//next button
        if ($page < $counter - 1)
            $pagination.= "<li class=\"previous\"><a href=\"$targetpage&pg=$next\" class=\" bgIcons pager-next\">&rsaquo;</a></li>
                    <li class=\"next\"><a href=\"$targetpage&pg=$lastpage\" class=\"bgIcons pager-last\">&raquo;</a><li>";
        else
            $pagination.= "<li class=\"bgIcons pager-last\"><a href=\"#\" class=\"bgIcons pager-next\">&rsaquo;</a></span>
                    <li class=\"next disable\"><a href=\"#\" class=\"bgIcons pager-last\">&raquo;</a></li>";
        $pagination.= "<ul></div>\n";
    }
    $result_array['start'] = $start;
    $result_array['pagination'] = $pagination;
    return $result_array;
}

function get_overlay($reload = 0) {
	$html_content = '<div class="wiki-lightbox-cover"  style="display: none"></div>
			<img class="wiki-spinner" width="200px" style="display: none" src="'.get_template_directory_uri().'/images/loading.gif" />
            <div class="wiki-lightbox" style="display: none">
                <div class="top-head">
                    <div class="top-head-msg">Information</div>
                </div>                
                <div class="wiki-display">
                    <div class="wiki-white">
                        <p></p>
						<button type="button" id="close" class="close" data-reload="'.$reload.'">OK</button>
                    </div>
                </div>
            </div>';
	return $html_content;
}

function sy_send_email($to, $subject, $content) {
	$content .= "\r\n" . "\r\n" . 'Thanks,' . "\r\n" . 'Shivyog Bangalore Seva Team';
	$headers = 'From: "Shivyog Bangalore Seva Team" <blrshivir@gmail.com>';
	wp_mail($to, $subject, $content, $headers);
}

new SY_Events();
new SY_Registrations();