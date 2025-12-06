<style>
.table thead th {
    position: sticky;
    top: 0;
    background-color: #fff; /* Ensure headers have a solid background */
    z-index: 10; /* Keep headers above table content */
    border-bottom: 2px solid #dee2e6; /* Match Bootstrap table styling */
}
.table-responsive {
    max-height: 500px; /* Limit table height for scrolling */
    overflow-y: auto; /* Enable vertical scrolling */
}
</style>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name / IQAMA#</th>
                <th>Expiry Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 1; foreach ($alerts as $a): 
            // pick expiry (staff alerts use 'expiry_date', vacations use 'expected_end_date')
            $date = $a['expiry_date'] ?? $a['expected_end_date'] ?? null;

            // handle empty / null / 0000-00-00
            if (empty($date) || $date === '0000-00-00') {
                $expiry_display = '-';
                $status_display = 'No expiry date provided';
            } else {
                $expiry_display = _d($date);
                // days difference (integer)
                $diff_days = (int) round( (strtotime($date) - time()) / (60*60*24) );
                if ($diff_days < 0) {
                    $status_display = abs($diff_days) . ' days ago expired';
                } else {
                    $status_display = 'Due in ' . $diff_days . ' days';
                }
            }
        ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td>
                    <a href="<?php echo admin_url('staff/member/'.$a['staffid']); ?>"><?php echo html_escape($a['name']); ?></a><br>
                    <small>IQAMA#: <?php echo html_escape($a['iqama_number'] ?? ''); ?></small>
                </td>
                <td><?php echo $expiry_display; ?></td>
                <td><?php echo $status_display; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>