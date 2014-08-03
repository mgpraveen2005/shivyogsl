<?php 
function change_status($table, $value, $id, $col = 'status'){
	global $wpdb;
	$query = 'UPDATE '.$table.' SET '.$col.' = %d WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $value, $id));
}
function get_table_field($field, $table, $id, $condition = ' WHERE id = ', $order = '') {
	global $wpdb;
	$query = 'SELECT '.$field.' FROM '.$table.$condition.$id.$order;
	$rs = $wpdb->get_var($query);
	return $rs;
}
function get_kiosk_users($role = 'contributor') {
	global $wpdb;
	$query = 'SELECT u.ID, u.display_name FROM wp_users u INNER JOIN wp_usermeta m ON u.ID =  m.user_id 
AND m.meta_key = "wp_capabilities" AND m.meta_value RLIKE "'.$role.'"';
	$rs = $wpdb->get_results($query);
	return $rs;
}
function get_total_records($table_name, $field_name = 'id', $condition = '') {
	global $wpdb;
	$query = 'SELECT COUNT('.$field_name.') AS total FROM ' . $table_name . $condition;
    $total_records = $wpdb->get_var($query);
	return $total_records;
}

function save_event($name, $location, $nickname, $start_date, $end_date) {
	global $wpdb;
	$query = 'INSERT INTO sy_event (name, location, nickname, start_date, end_date) VALUES (%s, %s, %s, %s, %s )';
	$wpdb->query($wpdb->prepare($query, $name, $location, $nickname, $start_date, $end_date));
	$event_id = $wpdb->insert_id;
	return $event_id;
}
function delete_event($event_id) {
	global $wpdb;
	$query = 'DELETE FROM sy_event WHERE id = '.$event_id;
	$wpdb->query($query);
	return 1;
}
function update_event($event_id, $name, $location, $nickname, $start_date, $end_date) {
	global $wpdb;
	$query = 'UPDATE sy_event SET name = %s, location = %s, nickname = %s, start_date = %s, end_date = %s WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $name, $location, $nickname, $start_date, $end_date, $event_id));
	return 1;
}
function get_event($status = 5, $limit = 20) {
	global $wpdb;
	$where = '';
	if($status != 5)
		$where = ' WHERE status = '.$status;
	$query = 'SELECT id, name, location, nickname, start_date, end_date, status FROM sy_event '.$where.' ORDER BY id DESC LIMIT '.$limit;
	$rs = $wpdb->get_results($query);
	return $rs;
}

function save_subevent($f_data_array) {
	global $wpdb;
	$query = 'INSERT INTO sy_subevent (event_id, name, nickname, start_date, end_date, start_time, end_time, dd_favor, event_type ) VALUES (%d, %s, %s, %s, %s, %s, %s, %s, %s)';
	$wpdb->query($wpdb->prepare($query, $f_data_array['new_event_name'], $f_data_array['new_subevent'], $f_data_array['new_subevent_slug'], $f_data_array['new_start_date'], $f_data_array['new_end_date'], $f_data_array['new_start_time'], $f_data_array['new_end_time'], stripslashes_deep($f_data_array['new_dd_favor']), $f_data_array['new_subevent_type']));
	$subevent_id = $wpdb->insert_id;
	return $subevent_id;
}
function delete_subevent($subevent_id) {
	global $wpdb;
	$query = 'DELETE FROM sy_subevent WHERE id = '.$subevent_id;
	$wpdb->query($query);
	return 1;
}
function update_subevent($f_data_array) {
	global $wpdb;
	$query = 'UPDATE sy_subevent SET event_id = %d, name = %s, nickname = %s, start_date = %s, end_date = %s, start_time = %s, end_time = %s, dd_favor = %s, event_type = %s WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $f_data_array['new_event_name'], $f_data_array['new_subevent'], $f_data_array['new_subevent_slug'], $f_data_array['new_start_date'], $f_data_array['new_end_date'], $f_data_array['new_start_time'], $f_data_array['new_end_time'], stripslashes_deep($f_data_array['new_dd_favor']), $f_data_array['new_subevent_type'], $f_data_array['new_subevent_id']));
	return 1;
}
function get_subevent($event_id = 0, $status = 5, $limit = 20) {
	global $wpdb;
	$where = '';
	if($event_id)
		$where .= ' AND event_id = '.$event_id;
	if($status != 5)
		$where .= ' AND status = '.$status;

	$query = 'SELECT id, event_id, name, nickname, start_date, end_date, start_time, end_time, status, dd_favor, event_type FROM sy_subevent WHERE 1=1 '.$where.' ORDER BY id DESC LIMIT '.$limit;
	$rs = $wpdb->get_results($query);
	return $rs;
}

