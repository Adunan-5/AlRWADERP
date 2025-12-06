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