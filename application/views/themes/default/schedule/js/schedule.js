function remove_scheduled_str(in_str)
{
	in_str = in_str.replace(/\*/g, '');
	in_str = in_str.replace(" ( " + _scheduled_label + " )", '');
	return in_str;
}

function create_filename()
{
	if (!$('#saved_report_id option:selected').val()) {
		$('input[name=filename]').val('');
		return false;
	}
	var new_filename = $('#saved_report_id option:selected').text();
	new_filename = remove_scheduled_str(new_filename);
	new_filename += '_' + $('#period option:selected').text() + '.pdf';
	new_filename = new_filename.replace(/ /g, '_');
	if ($('input[name=filename]').val() != '' && $('input[name=filename]').val() != current_filename) {
		if (!confirm(_schedule_change_filename)) {
			return false;
		}
	}
	$('input[name=filename]').val(new_filename);
	current_filename = new_filename;
	return true;
}

$(document).ready(function() {
	$("#saved_report_id").change(function() {
		create_filename();
	});
	setup_editable();
	$("#period").change(function() {
		var sel_report = $("#saved_report_id").fieldValue();
		if (sel_report[0] != '')
			create_filename();
	});

		// delete single schedule
	$(".delete_schedule").each(function() {
		$(this).click(function() {
			if ($(this).attr('class').indexOf('avail_del') > -1) {
				_schedule_remove = 'avail';
			} else {
				if ($(this).attr('class').indexOf('sla_del') > -1) {
					_schedule_remove = 'sla';
				}
				if ($(this).attr('class').indexOf('summary_del') > -1) {
					_schedule_remove = 'summary';
				}
			}
			if (!_schedule_remove) {
				_schedule_remove = $('input[name=type]').attr('value');
			}
			schedule_delete($(this).attr('id'), _schedule_remove);
		})
	});

	$(".deleteimg").css('cursor', 'pointer');

	$("#type").change(function() {
		var report_type = $(this).fieldValue()[0];
		$.getJSON(
			_site_domain + _index_page + "/schedule/list_by_type/"+report_type,
			function(response) {
				if(response.error) {
					alert(response.error);
					return;
				}
				var saved_reports = document.getElementById("saved_report_id");
				var child;
				while(child = saved_reports.firstChild) {
					saved_reports.removeChild(child);
				}
				if(!response.result.length) {
					return;
				}
				var options = document.createDocumentFragment();
				for(var i = 0; i < response.result.length; i++) {
					var option = document.createElement("option");
					var result = response.result[i];
					option.appendChild(document.createTextNode(result.report_name));
					option.setAttribute("value", result.id);
					options.appendChild(option);
				}
				saved_reports.appendChild(options);
				create_filename();
			}
		);
	});

	$('#new_schedule_report_form').submit(function(ev) {
		ev.preventDefault();

		var rep_type_str = $('#type option:selected').val();

		var recipients = $.trim($('#recipients').fieldValue()[0]);
		if (recipients.indexOf('@') === -1) {
			alert(_reports_invalid_email);
			return false;
		}

		if(!validate_form()) {
			return false;
		}
		show_progress('progress', _wait_str);
		$.ajax({
			url: _site_domain + _index_page + '/schedule/schedule',
			type: 'POST',
			data: {
				report_id: 0,
				type: $('#type').fieldValue()[0],
				saved_report_id: $('#saved_report_id').fieldValue()[0],
				period: $('#period').fieldValue()[0],
				recipients: recipients,
				filename: $('#filename').fieldValue()[0],
				description: $('#description').fieldValue()[0],
				local_persistent_filepath: $.trim($('#local_persistent_filepath').val())
			},
			complete: function() {
				$('#progress').hide();
				// make sure we hide message about no schedules and show table headers
				$('#' + rep_type_str + '_no_result').hide();
				$('#' + rep_type_str + '_headers').show();
			},
			success: function(data) {
				if (data.error) {
					jgrowl_message(data.error, _reports_error);
					return;
				}
				str = create_new_schedule_rows(data.result.id);
				$('#' + rep_type_str + '_scheduled_reports_table').append(str);
				setup_editable();
				$('#new_schedule_report_form').clearForm();

				jgrowl_message(_reports_schedule_create_ok, _reports_success);
			},
			dataType: 'json'
		});
	});
});

