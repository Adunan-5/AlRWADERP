<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="tw-mt-0 tw-font-bold tw-text-xl tw-text-neutral-800">
                            <?php echo isset($agreement) ? _l('edit_agreement') : _l('add_new_agreement'); ?>
                        </h4>
                        <hr class="hr-panel-heading">

                        <?php echo form_open($this->uri->uri_string()); ?>

                        <!-- Row 1: Agreement Number and Type -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agreement_number"><?php echo _l('agreement_number'); ?></label>
                                    <input type="text"
                                           class="form-control"
                                           name="agreement_number"
                                           id="agreement_number"
                                           value="<?php echo isset($agreement) ? $agreement->agreement_number : ''; ?>"
                                           placeholder="<?php echo _l('leave_empty_for_auto_generation'); ?>">
                                    <small class="text-muted"><?php echo _l('leave_empty_auto_generate'); ?></small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="agreement_type">
                                        <small class="req text-danger">* </small>
                                        <?php echo _l('agreement_type'); ?>
                                    </label>
                                    <select class="selectpicker"
                                            data-width="100%"
                                            name="agreement_type"
                                            id="agreement_type"
                                            required
                                            onchange="togglePartyField()">
                                        <option value=""><?php echo _l('select'); ?></option>
                                        <option value="supplier" <?php echo (isset($agreement) && $agreement->agreement_type == 'supplier') ? 'selected' : ''; ?>>
                                            <?php echo _l('supplier_agreement'); ?>
                                        </option>
                                        <option value="client" <?php echo (isset($agreement) && $agreement->agreement_type == 'client') ? 'selected' : ''; ?>>
                                            <?php echo _l('client_agreement'); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Party Selection (Supplier/Client and Project) -->
                        <div class="row">
                            <!-- Supplier Selection (shown when agreement_type = supplier) -->
                            <div class="col-md-6" id="supplier-field" style="display: none;">
                                <?php
                                $selected_supplier = isset($agreement) && $agreement->agreement_type == 'supplier' ? $agreement->party_id : '';
                                echo render_select('supplier_id', $suppliers, ['id', 'name'], _l('supplier'), $selected_supplier, ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => true]);
                                ?>
                            </div>

                            <!-- Client Selection (shown when agreement_type = client) -->
                            <div class="col-md-6" id="client-field" style="display: none;">
                                <?php
                                $selected_client = isset($agreement) && $agreement->agreement_type == 'client' ? $agreement->party_id : '';
                                echo render_select('client_id', $clients, ['userid', 'company'], _l('client'), $selected_client, ['data-none-selected-text' => _l('dropdown_non_selected_tex'), 'required' => true]);
                                ?>
                            </div>

                            <!-- Project (only for client agreements) -->
                            <div class="col-md-6" id="project-field" style="display: none;">
                                <?php
                                $selected_project = isset($agreement) ? $agreement->project_id : '';
                                echo render_select('project_id', $projects, ['id', 'name'], _l('project'), $selected_project, ['data-none-selected-text' => _l('dropdown_non_selected_tex')]);
                                ?>
                            </div>
                        </div>

                        <!-- Row 3: Start and End Dates -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group" app-field-wrapper="start_date">
                                    <label for="start_date" class="control-label">
                                        <small class="req text-danger">* </small>
                                        <?php echo _l('start_date'); ?>
                                    </label>
                                    <div class="input-group date">
                                        <input type="text"
                                               id="start_date"
                                               name="start_date"
                                               class="form-control datepicker"
                                               value="<?php echo isset($agreement) ? _d($agreement->start_date) : ''; ?>"
                                               autocomplete="off"
                                               required>
                                        <div class="input-group-addon">
                                            <i class="fa-regular fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group" app-field-wrapper="end_date">
                                    <label for="end_date" class="control-label">
                                        <?php echo _l('end_date'); ?>
                                    </label>
                                    <div class="input-group date">
                                        <input type="text"
                                               id="end_date"
                                               name="end_date"
                                               class="form-control datepicker"
                                               value="<?php echo isset($agreement) && $agreement->end_date ? _d($agreement->end_date) : ''; ?>"
                                               autocomplete="off">
                                        <div class="input-group-addon">
                                            <i class="fa-regular fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Duration, Payment Terms, Currency -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="duration_months"><?php echo _l('duration_months'); ?></label>
                                    <input type="number"
                                           class="form-control"
                                           name="duration_months"
                                           id="duration_months"
                                           value="<?php echo isset($agreement) ? $agreement->duration_months : ''; ?>"
                                           min="1">
                                </div>
                            </div>

                            <!-- Payment Terms (Days) -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_terms_days"><?php echo _l('payment_terms_days'); ?></label>
                                    <input type="number"
                                           class="form-control"
                                           name="payment_terms_days"
                                           id="payment_terms_days"
                                           value="<?php echo isset($agreement) ? $agreement->payment_terms_days : '30'; ?>"
                                           min="1">
                                    <small class="text-muted"><?php echo _l('default_30_days'); ?></small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <?php
                                $currencies = $this->db->get(db_prefix() . 'currencies')->result_array();
                                $selected_currency = isset($agreement) ? $agreement->currency : get_base_currency()->name;
                                echo render_select('currency', $currencies, ['name', 'name'], _l('currency'), $selected_currency);
                                ?>
                            </div>
                        </div>

                        <!-- Row 5: Status and Signed Date -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status"><?php echo _l('status'); ?></label>
                                    <select class="selectpicker"
                                            data-width="100%"
                                            name="status"
                                            id="status">
                                        <option value="draft" <?php echo (!isset($agreement) || $agreement->status == 'draft') ? 'selected' : ''; ?>>
                                            <?php echo _l('draft'); ?>
                                        </option>
                                        <option value="active" <?php echo (isset($agreement) && $agreement->status == 'active') ? 'selected' : ''; ?>>
                                            <?php echo _l('active'); ?>
                                        </option>
                                        <option value="expired" <?php echo (isset($agreement) && $agreement->status == 'expired') ? 'selected' : ''; ?>>
                                            <?php echo _l('expired'); ?>
                                        </option>
                                        <option value="terminated" <?php echo (isset($agreement) && $agreement->status == 'terminated') ? 'selected' : ''; ?>>
                                            <?php echo _l('terminated'); ?>
                                        </option>
                                        <option value="completed" <?php echo (isset($agreement) && $agreement->status == 'completed') ? 'selected' : ''; ?>>
                                            <?php echo _l('completed'); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group" app-field-wrapper="signed_date">
                                    <label for="signed_date" class="control-label">
                                        <?php echo _l('signed_date'); ?>
                                    </label>
                                    <div class="input-group date">
                                        <input type="text"
                                               id="signed_date"
                                               name="signed_date"
                                               class="form-control datepicker"
                                               value="<?php echo isset($agreement) && $agreement->signed_date ? _d($agreement->signed_date) : ''; ?>"
                                               autocomplete="off">
                                        <div class="input-group-addon">
                                            <i class="fa-regular fa-calendar calendar-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Row 6: Notes -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes"><?php echo _l('notes'); ?></label>
                                    <textarea class="form-control"
                                              name="notes"
                                              id="notes"
                                              rows="4"><?php echo isset($agreement) ? $agreement->notes : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-check"></i>
                                    <?php echo isset($agreement) ? _l('submit') : _l('submit'); ?>
                                </button>
                                <a href="<?php echo admin_url('equipments/agreements'); ?>" class="btn btn-default">
                                    <i class="fa fa-times"></i>
                                    <?php echo _l('cancel'); ?>
                                </a>
                            </div>
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
$(document).ready(function() {
    'use strict';

    // Show correct party field on page load
    togglePartyField();

    // Auto-calculate duration when dates change
    $('#start_date, #end_date').on('change', function() {
        calculateDuration();
    });

    // Auto-calculate end date when duration changes
    $('#duration_months').on('input', function() {
        calculateEndDate();
    });
});

