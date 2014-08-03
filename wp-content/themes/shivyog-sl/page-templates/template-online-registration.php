<?php
/*
  Template Name: Online Registration
 */
?>
<?php
get_header();
$title = get_the_title();
$event_detail = get_event(0, 1);
$subevent_detail = get_subevent($event_detail[0]->id, 0);
$subevent_type = $subevent_detail[0]->event_type;
$dd_favor = $subevent_detail[0]->dd_favor;
$seating_category = get_seat_category($subevent_detail[0]->id);
$view_only = 0;
$mr_active = "checked";
$ms_active = "";
$dr_active = "";
$m_active = "checked";
$f_active = "";
if (isset($_GET['view']) || isset($_GET['edit'])) {
    if (isset($_GET['view'])) {
        $view_only = 1;
        $p_id = $_GET['view'];
    } else {
        $p_id = $_GET['edit'];
    }
    $ep = get_event_participation($p_id);
    $transaction = get_transaction($ep->transaction_id);
    $participant = get_participant($ep->participant_id);

    switch ($participant->title) {
        case "Ms.":
            $mr_active = "";
            $ms_active = "checked";
            break;
        case "Dr.":
            $mr_active = "";
            $dr_active = "checked";
            break;
    }

    if ($participant->gender == 'F') {
        $m_active = "";
        $f_active = "checked";
    }
}
$reg_centres = get_kiosk_users();
$current_user_id = get_current_user_id();
$current_user_name = get_table_field('display_name', 'wp_users', $current_user_id);