function save_seat_category($name, $amount, $subevent_id, $description) {
	global $wpdb;
	$query = 'INSERT INTO sy_seat_category (name, amount, subevent_id, description ) VALUES (%s, %s, %d, %s)';
	$wpdb->query($wpdb->prepare($query, $name, $amount, $subevent_id, $description));
	$seat_id = $wpdb->insert_id;
	return $seat_id;
}
function delete_seat_category($seat_category_id) {
	global $wpdb;
	$query = 'DELETE FROM sy_seat_category WHERE id = '.$seat_category_id;
	$wpdb->query($query);
	return 1;
}
function update_seat_category($seat_category_id, $name, $amount, $description, $subevent_id) {
	global $wpdb;
	$query = 'UPDATE sy_seat_category SET name = %s, amount = %s, description = %s, subevent_id = %d WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $name, $amount, $description, $subevent_id, $seat_category_id));
	return 1;
}
function get_seat_category($subevent_id = 0, $seat_id = 0, $limit = 20) {
	global $wpdb;
	$row_flag = 0; $condition = '';
	if($subevent_id) {
		$condition = ' AND subevent_id = '.$subevent_id;
	} elseif($seat_id) {
		$condition = ' AND id = '.$seat_id;
		$row_flag = 1;
	}
	$query = 'SELECT id, name, amount, description, subevent_id FROM sy_seat_category WHERE 1=1'.$condition.' ORDER BY id DESC LIMIT '.$limit;
	if($row_flag)
		$rs = $wpdb->get_row($query);
	else
		$rs = $wpdb->get_results($query);
	return $rs;
}

function save_transaction($content, $approver) {
	global $wpdb;
	$query = 'INSERT INTO sy_transactions (dd_amount, dd_bank, dd_number, dd_date, payment_type, pay_status, cash_amount, remarks, approver ) VALUES (%f, %s, %s, %s, %s, %d, %f, %s, %d)';
	$wpdb->query($wpdb->prepare($query, $content['dd_amount'], $content['dd_bank'], $content['dd_number'], $content['dd_date'], $content['payment_type'], $content['pay_status'], $content['cash_amount'], $content['remarks'], $approver));
	$transaction_id = $wpdb->insert_id;
	return $transaction_id;
}
function update_transaction($content, $approver) {
	global $wpdb;
	$query = 'UPDATE sy_transactions SET dd_amount = %f, dd_bank = %s, dd_number = %s, dd_date = %s, payment_type = %s, pay_status = %d, cash_amount = %f, remarks = %s, approver = %d WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $content['dd_amount'], $content['dd_bank'], $content['dd_number'], $content['dd_date'], $content['payment_type'], $content['pay_status'], $content['cash_amount'], $content['remarks'], $approver, $content['transaction_id']));
	return 1;
}
function delete_transaction($transaction_id) {
	global $wpdb;
	$query = 'DELETE FROM sy_transactions WHERE id = '.$transaction_id;
	$wpdb->query($query);
	return 1;
}
function get_transaction($transaction_id) {
	global $wpdb;
	$query = 'SELECT dd_amount, dd_bank, dd_number, dd_date, payment_type, pay_status, cash_amount, remarks, approver FROM sy_transactions WHERE id = '.$transaction_id;
	$rs = $wpdb->get_row($query);
	return $rs;
}