function togglePartyField() {
    var agreementType = $('#agreement_type').val();

    if (agreementType === 'supplier') {
        $('#supplier-field').show();
        $('#client-field').hide();
        $('#project-field').hide();
        $('#supplier_id').attr('required', true);
        $('#client_id').attr('required', false);
    } else if (agreementType === 'client') {
        $('#supplier-field').hide();
        $('#client-field').show();
        $('#project-field').show();
        $('#supplier_id').attr('required', false);
        $('#client_id').attr('required', true);
    } else {
        $('#supplier-field').hide();
        $('#client-field').hide();
        $('#project-field').hide();
        $('#supplier_id').attr('required', false);
        $('#client_id').attr('required', false);
    }

    // Refresh selectpickers
    $('.selectpicker').selectpicker('refresh');
}

function calculateDuration() {
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();

    if (startDate && endDate) {
        // Parse dates (format depends on app.options.date_format)
        var start = moment(startDate, app.options.date_format.toUpperCase());
        var end = moment(endDate, app.options.date_format.toUpperCase());

        if (start.isValid() && end.isValid() && end.isSameOrAfter(start)) {
            // Calculate months including partial months (ceiling)
            var months = end.diff(start, 'months', true); // true = precise decimal
            months = Math.ceil(months); // Round up to include partial months

            // If same month, count as 1 month
            if (months === 0 && end.isSameOrAfter(start)) {
                months = 1;
            }

            $('#duration_months').val(months);
        }
    }
}

function calculateEndDate() {
    var startDate = $('#start_date').val();
    var duration = parseInt($('#duration_months').val());

    if (startDate && duration > 0) {
        // Parse start date
        var start = moment(startDate, app.options.date_format.toUpperCase());

        if (start.isValid()) {
            var endDate = start.add(duration, 'months');
            $('#end_date').val(endDate.format(app.options.date_format.toUpperCase()));
        }
    }
}
</script>
