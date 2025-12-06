<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (staff_can('create', 'staff')) { ?>
                <div class="sticky-panel">
                    <div class="tw-mb-2">
                        <?php if (staff_can('create', 'staff')) { ?>
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4 class="tw-font-semibold tw-mb-4">Import Employee</h4>
                                <?= form_open_multipart(admin_url('staff/import_excel'), ['id' => 'import-staff-form']) ?>
                                    <div class="form-group">
                                        <label for="import_file" class="control-label">Choose Excel File</label>
                                        <input type="file" name="import_file" accept=".xls,.xlsx" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-upload"></i> Upload
                                    </button>
                                <?= form_close(); ?>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- New Download Master Data Button -->
                        <a href="<?php echo admin_url('staff/export_master_data'); ?>" 
                            class="btn btn-success" style="float:right;">
                            <i class="fa fa-download"></i> Download Master Data
                        </a>

                        <!-- New Download Import Template Button -->
                        <a href="<?php echo admin_url('staff/download_import_template'); ?>" 
                            class="btn btn-info" style="float:right; margin-left: 5px; margin-right: 10px;">
                            <i class="far fa-file-excel"></i></i> Download Import Template
                        </a>
                        <a href="<?php echo admin_url('staff/member'); ?>" class="btn btn-primary">
                            <i class="fa-regular fa-plus tw-mr-1"></i>
                            <?php echo _l('new_staff'); ?>
                        </a>
                    </div>
                </div>
                <?php } ?>

                <?php
                $company_types = get_company_types_with_staff_count();
                ?>

                <div class="row tw-mt-4 tw-mb-4">
                    <?php foreach ($company_types as $company) { ?>
                        <div class="col-md-4">
                            <div class="card shadow-sm text-center p-3" style="border-radius: 10px; border:1px solid #ddd;">
                                <h5 class="mb-2">
                                    <?php echo $company['name']; ?>
                                </h5>
                                <h3 class="tw-font-bold text-primary"><?php echo $company['staff_count']; ?></h3>
                                <p class="mb-0 text-muted">Staffs</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Filters Row -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Staff Type</label>
                        <select id="filter_stafftype" class="form-control selectpicker" data-live-search="true" title="All Staff Types">
                            <option value="">All Staff Types</option>
                            <?php 
                            $staff_types = get_all_stafftypes();
                            foreach ($staff_types as $type) { 
                            ?>
                                <option value="<?php echo htmlspecialchars($type['id']); ?>">
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Country</label>
                        <select id="filter_country" class="form-control selectpicker" data-live-search="true" title="All Countries">
                            <option value="">All Countries</option>
                            <?php 
                            $countries = get_all_countries();
                            foreach ($countries as $country) { ?>
                                <option value="<?php echo htmlspecialchars($country['country_id']); ?>">
                                    <?php echo htmlspecialchars($country['short_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">GOSI Status</label>
                        <select id="filter_gosi" class="form-control selectpicker" data-live-search="true" title="All">
                            <option value="">All</option>
                            <option value="1">GOSI</option>
                            <option value="0">NO-GOSI</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end" style="margin-top: 24px;">
                        <button type="button" id="clear_filters" class="btn btn-secondary w-100">
                            Clear Filters
                        </button>
                    </div>
                </div>
                <div class="panel_s" style="margin-top: 25px">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('staff_dt_code'),
                            _l('company'),
                            _l('staff_dt_name'),
                            _l('iqama_number'),
                            _l('employee_type'),
                            _l('staff_dt_email'),
                            _l('phone_number'),
                            _l('staff_dt_active'),
                        ];
                        $custom_fields = get_custom_fields('staff', ['show_on_table' => 1]);
                        foreach ($custom_fields as $field) {
                            array_push($table_data, [
                                'name'     => $field['name'],
                                'th_attrs' => ['data-type' => $field['type'], 'data-custom-field' => 1],
                            ]);
                        }
                        render_datatable($table_data, 'staff');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="delete_staff" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('staff/delete', ['delete_staff_form'])); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('delete_staff'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="delete_id">
                    <?php echo form_hidden('id'); ?>
                </div>
                <p><?php echo _l('delete_staff_info'); ?></p>
                <?php
                echo render_select('transfer_data_to', $staff_members, ['staffid', ['name']], 'staff_member', get_staff_user_id(), [], [], '', '', false);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-danger _delete"><?php echo _l('confirm'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
// $(function() {
//     initDataTable('.table-staff', window.location.href, [], [], [], [0, "asc"]);
// });
$(function() {
    var staffTable = initDataTable('.table-staff', window.location.href, [], [], [], [0, "asc"]);
    staffTable = $('.table-staff').DataTable(); // Ensure we have the DataTable instance

    function reloadTableWithFilters() {
        var stafftype = $('#filter_stafftype').val();
        var country = $('#filter_country').val();
        var gosi = $('#filter_gosi').val();

        // Build query string for filters (only include non-empty values)
        var params = new URLSearchParams();
        if (stafftype) params.append('stafftype', stafftype);
        if (country) params.append('country', country);
        if (gosi) params.append('gosi', gosi);

        var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        staffTable.ajax.url(newUrl).load();
    }

    $('#filter_stafftype').on('change', reloadTableWithFilters);
    $('#filter_country').on('change', reloadTableWithFilters);
    $('#filter_gosi').on('change', reloadTableWithFilters);

    $('#clear_filters').on('click', function() {
        $('#filter_stafftype').val('').trigger('change');
        $('#filter_country').val('').trigger('change');
        $('#filter_gosi').val('').trigger('change');
    });

    // Apply current URL filters on load if any
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('stafftype')) {
        $('#filter_stafftype').val(urlParams.get('stafftype'));
    }
    if (urlParams.has('country')) {
        $('#filter_country').val(urlParams.get('country'));
    }
    if (urlParams.has('gosi')) {
        $('#filter_gosi').val(urlParams.get('gosi'));
    }
});

function delete_staff_member(id) {
    $('#delete_staff').modal('show');
    $('#transfer_data_to').find('option').prop('disabled', false);
    $('#transfer_data_to').find('option[value="' + id + '"]').prop('disabled', true);
    $('#delete_staff .delete_id input').val(id);
    $('#transfer_data_to').selectpicker('refresh');
}
</script>

<style>
/* Sticky panel for both import form and new staff button */
.sticky-panel {
    position: sticky;
    top: 60px; /* Adjust based on your Perfex header height */
    z-index: 999; /* Keep it above table */
    background-color: #fff; /* Ensure background is white to avoid transparency */
    padding: 10px 0; /* Add some padding for better spacing */
}

.card {
    background: #fff;
    transition: all 0.3s ease-in-out;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 6px 15px rgba(0,0,0,0.1);
}
</style>
</body>

</html>