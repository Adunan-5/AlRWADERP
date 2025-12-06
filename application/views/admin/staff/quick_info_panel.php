<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.quick-info-panel {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.quick-info-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e5e5;
}

.quick-info-photo {
    flex-shrink: 0;
}

.quick-info-photo img {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid #e5e5e5;
}

.quick-info-main {
    flex-grow: 1;
}

.quick-info-name {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin: 0 0 8px 0;
}

.quick-info-position {
    font-size: 14px;
    color: #666;
    margin: 0;
}

.quick-info-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.quick-info-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.quick-info-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
}

.quick-info-content {
    flex-grow: 1;
}

.quick-info-label {
    font-size: 11px;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin: 0 0 4px 0;
}

.quick-info-value {
    font-size: 14px;
    color: #333;
    font-weight: 500;
    margin: 0;
}

.quick-info-value.empty {
    color: #ccc;
    font-style: italic;
}

/* Badge styles for certain fields */
.quick-info-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-primary {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-success {
    background: #e8f5e9;
    color: #388e3c;
}

.badge-info {
    background: #e1f5fe;
    color: #0288d1;
}

@media (max-width: 768px) {
    .quick-info-header {
        flex-direction: column;
        text-align: center;
    }

    .quick-info-details {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="quick-info-panel">
    <div class="quick-info-header">
        <div class="quick-info-photo">
            <?php if ($member->profile_image != null): ?>
                <?= staff_profile_image($member->staffid, ['img', 'img-responsive'], 'thumb'); ?>
            <?php else: ?>
                <img src="<?= base_url('assets/images/user-placeholder.jpg'); ?>" alt="<?= e($member->name); ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23f0f0f0%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2240%22 fill=%22%23999%22%3E<?= substr($member->name, 0, 1); ?><?= isset(explode(' ', $member->name)[1]) ? substr(explode(' ', $member->name)[1], 0, 1) : ''; ?><?= '%3C/text%3E%3C/svg%3E'; ?>">
            <?php endif; ?>
        </div>
        <div class="quick-info-main">
            <h3 class="quick-info-name"><?= e($member->name); ?></h3>
            <p class="quick-info-position">
                <?php if (!empty($company_type_name)): ?>
                    Working at <strong><?= e($company_type_name); ?></strong><?php if (!empty($profession_type_name)): ?>, as <strong><?= e($profession_type_name); ?></strong><?php endif; ?>
                <?php elseif (!empty($profession_type_name)): ?>
                    <strong><?= e($profession_type_name); ?></strong>
                <?php else: ?>
                    Employee
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="quick-info-details">
        <!-- Iqama Number -->
        <div class="quick-info-item">
            <div class="quick-info-icon">
                <i class="fa fa-id-card"></i>
            </div>
            <div class="quick-info-content">
                <p class="quick-info-label">Iqama Number</p>
                <p class="quick-info-value <?= empty($member->iqama_number) ? 'empty' : ''; ?>">
                    <?= !empty($member->iqama_number) ? e($member->iqama_number) : 'N/A'; ?>
                </p>
            </div>
        </div>

        <!-- Passport Number -->
        <div class="quick-info-item">
            <div class="quick-info-icon">
                <i class="fa fa-passport"></i>
            </div>
            <div class="quick-info-content">
                <p class="quick-info-label">Passport</p>
                <p class="quick-info-value <?= empty($member->passport_number) ? 'empty' : ''; ?>">
                    <?= !empty($member->passport_number) ? e($member->passport_number) : 'N/A'; ?>
                </p>
            </div>
        </div>

        <!-- Current Project -->
        <div class="quick-info-item">
            <div class="quick-info-icon">
                <i class="fa fa-briefcase"></i>
            </div>
            <div class="quick-info-content">
                <p class="quick-info-label">Current Project</p>
                <p class="quick-info-value <?= empty($project_name) ? 'empty' : ''; ?>">
                    <?php if (!empty($project_name) && !empty($member->project_id)): ?>
                        <a href="<?= admin_url('projects/view/' . $member->project_id); ?>" class="quick-info-badge badge-primary">
                            <?= e($project_name); ?>
                        </a>
                    <?php else: ?>
                        <span class="empty">Not Assigned</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Skills/Profession Type -->
        <div class="quick-info-item">
            <div class="quick-info-icon">
                <i class="fa fa-wrench"></i>
            </div>
            <div class="quick-info-content">
                <p class="quick-info-label">Skills</p>
                <p class="quick-info-value <?= empty($profession_type_name) ? 'empty' : ''; ?>">
                    <?= !empty($profession_type_name) ? '<span class="quick-info-badge badge-info">' . e($profession_type_name) . '</span>' : '<span class="empty">N/A</span>'; ?>
                </p>
            </div>
        </div>

        <!-- Joined On -->
        <div class="quick-info-item">
            <div class="quick-info-icon">
                <i class="fa fa-calendar"></i>
            </div>
            <div class="quick-info-content">
                <p class="quick-info-label">Joined On</p>
                <p class="quick-info-value <?= empty($member->joining_date) ? 'empty' : ''; ?>">
                    <?= !empty($member->joining_date) ? date('Y-m-d', strtotime($member->joining_date)) : 'N/A'; ?>
                </p>
            </div>
        </div>

        <!-- Age -->
        <div class="quick-info-item">
            <div class="quick-info-icon">
                <i class="fa fa-birthday-cake"></i>
            </div>
            <div class="quick-info-content">
                <p class="quick-info-label">Age</p>
                <p class="quick-info-value <?= empty($age) ? 'empty' : ''; ?>">
                    <?= !empty($age) ? e($age) : 'N/A'; ?>
                </p>
            </div>
        </div>

        <!-- Basic Pay -->
        <div class="quick-info-item">
            <div class="quick-info-icon">
                <i class="fa fa-money"></i>
            </div>
            <div class="quick-info-content">
                <p class="quick-info-label">Basic Pay</p>
                <p class="quick-info-value <?= empty($current_basic_pay) ? 'empty' : ''; ?>">
                    <?php if (!empty($current_basic_pay)): ?>
                        <span class="quick-info-badge badge-success">
                            <?= isset($base_currency) ? e($base_currency->symbol) : ''; ?><?= number_format($current_basic_pay, 2); ?>
                        </span>
                    <?php else: ?>
                        <span class="empty">N/A</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Visa (if available) -->
        <?php if (isset($member->visa_number) && !empty($member->visa_number)): ?>
        <div class="quick-info-item">
            <div class="quick-info-icon">
                <i class="fa fa-file-text"></i>
            </div>
            <div class="quick-info-content">
                <p class="quick-info-label">Visa</p>
                <p class="quick-info-value">
                    <?= e($member->visa_number); ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