function save_event_participation($participant_id, $transaction_id, $content, $card_issuer ) {
	global $wpdb;
	$query = 'INSERT INTO sy_event_participation (participant_id, subevent_id, seat_category_id, transaction_id, seat_number, card_issued, card_issuer, reg_centre, registered_date ) VALUES (%d, %d, %d, %d, %s, %d, %d, %d, NOW())';
	$success = $wpdb->query($wpdb->prepare($query, $participant_id, $content['subevent_id'], $content['seat_category'], $transaction_id, $content['seat_number'], $content['card_issued'], $card_issuer, $content['reg_centre']));
	if($success){
		$event_participation_id = $wpdb->insert_id;
		return $event_participation_id;
	} else {
		return 0;
	}
}
function update_event_participation($event_participation_id, $content, $card_issuer ) {
	global $wpdb;
	$query = 'UPDATE sy_event_participation SET subevent_id = %d, seat_category_id = %d, seat_number = %s, card_issued = %d, card_issuer = %d, reg_centre = %d WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $content['subevent_id'], $content['seat_category'], $content['seat_number'], $content['card_issued'], $card_issuer, $content['reg_centre'], $event_participation_id));
	return 1;
}
function delete_event_participation($event_participation_id) {
	global $wpdb;
	$query = 'DELETE FROM sy_event_participation WHERE id = '.$event_participation_id;
	$wpdb->query($query);
	return 1;
}
function get_event_participation($participant_id = 0) {
	global $wpdb;
	$where = ''; $row_flag = 0;
	if($participant_id) {
		$where = ' WHERE id = '.$participant_id;
		$row_flag = 1;
	}
	$query = 'SELECT id, reg_number, participant_id, subevent_id, seat_category_id, transaction_id, seat_number, card_issued, card_issuer, reg_centre, registered_date FROM sy_event_participation'.$where;
	if($row_flag)
		$rs = $wpdb->get_row($query);
	else
		$rs = $wpdb->get_results($query);
	return $rs;
}

function save_participant($participant) {
	global $wpdb;
	$query = 'INSERT INTO sy_participants (first_name, last_name, title, gender, dob, address, locality, city, state, country, pin_code, contact_no, contact_type, email, pan_number, info_source) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';
	$success = $wpdb->query($wpdb->prepare($query, $participant['first_name'], $participant['last_name'], $participant['title'], $participant['gender'], $participant['dob'], $participant['address'], $participant['locality'], $participant['city'], $participant['state'], $participant['country'], $participant['pin_code'], $participant['contact_no'], $participant['contact_type'], $participant['email'], $participant['pan_number'], $participant['info_source']));
	if($success) {
		$participant_id = $wpdb->insert_id;
		return $participant_id;
	} else {
		return 0;
	}
}
function update_participant($participant) {
	global $wpdb;
	$query = 'UPDATE sy_participants SET first_name = %s, last_name = %s, title = %s, gender = %s, dob = %s, address = %s, locality = %s, city = %s, state = %s, country = %s, pin_code = %s, contact_no = %s, contact_type = %s, email = %s, pan_number = %s, info_source = %s WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $participant['first_name'], $participant['last_name'], $participant['title'], $participant['gender'], $participant['dob'], $participant['address'], $participant['locality'], $participant['city'], $participant['state'], $participant['country'], $participant['pin_code'], $participant['contact_no'], $participant['contact_type'], $participant['email'], $participant['pan_number'], $participant['info_source'], $participant['participant_id']));
	return 1;
}
function delete_participant($participant_id) {
	global $wpdb;
	$query = 'DELETE FROM sy_participants WHERE id = '.$participant_id;
	$wpdb->query($query);
	return 1;
}
function get_participant($participant_id) {
	global $wpdb;
	$query = 'SELECT * FROM sy_participants WHERE id = '.$participant_id;
	$rs = $wpdb->get_row($query);
	return $rs;
}

