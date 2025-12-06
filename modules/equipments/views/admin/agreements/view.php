<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Header -->
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="tw-mt-0 tw-font-bold tw-text-xl">
                                    <?php echo _l('agreement'); ?> - <?php echo $agreement->agreement_number; ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php if (has_permission('equipment_agreements', '', 'edit')) { ?>
                                    <a href="<?php echo admin_url('equipments/agreements/edit/' . $agreement->id); ?>" class="btn btn-info">
                                        <i class="fa fa-edit"></i> <?php echo _l('edit'); ?>
                                    </a>
                                <?php } ?>
                                <a href="<?php echo admin_url('equipments/agreements'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?php echo _l('back'); ?>
                                </a>
                            </div>
                        </div>
                        <hr>

                        <!-- Agreement Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="tw-font-semibold"><?php echo _l('agreement_details'); ?></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong><?php echo _l('agreement_number'); ?></strong></td>
                                            <td><?php echo $agreement->agreement_number; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('agreement_type'); ?></strong></td>
                                            <td>
                                                <?php if ($agreement->agreement_type == 'supplier') { ?>
                                                    <span class="label label-primary">
                                                        <i class="fa fa-building-o"></i> <?php echo _l('supplier'); ?>
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="label label-success">
                                                        <i class="fa fa-user-o"></i> <?php echo _l('client'); ?>
                                                    </span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('party'); ?></strong></td>
                                            <td>
                                                <?php
                                                if ($party_type == 'supplier') {
                                                    echo $party->name;
                                                } else {
                                                    echo $party->company;
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php if (isset($project)) { ?>
                                        <tr>
                                            <td><strong><?php echo _l('project'); ?></strong></td>
                                            <td><?php echo $project->name; ?></td>
                                        </tr>
                                        <?php } ?>
                                        <tr>
                                            <td><strong><?php echo _l('start_date'); ?></strong></td>
                                            <td><?php echo _d($agreement->start_date); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('end_date'); ?></strong></td>
                                            <td><?php echo $agreement->end_date ? _d($agreement->end_date) : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('duration_months'); ?></strong></td>
                                            <td><?php echo $agreement->duration_months ? $agreement->duration_months . ' ' . _l('months') : '-'; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h4 class="tw-font-semibold"><?php echo _l('payment_terms'); ?></h4>
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong><?php echo _l('payment_terms'); ?></strong></td>
                                            <td><?php echo $agreement->payment_terms_days . ' ' . _l('days'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('currency'); ?></strong></td>
                                            <td><?php echo $agreement->currency; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('status'); ?></strong></td>
                                            <td>
                                                <?php
                                                $status_labels = [
                                                    'draft' => '<span class="label label-default">' . _l('draft') . '</span>',
                                                    'active' => '<span class="label label-success">' . _l('active') . '</span>',
                                                    'expired' => '<span class="label label-warning">' . _l('expired') . '</span>',
                                                    'terminated' => '<span class="label label-danger">' . _l('terminated') . '</span>',
                                                    'completed' => '<span class="label label-info">' . _l('completed') . '</span>',
                                                ];
                                                echo isset($status_labels[$agreement->status]) ? $status_labels[$agreement->status] : $agreement->status;
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('signed_date'); ?></strong></td>
                                            <td><?php echo $agreement->signed_date ? _d($agreement->signed_date) : '-'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong><?php echo _l('created_at'); ?></strong></td>
                                            <td><?php echo _dt($agreement->created_at); ?></td>
                                        </tr>
                                        <?php if ($agreement->updated_at) { ?>
                                        <tr>
                                            <td><strong><?php echo _l('last_updated'); ?></strong></td>
                                            <td><?php echo _dt($agreement->updated_at); ?></td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Notes -->
                        <?php if ($agreement->notes) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="tw-font-semibold"><?php echo _l('notes'); ?></h4>
                                <div class="well">
                                    <?php echo nl2br(e($agreement->notes)); ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