function schedule_delete(id, remove_type)
{
	if (!confirm(_reports_confirm_delete_schedule)) {
		return false;
	}

	var img_src = $('#' + id + " img").attr('src');
	var in_id = id;

	$('#' + in_id + ' img').attr('src', loadimg.src);

	// clean input id from prefix (from setup template)
	if (isNaN(id)) {
		id = id.replace('delid_', '');  // from single report listing
		id = id.replace('alldel_', ''); // from all schedules list
	}

	$.ajax({
		url:_site_domain + _index_page + '/schedule/delete_schedule',
		data: {'id': id},
		success: function(data) {
			if (data.error) {
				jgrowl_message(data.error, _reports_error);
			} else {
				// item deleted
				remove_schedule(id, remove_type, data.result);
			}
			restore_delimg(in_id, img_src);
		},
		error: function(data) {
				jgrowl_message(data, _reports_error);
				restore_delimg(in_id, img_src);
		},
		type: 'POST',
		dataType: 'json'
	});
}

function send_report_now(type, sched_id, report_id)
{
	var elem = $(this);
	$(this)
		.css('background', 'url(' + loadimg.src + ') no-repeat scroll 0 0 transparent')
		.css('height', '16px')
		.css('width', '16px')
		.css('float', 'left');

	$.ajax({
		url: _site_domain + _index_page + '/schedule/send_now/' + sched_id,
		type: 'POST',
		success: function(data) {
			if (data.error) {
				if(data.error) {
					jgrowl_message(_reports_schedule_send_error + ': ' + data.error, _reports_error);
				} else {
					jgrowl_message(_reports_schedule_send_error, _reports_error);
				}
			} else {
				jgrowl_message(data.result, _reports_success);
			}
			restore_sendimg(elem);
		},
		error: function() {
			jgrowl_message(_reports_schedule_send_error, _reports_error);
			restore_sendimg(elem);
		},
		dataType: 'json'
	});
}

function restore_sendimg(id)
{
	var old_icon = _site_domain + _theme_path + "icons/16x16/send-report.png";
	id
		.css('background', 'url(' + old_icon + ') no-repeat scroll 0 0 transparent')
		.css('height', '16px')
		.css('width', '16px').css('float', 'left');

}

function restore_delimg(id, src)
{
	$('#' + id + ' img').attr('src', src);
}

function remove_schedule(id, remove_type, msg)
{
	var time = 3000;

	// remove row for deleted ID (both in fancybox and in original table)
	$('#report-' + id).remove();
	$('#fancybox-content #report-' + id).remove();

	// fancybox workaound
	if (remove_type == 'summary' && $('#fancybox-content #schedule_report_table').is(':visible')) {
		nr_of_scheduled_instances = $('#fancybox-content #schedule_report_table tr').not('#schedule_header').length;
	}
	if (nr_of_scheduled_instances == 0) {
		// last item deleted
		$('#schedule_report').hide(); // hide entire table/div
		$('#show_schedule').hide(); // remove 'View schedules' button
		$('#is_scheduled').remove();
		if ($('#report_id')) {
			var chk_text = '';
			chk_text = $('#report_id option:selected').text();
			chk_text = chk_text.replace(" ( *" + _scheduled_label + "* )", '');
			$('#report_id option:selected').text(chk_text);
		}
		if ($(".fancybox").is(':visible')) {
			$.fancybox.close();
		}
	}

	if (remove_type!='' && remove_type != 'undefined') {
		if ($('#' + remove_type + '_scheduled_reports_table tbody').not('.no-result').length == 0) {
			$('#' + remove_type + '_headers').hide();
			$('#' + remove_type + '_no_result').show();
		}
	}

	jgrowl_message(msg, _reports_success);
}