function save_donor($donor, $t_id, $card_issuer) {
	global $wpdb;
	$query = 'INSERT INTO sy_donors (first_name, last_name, title, gender, dob, address, locality, city, state, country, pin_code, contact_no, contact_type, email, pan_number, info_source, photo_id_proof, address_proof, reg_centre, transaction_id, subevent_id, registered_date, card_issued, card_issuer, seat_number, reg_number) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %d, NOW(), %d, %d, %s, %d)';
	$success = $wpdb->query($wpdb->prepare($query, $donor['first_name'], $donor['last_name'], $donor['title'], $donor['gender'], $donor['dob'], $donor['address'], $donor['locality'], $donor['city'], $donor['state'], $donor['country'], $donor['pin_code'], $donor['contact_no'], $donor['contact_type'], $donor['email'], $donor['pan_number'], $donor['info_source'], $donor['photo_id_proof'], $donor['address_proof'], $donor['reg_centre'], $t_id, $donor['subevent_id'], $donor['card_issued'], $card_issuer, $donor['seat_number'], $donor['reg_number']));
	if($success) {
		$donor_id = $wpdb->insert_id;
		return $donor_id;
	} else {
		return 0;
	}
}
function update_donor($donor, $card_issuer) {
	global $wpdb;
	$query = 'UPDATE sy_donors SET first_name = %s, last_name = %s, title = %s, gender = %s, dob = %s, address = %s, locality = %s, city = %s, state = %s, country = %s, pin_code = %s, contact_no = %s, contact_type = %s, email = %s, pan_number = %s, info_source = %s, photo_id_proof = %s, address_proof = %s, reg_centre = %d, card_issued = %d, card_issuer = %d, seat_number = %s, reg_number = %d  WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $donor['first_name'], $donor['last_name'], $donor['title'], $donor['gender'], $donor['dob'], $donor['address'], $donor['locality'], $donor['city'], $donor['state'], $donor['country'], $donor['pin_code'], $donor['contact_no'], $donor['contact_type'], $donor['email'], $donor['pan_number'], $donor['info_source'], $donor['photo_id_proof'], $donor['address_proof'], $donor['reg_centre'], $donor['card_issued'], $card_issuer, $donor['seat_number'], $donor['reg_number'], $donor['p_id']));
	return 1;
}
function delete_donor($donor_id) {
	global $wpdb;
	$query = 'DELETE FROM sy_donors WHERE id = '.$donor_id;
	$wpdb->query($query);
	return 1;
}
function get_donor($donor) {
	if($donor['reg_centre'])
		$where = ' WHERE reg_centre = '.$donor['reg_centre'];
	if($donor['limit'])
		$limit = '  ORDER BY id DESC LIMIT '.$donor['limit'];
	if($donor['p_id'])
		$where = ' WHERE id = '.$donor['p_id'];
	global $wpdb;
	$query = 'SELECT * FROM sy_donors '.$where.$limit;
	$rs = $wpdb->get_results($query);
	return $rs;
}
function get_search_donor($params) {
	$where = ''; $reg_where = '';
	if($params['f_name'])
		$where = ' WHERE d.first_name LIKE "%'.$params['f_name'].'%"';
	elseif($params['l_name'])
		$where = ' WHERE d.last_name LIKE "%'.$params['l_name'].'%"';
	elseif($params['reg_no'])
		$where = ' WHERE d.id = "'.$params['reg_no'].'"';
	elseif($params['contact_no'])
		$where = ' WHERE d.contact_no LIKE "%'.$params['contact_no'].'%"';
	elseif($params['dd_number'])
		$where = ' WHERE t.dd_number = "'.$params['dd_number'].'"';

	if($params['reg_centre'] && $params['card_status'] > -1){
		$reg_where = ' d.reg_centre = '.$params['reg_centre'].' AND d.card_issued = '.$params['card_status'];
	} else {
		if($params['reg_centre'])
			$reg_where = ' d.reg_centre = '.$params['reg_centre'];
		if($params['card_status'] > -1)
			$reg_where = ' d.card_issued = '.$params['card_status'];
	}

	if($where) {
		if($reg_where) {
			$where .= ' AND '.$reg_where;
		}
	} elseif($reg_where) {
		$where = ' WHERE '.$reg_where;
	}

	$limit_clause = '';
	if($params['limit'])
		$limit_clause = ' LIMIT '.$params['limit'];

	global $wpdb;
	$query = 'SELECT d.id, d.reg_number, d.seat_number, d.first_name, d.last_name, d.contact_no, t.pay_status, d.card_issued, d.reg_centre, d.registered_date FROM sy_donors d INNER JOIN sy_transactions t ON d.transaction_id = t.id '.$where.$limit_clause;
	$rs = $wpdb->get_results($query);
	return $rs;
}

