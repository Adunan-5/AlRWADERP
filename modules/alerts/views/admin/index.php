<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="panel_s">
                <div class="panel-body text-center">

                <div class="text-right mb-3">
                    <form id="alerts-filter-form" class="form-inline">
                        <label for="months" class="mr-2">Show alerts expiring in:</label>
                        <select id="months" name="months" class="form-control">
                            <?php foreach ([1,2,3,6,12] as $m): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($months == $m ? 'selected' : ''); ?>>
                                    <?php echo $m; ?> month<?php echo ($m > 1 ? 's' : ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                    <div id="alerts-icons-container">
    <?php
    $icons = [
        'iqama'     => 'id-card',
        'ajeer'     => 'file-contract',
        'passport'  => 'passport',
        'insurance' => 'heartbeat',
        'health'    => 'notes-medical',
        'visa'      => 'plane',
        'atm'       => 'credit-card',
        'contract'  => 'briefcase',
        'vacation'  => 'umbrella-beach'
    ];
    foreach ($counts as $type => $count): ?>
        <a href="javascript:void(0)" 
           class="alert-icon m-3 position-relative d-inline-block" 
           data-type="<?php echo $type; ?>" 
           data-toggle="tooltip" 
           title="<?php echo ucfirst($type).' Alerts'; ?>">

            <i class="fa fa-<?php echo $icons[$type]; ?> fa-3x"></i>
            <?php if ($count > 0): ?>
                <span class="badge badge-danger position-absolute" style="top:-5px; right:-10px;">
                    <?php echo $count; ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

                </div>
            </div>
        </div>
    </div>

    <!-- Table container -->
    <div class="row" id="alerts-table-container" style="display:none;">
        <div class="col-md-12">
            <div class="panel_s">
                <div class="panel-heading"><span id="alerts-title"></span></div>
                <div class="panel-body" id="alerts-table-content">
                    <!-- AJAX-loaded content will appear here -->
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
    $('[data-toggle="tooltip"]').tooltip();

    // when changing months dropdown
    $('#months').on('change', function() {
        var months = $(this).val();

        // fetch updated counts and re-render icons
        $.get(admin_url + 'alerts?months=' + months + '&ajax=1', function(html) {
            $('#alerts-icons-container').html(html); // replace icon grid
            $('[data-toggle="tooltip"]').tooltip();  // re-init tooltips
        });
    });

    // delegate event (since icons are replaced dynamically)
    $(document).on('click', '.alert-icon', function() {
        var type = $(this).data('type');
        var months = $('#months').val();

        $('.alert-icon').removeClass('text-primary');
        $(this).addClass('text-primary');

        $.get(admin_url + 'alerts/view/' + type + '?months=' + months, function(html) {
            $('#alerts-title').text(type.charAt(0).toUpperCase() + type.slice(1) + ' Alerts');
            $('#alerts-table-content').html(html);
            $('#alerts-table-container').show();
        });
    });
});
</script>
