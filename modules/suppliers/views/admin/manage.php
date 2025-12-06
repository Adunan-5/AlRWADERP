<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if (has_permission('suppliers', '', 'create')): ?>
                    <a href="<?= admin_url('suppliers/add') ?>" class="btn btn-primary pull-right"><?= _l('add_new_supplier') ?></a>
                <?php endif; ?>
                <h4 class="bold"><?= _l('suppliers') ?></h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <table class="table dt-table" data-order-col="0" data-order-type="desc">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= _l('supplier_name') ?></th>
                                    <th><?= _l('supplier_mobile') ?></th>
									<th><?= _l('supplier_email') ?></th>
									<th><?= _l('supplier_enable_vat') ?></th>
									<th><?= _l('supplier_vat_percentage') ?></th>
                                    <th><?= _l('actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($this->suppliers_model->get() as $supplier): ?>
                                    <tr>
                                        <td><?= $supplier['id'] ?></td>
                                        <td> <a href="<?= admin_url('suppliers/edit/' . $supplier['id']) ?>"> <?= $supplier['name'] ?></td></a>
                                        <td><?= $supplier['mobile'] ?></td>
										<td><?= $supplier['email'] ?></td>
										<td><?= ($supplier['enable_vat'] == 1) ? 'Yes' : 'No' ?></td>
										<td><?= $supplier['vat_percentage'] ?></td>
                                        <td>
                                            <?php if (has_permission('suppliers', '', 'edit')): ?>
                                                <a href="<?= admin_url('suppliers/edit/' . $supplier['id']) ?>" class="btn btn-sm btn-default"><?= _l('edit') ?></a>
                                            <?php endif; ?>

                                            <?php if (has_permission('suppliers', '', 'delete')): ?>
                                                <a href="<?= admin_url('suppliers/delete/' . $supplier['id']) ?>" class="btn btn-sm btn-danger _delete"><?= _l('delete') ?></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