function set_reg_number($ep_id, $reg_number) {
	global $wpdb;
	$query = 'UPDATE sy_event_participation SET reg_number = %s WHERE id = %d';
	$wpdb->query($wpdb->prepare($query, $reg_number, $ep_id));
	return $reg_number;
}
function check_registration($subevent_id, $first_name, $contact_no, $contact_type) {
	global $wpdb;
	$query = 'SELECT COUNT(ep.id) FROM sy_event_participation ep INNER JOIN sy_participants p ON ep.participant_id = p.id AND p.first_name = "'.$first_name.'" AND p.contact_no = "'.$contact_no.'" AND p.contact_type = "'.$contact_type.'" AND ep.subevent_id = '.$subevent_id;
	$rs = $wpdb->get_var($query);
	return $rs;
}
function get_registrations($params){
	$where = ''; $reg_where = '';
	if($params['f_name'])
		$where = ' WHERE p.first_name LIKE "%'.$params['f_name'].'%"';
	elseif($params['l_name'])
		$where = ' WHERE p.last_name LIKE "%'.$params['l_name'].'%"';
	elseif($params['reg_no'])
		$where = ' WHERE ep.reg_number = "'.$params['reg_no'].'"';
	elseif($params['contact_no'])
		$where = ' WHERE p.contact_no LIKE "%'.$params['contact_no'].'%"';
	elseif($params['dd_number'])
		$where = ' WHERE t.dd_number = "'.$params['dd_number'].'"';

	if($params['reg_centre'] && $params['card_status'] > -1){
		$reg_where = ' ep.reg_centre = '.$params['reg_centre'].' AND ep.card_issued = '.$params['card_status'];
	} else {
		if($params['reg_centre'])
			$reg_where = ' ep.reg_centre = '.$params['reg_centre'];
		if($params['card_status'] > -1)
			$reg_where = ' ep.card_issued = '.$params['card_status'];
	}

	if($where) {
		if($reg_where) {
			$where .= ' AND '.$reg_where;
		}
	} elseif($reg_where) {
		$where = ' WHERE '.$reg_where;
	}

	$limit_clause = '';
	if($params['limit'])
		$limit_clause = ' LIMIT '.$params['limit'];

	global $wpdb;
	if($params['q_type'] == 'fill') {
		$query = 'SELECT p.* FROM sy_event_participation ep INNER JOIN sy_participants p ON ep.participant_id = p.id '.$where.' ORDER BY ep.id DESC '.$limit_clause;
	} else {
		$query = 'SELECT ep.id, p.first_name, p.last_name, p.contact_no, se.name as subevent, se.start_date, se.end_date, se.event_type, t.payment_type, t.pay_status, ep.card_issued, ep.reg_centre, ep.registered_date, ep.reg_number FROM sy_event_participation ep INNER JOIN sy_participants p ON ep.participant_id = p.id LEFT JOIN sy_transactions t ON ep.transaction_id = t.id INNER JOIN sy_subevent se on ep.subevent_id = se.id '.$where.' ORDER BY ep.id DESC '.$limit_clause;
	}
	$rs = $wpdb->get_results($query);
	return $rs;
}