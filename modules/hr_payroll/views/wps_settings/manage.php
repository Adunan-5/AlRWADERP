<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
						<h4 class="no-margin">
							<i class="fa fa-file-excel-o"></i> <?php echo _l('WPS Export Settings'); ?>
						</h4>
						<hr class="hr-panel-heading" />

						<p class="text-muted">
							<?php echo _l('Configure WPS (Wage Protection System) export file settings for bank transfers'); ?>
						</p>

						<?php echo form_open(admin_url('hr_payroll/save_wps_settings'), ['id' => 'wps_settings_form']); ?>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="company_id" class="control-label">
										<?php echo _l('company'); ?> <small class="text-muted">(Leave blank for default)</small>
									</label>
									<select name="company_id" id="company_id" class="form-control selectpicker" data-live-search="true" onchange="loadWpsSettings(this.value)">
										<option value=""><?php echo _l('Default (All Companies)'); ?></option>
										<?php if (!empty($companies)): ?>
											<?php foreach ($companies as $company): ?>
												<option value="<?php echo $company['id']; ?>"><?php echo $company['name']; ?></option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</div>
							</div>
						</div>

						<hr>

						<h5 class="font-medium">Header Information (Row 2 in Export File)</h5>

						<div class="row">
							<div class="col-md-4">
								<?php echo render_input('type', 'Type', '', 'text', ['placeholder' => 'e.g., 111']); ?>
							</div>
							<div class="col-md-4">
								<?php echo render_input('customer_name', 'Customer Name', '', 'text'); ?>
							</div>
							<div class="col-md-4">
								<?php echo render_input('agreement_code', 'Agreement Code', '', 'text'); ?>
							</div>
						</div>

						<div class="row">
							<div class="col-md-4">
								<?php echo render_input('funding_account', 'Funding Account', '', 'text'); ?>
							</div>
							<div class="col-md-4">
								<?php echo render_input('branch_no', 'Branch No', '', 'text'); ?>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="credit_date_format">Credit Date Format</label>
									<select name="credit_date_format" id="credit_date_format" class="form-control selectpicker">
										<option value="DDMMYYYY">DDMMYYYY</option>
										<option value="YYYY-MM-DD">YYYY-MM-DD</option>
										<option value="DD/MM/YYYY">DD/MM/YYYY</option>
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-4">
								<?php echo render_input('mins_lab_establish_id', 'Ministry of Labor Establishment ID', '', 'text'); ?>
							</div>
							<div class="col-md-4">
								<?php echo render_input('ecr_id', 'ECR ID', '', 'text'); ?>
							</div>
							<div class="col-md-4">
								<?php echo render_input('bank_code', 'Bank Code', 'RIBL', 'text', ['placeholder' => 'e.g., RIBL']); ?>
							</div>
						</div>

						<div class="row">
							<div class="col-md-4">
								<?php echo render_input('currency', 'Currency', 'SAR', 'text', ['placeholder' => 'e.g., SAR']); ?>
							</div>
							<div class="col-md-4">
								<?php echo render_input('batch', 'Batch', '', 'text'); ?>
							</div>
							<div class="col-md-4">
								<?php echo render_input('file_reference', 'File Reference', '', 'text'); ?>
							</div>
						</div>

						<hr>

						<h5 class="font-medium">Default Payment Information</h5>

						<div class="row">
							<div class="col-md-6">
								<?php echo render_input('payment_desc', 'Payment Description', 'Salary for October 2025', 'text', ['placeholder' => 'e.g., Salary for October 2025']); ?>
							</div>
							<div class="col-md-6">
								<?php echo render_input('payment_ref', 'Payment Reference', '', 'text'); ?>
							</div>
						</div>

						<div class="btn-bottom-toolbar text-right">
							<button type="submit" class="btn btn-primary">
								<?php echo _l('submit'); ?>
							</button>
						</div>

						<?php echo form_close(); ?>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php init_tail(); ?>

<script>
	function loadWpsSettings(companyId) {
		console.log('Loading WPS settings for company:', companyId);

		if (companyId === '') {
			companyId = 'null';
		}

		$.ajax({
			url: admin_url + 'hr_payroll/get_wps_settings',
			type: 'GET',
			data: { company_id: companyId },
			dataType: 'json',
			success: function(response) {
				console.log('WPS Settings Response:', response);

				if (response.success && response.data) {
					var data = response.data;
					console.log('Populating form with data:', data);

					// Populate form fields
					$('input[name="type"]').val(data.type || '');
					$('input[name="customer_name"]').val(data.customer_name || '');
					$('input[name="agreement_code"]').val(data.agreement_code || '');
					$('input[name="funding_account"]').val(data.funding_account || '');
					$('input[name="branch_no"]').val(data.branch_no || '');
					$('#credit_date_format').val(data.credit_date_format || 'DDMMYYYY').selectpicker('refresh');
					$('input[name="mins_lab_establish_id"]').val(data.mins_lab_establish_id || '');
					$('input[name="ecr_id"]').val(data.ecr_id || '');
					$('input[name="bank_code"]').val(data.bank_code || 'RIBL');
					$('input[name="currency"]').val(data.currency || 'SAR');
					$('input[name="batch"]').val(data.batch || '');
					$('input[name="file_reference"]').val(data.file_reference || '');
					$('input[name="payment_desc"]').val(data.payment_desc || '');
					$('input[name="payment_ref"]').val(data.payment_ref || '');

					console.log('Form fields populated successfully');
				} else {
					console.log('No data in response or success is false');
				}
			},
			error: function(xhr, status, error) {
				console.error('Error loading WPS settings:', error);
				console.error('Response:', xhr.responseText);
				alert_float('danger', 'Failed to load WPS settings');
			}
		});
	}

	$(function() {
		// Initialize selectpicker
		$('.selectpicker').selectpicker();

		// Load default settings on page load
		loadWpsSettings('null');

		// Handle form submission
		$('#wps_settings_form').on('submit', function(e) {
			e.preventDefault();

			var formData = $(this).serialize();

			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: formData,
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						alert_float('success', response.message);
					} else {
						alert_float('danger', response.message);
					}
				},
				error: function() {
					alert_float('danger', 'An error occurred while saving');
				}
			});
		});
	});
</script>

</body>
</html>
