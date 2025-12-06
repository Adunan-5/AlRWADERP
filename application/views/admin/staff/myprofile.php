<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php if (($staff_p->staffid == get_staff_user_id() || is_admin()) && ! $this->input->get('notifications')) { ?>
        <?php $this->load->view('admin/staff/stats'); ?>
        <hr class="hr-separator tw-mb-10" />
        <?php } ?>
        <div class="row">
            <?php hooks()->do_action('before_staff_myprofile'); ?>
            <div
                class="col-md-5<?= $this->input->get('notifications') ? ' hide' : ''; ?>">
                <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 tw-inline-flex tw-items-center">
                    <?= staff_profile_image($staff_p->staffid, ['staff-profile-image-small tw-mr-2'], 'small'); ?>
                    <?= e($staff_p->firstname . ' ' . $staff_p->lastname); ?>
                </h4>

                <div class="panel_s">
                    <div class="panel-body">
                        <?php if ($staff_p->active == 0) { ?>
                        <div class="alert alert-danger text-center tw-mb-2">
                            <?= _l('staff_profile_inactive_account'); ?>
                        </div>
                        <?php } ?>
                        <div class="tw-flex">
                            <div class="tw-grow">
                                <div class="profile display-inline-block">
                                    <h4 class="tw-mb-1 tw-mt-0">
                                        <?php if ($staff_p->last_activity && $staff_p->staffid != get_staff_user_id()) { ?>
                                        <small> -
                                            <?= _l('last_active'); ?>:
                                            <span class="text-has-action" data-toggle="tooltip"
                                                data-title="<?= e(_dt($staff_p->last_activity)); ?>">
                                                <?= e(time_ago($staff_p->last_activity)); ?>
                                            </span>
                                        </small>
                                        <?php } ?>
                                    </h4>
                                    <p class="tw-flex tw-items-center tw-mb-1">
                                        <i class="fa-regular fa-envelope fa-lg tw-text-neutral-400 tw-mr-2"></i>
                                        <a
                                            href="mailto:<?= e($staff_p->email); ?>">
                                            <?= e($staff_p->email); ?>
                                        </a>
                                    </p>
                                    <?php if ($staff_p->phonenumber != '') { ?>
                                    <p class="tw-flex tw-items-center">
                                        <i class="fa fa-phone-square fa-lg tw-text-neutral-400 tw-mr-2"></i>
                                        <?= e($staff_p->phonenumber); ?>
                                    </p>
                                    <?php } ?>
                                    <?php if (count($staff_departments) > 0) { ?>
                                    <div class="form-group tw-mt-6">
                                        <p class="tw-text-neutral-600 tw-font-semibold">
                                            <?= _l('staff_profile_departments'); ?>
                                        </p>
                                        <div class="clearfix"></div>
                                        <?php
              foreach ($departments as $department) { ?>
                                        <?php
              foreach ($staff_departments as $staff_department) {
                  if ($staff_department['departmentid'] == $department['departmentid']) { ?>
                                        <div class="label label-primary">
                                            <?= e($staff_department['name']); ?>
                                        </div>
                                        <?php }
                  }
                  ?>
                                        <?php } ?>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="tw-space-x-0.5">
                                <?php if (! empty($staff_p->facebook)) { ?>
                                <a href="<?= e($staff_p->facebook); ?>"
                                    target="_blank" class="btn btn-default btn-icon">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>
                                <?php } ?>
                                <?php if (! empty($staff_p->linkedin)) { ?>
                                <a href="<?= e($staff_p->linkedin); ?>"
                                    class="btn btn-default btn-icon">
                                    <i class="fa-brands fa-linkedin-in"></i>
                                </a>
                                <?php } ?>
                                <?php if (! empty($staff_p->skype)) { ?>
                                <a href="skype:<?= e($staff_p->skype); ?>"
                                    data-toggle="tooltip"
                                    title="<?= e($staff_p->skype); ?>"
                                    target="_blank" class="btn btn-default btn-icon">
                                    <i class="fa-brands fa-skype"></i>
                                </a>
                                <?php } ?>
                                <?php if (staff_can('edit', 'staff') && staff_can('view', 'staff')) { ?>
                                <a href="<?= admin_url('staff/member/' . $staff_p->staffid); ?>"
                                    class="btn btn-default btn-icon">
                                    <i class="fa fa-pencil-square"></i>
                                </a>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if (($staff_p->staffid == get_staff_user_id() || is_admin()) && ! $this->input->get('notifications')) { ?>
                        <h4 class="tw-mt-4 tw-font-semibold tw-text-base tw-text-neutral-700">
                            <?= _l('projects'); ?>
                        </h4>

                        <div>
                            <div class="_filters _hidden_inputs hidden staff_projects_filter">
                                <?= form_hidden('staff_id', $staff_p->staffid); ?>
                            </div>
                            <?php render_datatable([
                                _l('project_name'),
                                 _l('skills'),
                                _l('project_start_date'),
                                _l('project_deadline'),
                                _l('rate_type'),
                                _l('options'),
                                // _l('project_status'),
                            ], 'staff-projects', [], [
                                'data-last-order-identifier' => 'my-projects',
                                'data-default-order'         => get_table_last_order('my-projects'),
                            ]); ?>
                        </div>
                        <?php } ?>
                    </div>

                </div>

            </div>
            <?php if ($staff_p->staffid == get_staff_user_id()) { ?>
            <div class="col-md-7<?php if ($this->input->get('notifications')) {
                echo ' col-md-offset-2';
            } ?>">
                <h4
                    class="tw-mt-0 tw-flex tw-justify-between tw-items-center tw-font-bold tw-text-lg tw-text-neutral-700">
                    <?= _l('staff_profile_notifications'); ?>

                    <a href="#" class="tw-font-normal text-muted tw-text-sm"
                        onclick="mark_all_notifications_as_read_inline(); return false;">
                        <?= _l('mark_all_as_read'); ?>
                    </a>
                </h4>
                <div id="notifications"
                    class="tw-my-3 tw-rounded-md tw-shadow-sm tw-overflow-hidden tw-divide-y tw-divide-solid tw-divide-neutral-200 tw-border tw-border-solid tw-border-neutral-200 [&_img]:tw-mr-3">
                </div>
                <div class="tw-text-right">
                    <a href="#" class="btn btn-primary loader">
                        <?= _l('load_more'); ?>
                    </a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="modal fade" id="assign_job_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
            <?= form_open(admin_url('projects/assign_job'), ['id' => 'assign-job-form']) ?>
                <div class="modal-header">
                <h4 class="modal-title"><?= _l('assign_job_to_staff') ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body row">

                <!-- Project (Single select, no default) -->
                <div class="form-group col-md-6">
                    <?php
                    $projects = get_all_projects();
                    echo render_select('project_id', $projects, ['id', 'name'], 'Projects');
                    ?>
                </div>

                <!-- Skills (Multiple select, optional prefill) -->
                <div class="form-group col-md-6">
                    <?php
                    $skills = get_all_skills();
                    $selected_skills = (isset($member) ? explode(',', $member->skills) : []);
                    echo render_select('skills[]', $skills, ['id', 'name'], 'Skills', $selected_skills, ['multiple' => true]);
                    ?>
                </div>

                <!-- Badge -->
                <div class="form-group col-md-6">
                    <?= render_input('badge', 'badge') ?>
                </div>

                <!-- Start Date -->
                <div class="form-group col-md-6">
                    <?= render_date_input('start_date', 'start_date') ?>
                </div>

                <!-- End Date -->
                <div class="form-group col-md-6">
                    <?= render_date_input('end_date', 'end_date') ?>
                </div>

                <!-- Equipment (dropdown from helper) -->
                <div class="form-group col-md-6">
                    <?php
                    $equipments = get_all_equipments();
                    echo render_select('equipment', $equipments, ['id', 'name'], 'Equipment');
                    ?>
                </div>

                <!-- Regular Rate -->
                <div class="form-group col-md-6">
                    <?= render_input('regular_rate', 'regular_rate', '', 'number') ?>
                </div>

                <!-- Overtime Rate -->
                <div class="form-group col-md-6">
                    <?= render_input('overtime_rate', 'overtime_rate', '', 'number') ?>
                </div>

                <!-- Rate Type -->
                <div class="form-group col-md-12">
                    <label for="rate_type"><?= _l('rate_type') ?></label><br/>
                    <div class="radio radio-primary radio-inline">
                    <input type="radio" name="rate_type" id="hourly" value="hourly" checked>
                    <label for="hourly"><?= _l('hourly') ?></label>
                    </div>
                    <div class="radio radio-primary radio-inline">
                    <input type="radio" name="rate_type" id="monthly" value="monthly">
                    <label for="monthly"><?= _l('monthly') ?></label>
                    </div>
                </div>

                <?= form_hidden('staff_id', $staff_p->staffid) ?>
                </div>

                <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?= _l('submit') ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= _l('close') ?></button>
                </div>

            <?= form_close(); ?>
            </div>
        </div>
    </div>
<?php init_tail(); ?>
<script>
    $(function() {
        var notifications = $('#notifications');
        if (notifications.length > 0) {
            var page = 0;
            var total_pages = '<?= e($total_pages); ?>';
            $('.loader').on('click', function(e) {
                e.preventDefault();
                if (page <= total_pages) {
                    $.post(admin_url + 'staff/notifications', {
                        page: page
                    }).done(function(response) {
                        response = JSON.parse(response);
                        var notifications = '';
                        $.each(response, function(i, obj) {
                            notifications +=
                                '<div class="notification-wrapper tw-bg-white hover:tw-bg-neutral-50" data-notification-id="' +
                                obj.id + '">';
                            notifications +=
                                '<div class="tw-px-4 tw-py-3 notification-handler notification-box-all' +
                                (
                                    obj
                                    .isread_inline == 0 ? ' unread-notification' : '') +
                                '">';
                            var link_notification = '';
                            var link_class_indicator = '';
                            if (obj.link) {
                                link_notification = ' data-link="' + admin_url + obj
                                    .link +
                                    '"';
                                link_class_indicator =
                                    ' notification_link tw-cursor-pointer';
                            }
                            notifications += obj.profile_image;
                            notifications += '<div class="media-body' +
                                link_class_indicator + '"' + link_notification + '>';
                            notifications += '<div class="description">';
                            if (obj.from_fullname) {
                                notifications += escapeHtml(obj.from_fullname) + ' - ';
                            }
                            notifications += obj.description;
                            notifications += '</div>';
                            notifications +=
                                '<span class="tw-text-sm text-right text-has-action notification-date" data-placement="right" data-toggle="tooltip" data-title="' +
                                obj.full_date + '">' + obj.date + '</span>';
                            if (obj.isread_inline == 0) {
                                notifications +=
                                    '<a href="#" class="text-muted pull-right not-mark-as-read-inline notification-profile" onclick="set_notification_read_inline(' +
                                    obj.id +
                                    ')" data-placement="left" data-toggle="tooltip" data-title="<?= _l('mark_as_read'); ?>"><small><i class="fa-regular fa-circle"></i></a></small>';
                            }
                            notifications += '</div>';
                            notifications += '</div>';
                            notifications += '</div>';
                        });

                        $('#notifications').append(notifications);
                        page++;
                    });

                    if (page >= total_pages - 1) {
                        $(".loader").addClass("disabled");
                    }
                }
            });

            $('.loader').click();
        }
    });

    $(document).on('click', '#assign-job-form button[type="submit"]', function() {
        $('#assign_job_modal').modal('hide');
        blockArea($('#wrapper'));
    
            // simulate unblock after ajax
            setTimeout(function(){
                unBlockArea($('#wrapper'));
            }, 2000);
    });

    $(document).on('click', '.edit-job, .edit-project', function () {
        const modal = $('#assign_job_modal');
        const data = $(this).data();

        modal.find('form').attr('action', '<?= admin_url('projects/assign_job') ?>');
            
        modal.find('select[name="project_id"]').val(data.project).change();
        modal.find('select[name="skills[]"]').val(data.skills.toString().split(',')).change();
        modal.find('input[name="badge"]').val(data.badge);
        modal.find('input[name="start_date"]').val(data.start);
        modal.find('input[name="end_date"]').val(data.end);
        modal.find('select[name="equipment"]').val(data.equipment).change();
        modal.find('input[name="regular_rate"]').val(data.regular);
        modal.find('input[name="overtime_rate"]').val(data.overtime);
        modal.find('input[name="rate_type"][value="' + data.type + '"]').prop('checked', true);

        modal.find('input[name="edit_id"]').remove();
        modal.find('form').append('<input type="hidden" name="edit_id" value="' + data.id + '">');

        modal.modal('show');
    });
</script>
</body>

</html>