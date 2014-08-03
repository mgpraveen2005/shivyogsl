jQuery(document).ready(function() {
    if (jQuery("#admin_page").length > 0) {
        if (jQuery("#admin_page").val() == "subevents") {
            jQuery(".sy_time").timepicker({
                timeFormat: "HH:mm:ss"
            });
            jQuery("#new_link").on("submit", "#add_subevent", function(b) {
                b.preventDefault();
                var c = jQuery("#ajaxLink").val();
                jQuery.ajax({
                    type: "POST",
                    url: c,
                    data: {
                        action: "save_ajax",
                        act_type: "subevent",
                        act: jQuery("#save_new_data").data("action"),
                        formdata: jQuery(this).serializeArray()
                    },
                    success: function(a) {
                        location.reload();
                    }
                });
            });
        } else {
            if (jQuery("#admin_page").val() == "events") {
                jQuery("#new_link").on("submit", "#add_event", function(b) {
                    b.preventDefault();
                    var c = jQuery("#ajaxLink").val();
                    jQuery.ajax({
                        type: "POST",
                        url: c,
                        data: {
                            action: "save_ajax",
                            act_type: "event",
                            act: jQuery("#save_new_data").data("action"),
                            formdata: jQuery(this).serializeArray()
                        },
                        success: function(a) {
                            location.reload();
                        }
                    });
                });
            } else {
                if (jQuery("#admin_page").val() == "seats") {
                    jQuery("#new_link").on("submit", "#add_seat", function(b) {
                        b.preventDefault();
                        var c = jQuery("#ajaxLink").val();
                        jQuery.ajax({
                            type: "POST",
                            url: c,
                            data: {
                                action: "save_ajax",
                                act_type: "seat",
                                act: jQuery("#save_new_data").data("action"),
                                formdata: jQuery(this).serializeArray()
                            },
                            success: function(a) {
                                location.reload();
                            }
                        });
                    });
                } else {
                    if (jQuery("#admin_page").val() == "reports") {
                        jQuery(".online_reg_form").on("change", "#js_report_event", function(c) {
                            c.preventDefault();
                            var d = jQuery("#ajaxLink").val();
                            var b = jQuery(this).val();
                            jQuery.ajax({
                                type: "POST",
                                url: d,
                                data: {
                                    action: "get_subevents_ajax",
                                    event_id: b
                                },
                                success: function(a) {
                                    if (a != "") {
                                        jQuery("#js_report_subevent").html(a);
                                    }
                                }
                            });
                        });
                        jQuery("#js_report_event").trigger("change");
                    }
                }
            }
        }
        jQuery(".js_edit").on("click", function() {
            var c = jQuery(this).data("type");
            var e = jQuery(this).data("id");
            var d = "add_" + c;
            var b = 1;
            if (c == "event") {
                jQuery("#new_event").val(jQuery("#row_name_" + e).text());
                jQuery("#new_location").val(jQuery("#row_location_" + e).text());
                jQuery("#new_event_slug").val(jQuery("#row_nickname_" + e).text());
                jQuery("#new_event_id").val(e);
                jQuery("#new_link legend").text("Edit Event");
            } else {
                if (c == "subevent") {
                    jQuery("#new_subevent").val(jQuery("#row_name_" + e).text());
                    jQuery("#new_subevent_slug").val(jQuery("#row_nickname_" + e).text());
                    jQuery("#new_subevent_id").val(e);
                    jQuery("#new_link legend").text("Edit Sub-Event");
                    jQuery("#new_dd_favor").val(jQuery("#row_dd_favor_" + e).text());
                    jQuery("#new_start_time").val(jQuery("#row_start_time_" + e).text());
                    jQuery("#new_end_time").val(jQuery("#row_end_time_" + e).text());
                    jQuery("#new_event_name").val(jQuery("#row_event_name_" + e).data("eventid"));
                    jQuery("#new_subevent_type").val(jQuery("#row_subevent_type_" + e).data("subevent_type"));
                } else {
                    if (c == "seat") {
                        b = 0;
                        jQuery("#new_seat_id").val(e);
                        jQuery("#new_seat").val(jQuery("#row_name_" + e).text());
                        jQuery("#new_description").val(jQuery("#row_description_" + e).text());
                        jQuery("#new_amount").val(jQuery("#row_amount_" + e).text());
                        jQuery("#new_subevent_name").val(jQuery("#row_subevent_name_" + e).data("subeventid"));
                        jQuery("#new_link legend").text("Edit Seat Category");
                    }
                }
            }
            if (b == 1) {
                jQuery("#new_start_date").val(jQuery("#row_start_date_" + e).text());
                jQuery("#new_end_date").val(jQuery("#row_end_date_" + e).text());
            }
            jQuery("#save_new_data").data("action", "edit");
        });
        jQuery(document).on("click", ".js_disable", function() {
            var b = jQuery(this).data("type");
            var f = jQuery(this).data("id");
            var c = jQuery(this).data("action");
            var e = jQuery(this).attr("id");
            var d = jQuery("#ajaxLink").val();
            jQuery.ajax({
                type: "POST",
                url: d,
                data: {
                    action: "update_status",
                    act_type: b,
                    id: f,
                    value: c
                },
                success: function(a) {
                    if (a == 1) {
                        jQuery("#" + e).data("action", 0);
                        if (b == "card" || b == "donorcard") {
                            jQuery("#" + e).html("Issued");
                        } else {
                            jQuery("#" + e).html("Enable");
                        }
                    } else {
                        jQuery("#" + e).data("action", 1);
                        if (b == "card" || b == "donorcard") {
                            jQuery("#" + e).html("Not-Issued");
                        } else {
                            jQuery("#" + e).html("Disable");
                        }
                    }
                }
            });
        });
        jQuery(".js_delete").on("click", function() {
            var b = jQuery(this).data("type");
            var e = jQuery(this).data("id");
            var d = "row_" + b + "_" + e;
            var c = jQuery("#ajaxLink").val();
            jQuery.ajax({
                type: "POST",
                url: c,
                data: {
                    action: "delete_ajax",
                    act_type: b,
                    id: e
                },
                success: function(a) {
                    jQuery("#" + d).hide();
                }
            });
        });
        jQuery(".sy_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            yearRange: "-100:+1"
        });
    } else {
        jQuery(".sy_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "yy-mm-dd",
            yearRange: "-100:+0"
        });
        if (jQuery(".seat_category").is(":checked") == false) {
            jQuery(".seat_category:first").attr("checked", "checked");
        }
    }
});
jQuery(".close").on("click", function() {
    var a = jQuery(this).data("reload");
    jQuery(".wiki-lightbox-cover").hide();
    jQuery(".wiki-lightbox").hide();
    var c = jQuery("#current_user").val();
    var b = jQuery("#p_id").val();
    if (a == 1) {
        if ((c > 0) && (b < 1)) {
            jQuery("#first_name, #last_name, #contact_no, #dob").val("");
        } else {
            location.reload(true);
        }
    } else {
        if (a == 2) {
            location.reload(true);
        } else {
            return false;
        }
    }
});
jQuery(".online_reg_form").on("click", ".js_search_submit", function(h) {
    h.preventDefault();
    var d = jQuery("#ajaxLink").val();
    var j, f, a, i, g, b;
    var c = jQuery("#first_name").val();
    var v = jQuery("#last_name").val();
    g = jQuery("#reg_centre").val();
    b = jQuery("#card_status").val();
    j = jQuery("#reg_no").val();
    f = jQuery("#contact_no").val();
    a = jQuery("#dd_number").val();
    i = jQuery("#admin_page").val();
    var u = jQuery(this).data("search_type");
    if (typeof(i) === "undefined") {
        i = 0;
    }
    var s = 1;
    if (g == 0 && b == -1) {
        s = 0;
    }
    if (j.length > 0 || f.length > 0 || a.length > 0 || s > 0 || c.length > 0 || v.length > 0) {
        jQuery.ajax({
            type: "POST",
            url: d,
            data: {
                action: "search_reg_details",
                f_name: c,
                l_name: v,
                reg_no: j,
                contact_no: f,
                dd_number: a,
                reg_centre: g,
                card_status: b,
                admin_page: i,
                search_type: u
            },
            success: function(e) {
                jQuery("#search_results").html(e);
            }
        });
    }
}).on("submit", "#reg_form", function(a) {
    a.preventDefault();
    var b = jQuery("#ajaxLink").val();
    jQuery(".wiki-lightbox-cover").show();
    jQuery(".wiki-spinner").show();
    jQuery.ajax({
        type: "POST",
        url: b,
        data: {
            action: "save_registration_form",
            formdata: jQuery(this).serializeArray(),
            event_nickname: jQuery("#event_id").data("nickname"),
            subevent_nickname: jQuery(".subevent_id").data("nickname"),
            sy_form_type: jQuery(this).data("sy_form_type")
        },
        success: function(c) {
            jQuery(".wiki-spinner").hide();
            jQuery(".wiki-white p").html(c);
            jQuery(".wiki-lightbox").show();
        },
        error: function(c) {
            jQuery(".wiki-lightbox-cover").hide();
            jQuery(".wiki-spinner").hide();
        }
    });
}).on("click", ".js_form_submit", function(c) {
    c.preventDefault();
    var b = jQuery("#subevent_type").val();
    if (b === "paid") {
        if (jQuery.trim(jQuery("#dd_amount").val()).length < 1) {
            jQuery("#dd_amount").focus();
            return false;
        }
        if (jQuery.trim(jQuery("#dd_number").val()).length < 1) {
            jQuery("#dd_number").focus();
            return false;
        }
        if (jQuery.trim(jQuery("#dd_date").val()).length < 1) {
            jQuery("#dd_date").focus();
            return false;
        }
        if (jQuery.trim(jQuery("#dd_bank").val()).length < 1) {
            jQuery("#dd_bank").focus();
            return false;
        }
    }
    var d = jQuery("input:radio[name=title]:checked").val();
    var a = jQuery("input:radio[name=gender]:checked").val();
    if ((d == "Mr." && a == "F") || (d == "Ms." && a == "M")) {
        alert("Title and Gender mismatch");
        return false;
    }
    if (jQuery.trim(jQuery("#first_name").val()).length < 1) {
        jQuery("#first_name").focus();
        return false;
    }
    if (jQuery.trim(jQuery("#contact_no").val()).length < 1) {
        jQuery("#contact_no").focus();
        return false;
    }
    if (jQuery.trim(jQuery("#city").val()).length < 1) {
        jQuery("#city").focus();
        return false;
    }
    if (jQuery.trim(jQuery("#js_country").val()).length < 1) {
        jQuery("#js_country").focus();
        return false;
    }
    if (jQuery.trim(jQuery("#dob").val()).length < 1) {
        jQuery("#dob").focus();
        return false;
    }
    if (jQuery("#term_accept").is(":checked")) {
        jQuery("#reg_form").trigger("submit");
    } else {
        return false;
    }
}).on("change", ".js_payment_type", function() {
    var payment_type = $(this).val();
    if(payment_type == 'bank transfer'){
        $('.js_lbl_dd_amt').text('Transferred Amount ');
        $('.js_lbl_dd_no').text('Transaction ID ');
        $('.js_lbl_dd_date').text('Transaction Date ');
        $('.js_lbl_dd_bank').text('Transaction Bank ');
    } else {
        $('.js_lbl_dd_amt').text('DD Amount ');
        $('.js_lbl_dd_no').text('DD Number ');
        $('.js_lbl_dd_date').text('DD Date ');
        $('.js_lbl_dd_bank').text('DD Bank ');
    }
});