$event_st_date = date('d M', strtotime($subevent_detail[0]->start_date));
$event_end_date = date('d M', strtotime($subevent_detail[0]->end_date));
?>
<div id="primary" class="site-content">
    <div id="" role="main">
        <div class="entry-content online_reg_form reg_template">
            <?php echo get_overlay(1); ?>
            <input type="hidden" id="ajaxLink" name="ajaxLink" value="<?php echo site_url(); ?>/wp-admin/admin-ajax.php" />
            <input type="hidden" id="current_user" value="<?php echo $current_user_id; ?>" data-name="<?php echo $current_user_name; ?>" />
            <h1 class="event_title">
                <?php echo $event_detail[0]->name; ?> - Online Registration<br />
                <?php echo $subevent_detail[0]->name; ?> - <?php echo $event_st_date . ' to ' . $event_end_date; ?>
            </h1>
            <div class="reg_message">
                <p>You will be able to register one person at a time. All fields marked with <span class="star"></span> are mandatory.</p>
            </div>
            <form action="" method="post" name="reg_form" id="reg_form" data-sy_form_type="participant">
                <input type="hidden" value="<?php echo $p_id; ?>" name="p_id" id="p_id"/>
                <input type="hidden" value="<?php echo $ep->reg_number; ?>" name="reg_number" />
                <input type="hidden" value="<?php echo $participant->id; ?>" name="participant_id" />
                <input type="hidden" value="<?php echo $event_detail[0]->id; ?>" name="event_id" id="event_id" data-nickname="<?php echo $event_detail[0]->nickname; ?>"/>
                <input type="hidden" value="<?php echo $subevent_type; ?>" name="subevent_type" id="subevent_type" />
                <input type="hidden" value="<?php echo $subevent_detail[0]->id; ?>" name="subevent_id" class="subevent_id" data-nickname="<?php echo $subevent_detail[0]->nickname; ?>"/>
                <?php if ($subevent_type == 'paid') { ?>
                    <div class="reg_step_sml">Categories</div>
                    <table>
                        <?php foreach ($seating_category as $seat_category) { ?>
                            <tr><td><input type="radio" class="seat_category" name="seat_category" value="<?php echo $seat_category->id; ?>" <?php if ($ep->seat_category_id == $seat_category->id) echo "checked"; ?> /></td><td><?php echo $seat_category->name; ?></td><td><?php echo $seat_category->amount; ?></td><td><?php echo $seat_category->description; ?></td></tr>
                        <?php } ?>
                    </table>
                    <?php
                } else {
                    $seat_category = $seating_category[0];
                    ?>
                    <input type="hidden" class="seat_category" name="seat_category" value="<?php echo $seat_category->id; ?>" />
                <?php } ?>
                <?php if ($subevent_type == 'paid') { ?>
                    <div class="dd-details">
                        <div class="reg_step_sml">Payment Details</div>
                        <input type="hidden" value="<?php echo $ep->transaction_id; ?>" name="transaction_id" />
                        <table>
                            <tr><td><label>Payment Type <span class="star"></span></label></td><td>
                                    <select name="payment_type" id="payment_type" class="js_payment_type">
                                        <?php
                                        if ($transaction->payment_type) {
                                            echo '<option value="' . $transaction->payment_type . '">' . ucwords($transaction->payment_type) . '</option>';
                                        }
                                        ?>
                                        <option value="demand draft">Demand Draft</option>
                                        <option value="bank transfer">Bank Transfer</option>
                                    </select>
                                </td></tr>
                            <tr><td><label><span class="js_lbl_dd_amt">DD Amount </span><span class="star"></span></label></td><td><input type="text" name="dd_amount" id="dd_amount" value="<?php echo $transaction->dd_amount; ?>" required /></td></tr>
                            <tr><td><label><span class="js_lbl_dd_no">DD Number </span><span class="star"></span></label></td><td><input type="text" name="dd_number" id="dd_number" value="<?php echo $transaction->dd_number; ?>" required /></td></tr>
                            <tr><td><label><span class="js_lbl_dd_date">DD Date </span><span class="star"></span></label></td><td><input type="text" class="sy_date" name="dd_date" id="dd_date" value="<?php echo $transaction->dd_date; ?>" required /></td></tr>
                            <tr><td><label><span class="js_lbl_dd_bank">DD Bank </span><span class="star"></span></label></td><td><input type="text" name="dd_bank" id="dd_bank" value="<?php echo $transaction->dd_bank; ?>" required /></td></tr>
                            <?php if (current_user_can('edit_posts')) { ?>
                                <tr><td><label>Approve Payment <span class="star"></span></label></td><td><input name="pay_status" id="pay_status" type="checkbox" <?php if ($transaction->pay_status) echo "checked"; ?> value=1 /></td></tr>
                                <tr><td><label>Cash Amount (optional)</label></td><td><input type="text" name="cash_amount" id="cash_amount" value="<?php echo $transaction->cash_amount; ?>" /></td></tr>
                                <tr><td><label>Remarks (optional)</label></td><td><textarea name="remarks" id="remarks" ><?php echo $transaction->remarks; ?></textarea></td></td></tr>
                                <?php
                            } else if ($p_id) {
                                switch ($transaction->pay_status) {
                                    case 1: $status_text = 'Approved';
                                        break;
                                    default: $status_text = 'InProcess';
                                        break;
                                }
                                ?>
                                <tr><td><label>Status </label></td><td>&nbsp;&nbsp;&nbsp;<span class="reg_step_sml"><?php echo $status_text; ?></span></td></tr>
                            <?php } ?>
                        </table>
                        <div class="reg_text">DD need to be made in favour of: <span class="reg_step_sml"><?php echo $dd_favor; ?></span>.</div>
                    </div>
                <?php } ?>
                <div class="reg_step_sml">Details</div>
                <div class="flow_form">
                    <?php if ($ep->reg_number) { ?>
                        <div><div class="flow_elements"><div class="lbl_first"><label>Registration Number </label></div><div class="lbl_last">&nbsp;&nbsp;&nbsp;<span class="reg_step_sml"><?php echo $ep->reg_number; ?></span></div></div></div>
                    <?php } ?>
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Title <span class="star"></span></label></div><div class="lbl_last"><input type="radio" name="title" value="Mr." <?php echo $mr_active; ?> />Mr.&nbsp;&nbsp;&nbsp;<input type="radio" name="title" value="Ms." <?php echo $ms_active; ?> />Ms.&nbsp;&nbsp;&nbsp;<input type="radio" name="title" value="Dr." <?php echo $dr_active; ?> />Dr.</div>
                        </div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Gender <span class="star"></span></label></div><div class="lbl_last"><input type="radio" name="gender" value="M" <?php echo $m_active; ?> />Male &nbsp;&nbsp;&nbsp;<input type="radio" name="gender" value="F" <?php echo $f_active; ?> />Female</div>
                        </div>
                    </div>
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>First Name <span class="star"></span></label></div><div class="lbl_last"><input type="text" name="first_name" id="first_name" value="<?php echo $participant->first_name; ?>" required/></div>
                        </div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Last Name </label></div><div class="lbl_last"><input type="text" name="last_name" id="last_name"  value="<?php echo $participant->last_name; ?>" /></div>
                        </div>
                    </div>
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Contact No. <span class="star"></span></label></div><div class="lbl_last"><input type="text" name="contact_no" id="contact_no"  value="<?php echo $participant->contact_no; ?>" /></div>
                        </div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Contact Type. <span class="star"></span></label></div>
                            <div class="lbl_last">
                                <select name="contact_type" id="contact_type">
                                    <?php
                                    if ($participant->contact_type) {
                                        echo '<option value="' . $participant->contact_type . '">' . ucfirst($participant->contact_type) . '</option>';
                                    }
                                    ?>
                                    <option value="mobile">Mobile</option>
                                    <option value="landline">Landline</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Country <span class="star"></span></label></div><div class="lbl_last"><input type="text" name="country" id="js_country"  value="<?php echo $participant->country ? $participant->country : 'Sri Lanka'; ?>"  required/></div>
                        </div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>City <span class="star"></span></label></div><div class="lbl_last"><input type="text" name="city" id="city"  value="<?php echo $participant->city; ?>"  required/></div>
                        </div>
                    </div>
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Source of Information </label></div>
                            <div class="lbl_last">
                                <select id="info_source" name="info_source">
                                    <?php if ($participant->info_source) echo '<option value="' . $participant->info_source . '"  selected >' . $participant->info_source . '</option>'; ?>
                                    <option value="TV" >TV</option>
                                    <option value="Word-of-Mouth" >Word of Mouth</option>
                                    <option value="Poster" >Poster</option>
                                    <option value="Internet" >Internet</option>
                                    <option value="Others" >Others</option>
                                </select>
                            </div>
                        </div>
                        <div class="flow_elements">
                            <?php
                            if (current_user_can('manage_options')) {
                                ?>
                                <div class="lbl_first"><label>Card Status <span class="star"></span></label></div>
                                <div class="lbl_last">
                                    <select name="card_issued" id="card_issued" >
                                        <option value="0" <?php if ($ep->card_issued == 0) echo "selected"; ?>>Not Issued</option>
                                        <option value="1" <?php if ($ep->card_issued == 1) echo "selected"; ?>>Issued</option>
                                        <option value="2" <?php if ($ep->card_issued == 2) echo "selected"; ?>>Returned</option>
                                    </select>
                                </div>
                                <?php
                            } else if (current_user_can('edit_posts')) {
                                $card_issuer_name = '';
                                if ($ep->card_issued) {
                                    $card_issuer_name = get_table_field('display_name', 'wp_users', $ep->card_issuer);
                                    if ($card_issuer_name)
                                        $card_issuer_name = ' ( ' . $card_issuer_name . ' )';
                                }
                                ?>
                                <div class="lbl_first"><label>Card Issued <span class="star"></span></label></div>
                                <div class="lbl_last">
                                    <?php
                                    switch ($ep->card_issued) {
                                        case 1: echo '<input name="card_issued" id="card_issued" type="checkbox" checked value=1 />';
                                            break;
                                        case 2: echo '<input name="card_issued" id="card_issued" type="hidden" value=2 />&nbsp;&nbsp;&nbsp;<span class="reg_step_sml">Returned</span>';
                                            break;
                                        default: echo '<input name="card_issued" id="card_issued" type="checkbox" value=1 />';
                                            break;
                                    }
                                    echo $card_issuer_name;
                                    ?>
                                </div>
                                <?php
                            } else if ($p_id) {
                                switch ($ep->card_issued) {
                                    case 1: $card_text = 'Issued';
                                        break;
                                    case 2: $card_text = 'Returned';
                                        break;
                                    default: $card_text = 'Not-Issued';
                                        break;
                                }
                                ?>
                                <div class="lbl_first"><label>Card Status </label></div><div class="lbl_last">&nbsp;&nbsp;&nbsp;<span class="reg_step_sml"><?php echo $card_text; ?></span></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Registration Centre <span class="star"></span></label></div>
                            <div class="lbl_last">
                                <?php if (!current_user_can('manage_options') && current_user_can('edit_posts')) { ?>
                                    <input type="hidden" name="reg_centre" value="<?php echo $current_user_id; ?>" />
                                    &nbsp;&nbsp;&nbsp;<span class="reg_step_sml"><?php echo $current_user_name; ?></span>
                                <?php } else { ?>
                                    <select id="reg_centre" name="reg_centre">
                                        <?php foreach ($reg_centres as $centre) { ?>
                                            <option value="<?php echo $centre->ID; ?>" <?php if ($ep->reg_centre == $centre->ID) echo "selected"; ?> ><?php echo $centre->display_name; ?></option>
                                        <?php } ?>
                                    </select>
                                <?php } ?>
                            </div>						
                        </div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Email ID </label></div><div class="lbl_last"><input type="text" name="email" id="email"  value="<?php echo $participant->email; ?>" /></div>
                        </div>
                    </div>
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Date of Birth <span class="star"></span></label></div><div class="lbl_last"><input type="text" class="sy_date" name="dob" id="dob"  value="<?php echo $participant->dob; ?>" ></div>
                        </div>
                        <div class="flow_elements">
                            <?php if (current_user_can('edit_posts') && $subevent_type == 'paid') { ?>
                                <div class="lbl_first"><label>Seat Number (optional)</label></div><div class="lbl_last"><input type="text" name="seat_number" id="seat_number" value="<?php echo $ep->seat_number; ?>" /></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="flow_form">
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>State </label></div><div class="lbl_last"><input type="text" name="state" id="state"  value="<?php echo $participant->state; ?>" /></div>
                        </div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>ZIP </label></div><div class="lbl_last"><input type="text" name="pin_code" id="pin_code"  value="<?php echo $participant->pin_code; ?>" /></div>
                        </div>
                    </div>
                    <div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Street </label></div><div class="lbl_last"><input type="text" name="address" id="address"  value="<?php echo $participant->address; ?>" /></div>
                        </div>
                        <div class="flow_elements">
                            <div class="lbl_first"><label>Locality </label></div><div class="lbl_last"><input type="text" name="locality" id="locality"  value="<?php echo $participant->locality; ?>" /></div>
                        </div>
                    </div>
                    <div>
                        <div class="flow_elements">
                            <?php if ($subevent_type == 'paid') { ?>
                                <div class="lbl_first"><label>PAN Card No. </label></div><div class="lbl_last"><input type="text" name="pan_number" id="pan_number"   value="<?php echo $participant->pan_number; ?>" /></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="reg_step">Terms and Conditions</div>
                <div class="reg_message">
                    <div class="reg_step_sml">Declaration:</div>
                    <p>I am participating in the Shiv Yog programs at my own will. I take full responsibility for participating in this program, and its outcome whatsoever.</p><p>I will maintain the sanctity of the program and keep the proceedings of the program confidential.</p><p>I will maintain the discipline during the program and I understand that if my conduct is found to be inappropriate, I would be asked to vacate the premises and I will be refused admission in the program.</p><p>The Registration amount for the program is Non Refundable and Non Transferable.</p>
                    <p>Note : Recording the program content by any device or mode is strictly prohibited. Anyone found recording will be asked to leave the venue and his/her registration will be canceled.</p>
                    <?php if (!$view_only) { ?>
                        <p><input name="term_accept" id="term_accept" type="checkbox" <?php if (current_user_can('edit_posts')) echo "checked"; ?> required/>&nbsp;I accept the Terms and Conditions.</p>
                    <?php } ?>
                </div>
                <?php if (!$view_only) { ?>
                    <div style="text-align:center;">
                        <button type="submit" class="js_form_submit ok-btn">Submit</button>
                    </div>
                <?php } ?>
            </form>
        </div>
    </div>
</div>
<?php
get_footer(); // show footer     ?>