function setup_editable()
{
	var save_url = _site_domain + _index_page + "/schedule/save_schedule_item/";
	$(".iseditable").editable(save_url, {
		id   : 'elementid',
		name : 'newvalue',
		type : 'text',
		event : 'dblclick',
		width : 'auto',
		height : '14px',
		submit : _ok_str,
		cancel : _cancel_str,
		placeholder:_reports_edit_information
	});
	$(".period_select").editable(save_url, {
		data : function(value) {
			var intervals = [];
			$('#period option').map(function() {
				intervals.push("'"+$(this).val()+"': '"+$(this).text()+"' ");
			});
			intervals = "{"+intervals.join(",")+"}";
			return intervals;
		},
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : _ok_str,
		cancel : _cancel_str
	});
	$(".iseditable_txtarea").editable(save_url, {
		indicator : "<img src='" + _site_domain + "application/media/images/loading.gif'>",
		id   : 'elementid',
		name : 'newvalue',
		type : 'textarea',
		event : 'dblclick',
		rows: '3',
		submit : _ok_str,
		cancel : _cancel_str,
		cssclass: "txtarea",
		placeholder:_reports_edit_information
	});
	$(".report_name").editable(save_url, {
		data : function (){
			switch (_report_types_json[this.id.split('-')[0].split('.')[0]]) {
				case 'avail':
					return _saved_avail_reports;
				case 'sla':
					return _saved_sla_reports;
				case 'summary':
					return _saved_summary_reports;
			}
			return false;
		},
		id   : 'elementid',
		name : 'newvalue',
		event : 'dblclick',
		type : 'select',
		submit : 'OK',
		cancel : 'cancel'
	});
}

function create_new_schedule_rows(id)
{
	var return_str = '';
	var rep_type = $('#type').attr('value');
	var saved_report_id = $('#saved_report_id').attr('value');
	var period = $('#period').attr('value');
	var period_str = $('#period option:selected').text();
	var recipients = $('#recipients').attr('value');
	var filename = $('#filename').attr('value');
	var local_persistent_filepath = $('#local_persistent_filepath').attr('value');
	var description = $('#description').attr('value');
	if (description == '')
		description = '&nbsp;';

	var report_type_id = -1;
	for (var i in _report_types_json) {
		if (_report_types_json[i] == rep_type) {
			report_type_id = i;
		}
	}

	var reportname = $('#saved_report_id').find(':selected').text();

	return_str += '<tr id="report-' + id + '" class="odd">';
	return_str += '<td class="period_select" title="' + _reports_edit_information + '" id="period_id-' + id + '">' + period_str + '</td>';
	return_str += '<td class="report_name" id="' + report_type_id + '.report_id-' + id + '">' + reportname + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="recipients-' + id + '">' + recipients + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="filename-' + id + '">' + filename + '</td>';
	return_str += '<td class="iseditable_txtarea" title="' + _reports_edit_information + '" id="description-' + id + '">' + description + '</td>';
	return_str += '<td class="iseditable" title="' + _reports_edit_information + '" id="local_persistent_filepath-' + id + '">' + local_persistent_filepath + '</td>';
	return_str += '<td><form><input type="button" class="send_report_now" id="send_now_' + rep_type + '_' + id + '" title="' + _reports_send_now + '" value="&nbsp;" data-report_id="'+saved_report_id+'" data-type="'+rep_type+'" data-schedule="'+id+'"></form>';
	return_str += '<div class="delete_schedule" onclick="schedule_delete(' + id + ', \'' + rep_type + '\');" id="delid_' + id + '"><img src="' + _site_domain + _theme_path + 'icons/16x16/delete-schedule.png" class="deleteimg" /></div></td></tr>';
	return return_str;
}


