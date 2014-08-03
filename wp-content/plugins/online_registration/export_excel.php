<?php
require_once( '../../../wp-load.php' );

$event_id = $_POST['event_id'];
$subevent_id = $_POST['subevent_id'];
$regcentre_id = $_POST['regcentre_id'];
$report_type = $_POST['report_type'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$condition = '';

$date_now = date('Ymd');
$filename = "OnlineRegistration_".$report_type."_".$date_now.".xls";
header('Content-Type: application/vnd.ms-excel');
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename=' . $filename);
$outstream = fopen("php://output", 'w');
	
if($regcentre_id){
	$condition .= ' AND ep.reg_centre = '.$regcentre_id;
}
if($report_type == 'summary') {
	$query = 'SELECT ep.reg_centre, COUNT(ep.id) AS total_reg FROM sy_event_participation ep WHERE ep.subevent_id = '.$subevent_id.$condition.' GROUP BY ep.reg_centre';
	global $wpdb;
	$rs = $wpdb->get_results($query);
	$export_data = '<table border="1">
					<tr style="font-weight:500;">
					<th style="background-color:#F5C89D;">Registration Center</th>
					<th style="background-color:#F5C89D;">Total Registrations</th>
					<th style="background-color:#F5C89D;">Card Issued</th>
					<th style="background-color:#F5C89D;">Card Not Issued</th>
					<th style="background-color:#F5C89D;">Last Registration Time</th>
					</tr>';
	fwrite($outstream, $export_data);
	foreach($rs as $row){
		$reg_center = get_table_field('display_name', 'wp_users', $row->reg_centre);
		$card_issued = get_total_records('sy_event_participation', 'card_issued', $condition = ' WHERE reg_centre = '.$row->reg_centre.' AND card_issued = 1');
		$card_not_issued = $row->total_reg - $card_issued;
		$last_reg_time = get_table_field('registered_date', 'sy_event_participation', $row->reg_centre, ' WHERE reg_centre = ', ' ORDER BY registered_date DESC LIMIT 1');
		$export_data = '<tr>
						<td>'.stripslashes($reg_center).'</td>
						<td>'.$row->total_reg.'</td>
						<td>'.$card_issued.'</td>
						<td>'.$card_not_issued.'</td>
						<td>'.$last_reg_time.'</td>
						</tr>';
		fwrite($outstream, $export_data);				
	}
	$export_data = '</table>';
	fwrite($outstream, $export_data);
} else {
	if($report_type == 'donordetails'){
		if($from_date && $to_date){
			if($from_date == $to_date)
				$condition .= ' AND d.registered_date LIKE "'.$from_date.'%"';
			else
				$condition .= ' AND ( d.registered_date BETWEEN "'.$from_date.'" AND "'.$to_date.'") OR d.registered_date LIKE "'.$to_date.'%"';
		}
		$query = 'SELECT d.*, t.dd_amount, t.dd_bank, t.dd_number, t.dd_date FROM sy_donors d INNER JOIN sy_transactions t ON d.transaction_id = t.id WHERE d.subevent_id = '.$subevent_id.$condition;
	} else {
		if($from_date && $to_date){
			if($from_date == $to_date)
				$condition .= ' AND ep.registered_date LIKE "'.$from_date.'%"';
			else
				$condition .= ' AND ( ep.registered_date BETWEEN "'.$from_date.'" AND "'.$to_date.'") OR ep.registered_date LIKE "'.$to_date.'%"';
		}
		$query = 'SELECT ep.reg_number, ep.seat_number, ep.card_issued, ep.card_issuer, ep.reg_centre, ep.registered_date, sc.name AS seat_category, sc.amount, p.* FROM sy_event_participation ep INNER JOIN sy_subevent se ON ep.subevent_id = se.id INNER JOIN sy_seat_category sc ON ep.seat_category_id = sc.id INNER JOIN sy_participants p ON ep.participant_id = p.id WHERE ep.subevent_id = '.$subevent_id.$condition;
	}
	
	global $wpdb;
	$rs = $wpdb->get_results($query);
	$export_data = '<table border="1">
					<tr style="font-weight:500;">
					<th style="background-color:#F5C89D;">ID</th>
					<th style="background-color:#F5C89D;">Registration Number</th>
					<th style="background-color:#F5C89D;">First Name</th>
					<th style="background-color:#F5C89D;">Last Name</th>
					<th style="background-color:#F5C89D;">Title</th>
					<th style="background-color:#F5C89D;">Gender</th>
					<th style="background-color:#F5C89D;">Contact No</th>
					<th style="background-color:#F5C89D;">Contact Type</th>
					<th style="background-color:#F5C89D;">Email</th>
					<th style="background-color:#F5C89D;">City</th>
					<th style="background-color:#F5C89D;">Country</th>
					<th style="background-color:#F5C89D;">Source of Information</th>
					<th style="background-color:#F5C89D;">Registration Date</th>
					<th style="background-color:#F5C89D;">Registration Centre</th>';
	if($report_type == 'donordetails'){
		$export_data .= '<th style="background-color:#F5C89D;">Invitee Pass Status</th>
						<th style="background-color:#F5C89D;">Invitee Pass No</th>
						<th style="background-color:#F5C89D;">DD Amount</th>
						<th style="background-color:#F5C89D;">DD Number</th>
						<th style="background-color:#F5C89D;">DD Date</th>
						<th style="background-color:#F5C89D;">DD Bank</th>';
	} else {
		$export_data .= '<th style="background-color:#F5C89D;">Card Status</th>';
	}
	$export_data .= '</tr>';
	fwrite($outstream, $export_data);
	foreach($rs as $row){
		$card_status = 'Not Issued';
		$card_issuedby = '';
		if($row->card_issued){
			$card_status = 'Issued';
			$card_issuedby = ' ( '.get_table_field('display_name', 'wp_users', $row->card_issuer).' )';
		}
		$export_data = '<tr>
						<td>'.$row->id.'</td>
						<td>'.$row->reg_number.'</td>
						<td>'.stripslashes($row->first_name).'</td>
						<td>'.stripslashes($row->last_name).'</td>
						<td>'.$row->title.'</td>
						<td>'.$row->gender.'</td>
						<td>'.$row->contact_no.'</td>
						<td>'.$row->contact_type.'</td>
						<td>'.$row->email.'</td>
						<td>'.$row->city.'</td>
						<td>'.$row->country.'</td>
						<td>'.$row->info_source.'</td>
						<td>'.$row->registered_date.'</td>
						<td>'.get_table_field('display_name', 'wp_users', $row->reg_centre).'</td>
						<td>'.$card_status.$card_issuedby.'</td>';
		if($report_type == 'donordetails'){
			$export_data .= '<td>'.$row->seat_number.'</td>
						<td>'.$row->dd_amount.'</td>
						<td>'.$row->dd_number.'</td>
						<td>'.$row->dd_date.'</td>
						<td>'.$row->dd_bank.'</td>';
		}
		$export_data .= '</tr>';
		fwrite($outstream, $export_data);
	}
	$export_data = '</table>';
	fwrite($outstream, $export_data);
}

