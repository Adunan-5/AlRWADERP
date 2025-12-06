<script src="<?php echo base_url('assets/plugins/jquery-validation/additional-methods.min.js'); ?>"></script>
<script>

	function uploadfilecsv(event) {
    'use strict';

	console.log('Selected file:', $('#file_csv')[0].files[0]);

    if ($("#file_csv").val() != '' && $("#file_csv").val().split('.').pop() === 'xlsx') {
        var formData = new FormData();
        formData.append("file_csv", $('#file_csv')[0].files[0]);
        formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
        formData.append("month_employees", $('#month_employees').val());

        // Show box loading
        var html = '<div class="Box"><span><span></span></span></div>';
        $('#box-loading').html(html);
        $(event).attr("disabled", "disabled");

        $.ajax({
            url: admin_url + 'hr_payroll/import_employees_excel',
            method: 'post',
            data: formData,
            contentType: false,
            processData: false
        }).done(function(response) {
            response = JSON.parse(response);
            $('#box-loading').html('');
            $(event).removeAttr('disabled');

            $("#file_csv").val(null);
            $("#file_csv").change();
            $("#file_upload_response").empty();

            if (response.total_rows) {
                $("#file_upload_response").append("<h4><?php echo _l('_Result'); ?></h4><h5><?php echo _l('import_line_number'); ?> :" + response.total_rows + "</h5>");
            }
            if (response.total_row_success) {
                $("#file_upload_response").append("<h5><?php echo _l('import_line_number_success'); ?> :" + response.total_row_success + "</h5>");
            }
            if (response.total_row_false) {
                $("#file_upload_response").append("<h5><?php echo _l('import_line_number_failed'); ?> :" + response.total_row_false + "</h5>");
            }
            if (response.total_row_false > 0) {
                $("#file_upload_response").append('<a href="' + response.site_url + response.filename + '" class="btn btn-warning"><?php echo _l('hr_download_file_error'); ?></a>');
            }
            if (response.total_rows < 1) {
                alert_float('warning', response.message);
            }

			// Redirect on success
            if (response.message === <?php echo json_encode(_l('import_processed')); ?>) {
                alert_float('success', response.message);
                setTimeout(function() {
                    // window.location.href = admin_url + 'hr_payroll/manage_employees';
                }, 1000); // Delay for user to see success message
            }

            // Refresh Handsontable grid
            employees_filter();
        }).fail(function() {
            $('#box-loading').html('');
            $(event).removeAttr('disabled');
            alert_float('danger', '<?php echo _l('error_uploading_file'); ?>');
        });

        return false;
    } else if ($("#file_csv").val() != '') {
        alert_float('warning', "<?php echo _l('_please_select_a_file'); ?>");
    }
}

	function dowload_contract_excel() {
    'use strict';

    var formData = new FormData();
    formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
    formData.append("month_employees", $('#month_employees').val());

    $.ajax({
        url: admin_url + 'hr_payroll/create_employees_sample_file',
        method: 'post',
        data: formData,
        contentType: false,
        processData: false
    }).done(function(response) {
        response = JSON.parse(response);
        if (response.success === true) {
            alert_float('success', "<?php echo _l('create_payroll_file_success'); ?>");
            $('.staff_contract_download').removeClass('hide');
            $('.staff_contract_create').addClass('hide');
            $('.staff_contract_download').attr({target: '_blank', href: site_url + response.filename});
        } else {
            alert_float('warning', response.message || "<?php echo _l('create_attendance_file_false'); ?>");
        }
    });
}

	$('#month_employees').on('change', function() {
		'use strict';

		$('.staff_contract_download').addClass('hide');
		$('.staff_contract_create').removeClass('hide');

	});
</script>