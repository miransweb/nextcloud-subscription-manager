<?php
script('subscriptionmanager', 'script');
style('subscriptionmanager', 'style');
?>

<div id="subscription-manager">
    <div class="subscription-header">
        <h2><?php p($l->t('Subscription Status')); ?></h2>
    </div>

    <?php if (isset($_['error'])): ?>
        <div class="error">
            <p><?php p($_['error']); ?></p>
        </div>
    <?php endif; ?>

    <div class="subscription-content">
        <div class="status-card">
            <div class="status-info">
                <h3><?php p($l->t('Account Type')); ?></h3>
                <p class="status-type <?php p($_['subscription']['status']); ?>">
                    <?php if ($_['subscription']['is_trial']): ?>
                        <span class="icon-info"></span>
                        <?php p($l->t('Trial Account')); ?>
                    <?php else: ?>
                        <span class="icon-checkmark"></span>
                        <?php p($l->t('Premium Account')); ?>
                    <?php endif; ?>
                </p>
            </div>

            <div class="quota-info">
                <h3><?php p($l->t('Storage Quota')); ?></h3>
                <p class="quota-amount"><?php p($_['subscription']['quota']); ?></p>
            </div>

            <?php if ($_['subscription']['expires_at']): ?>
                <div class="expiry-info">
                    <h3><?php p($l->t('Expires')); ?></h3>
                    <p><?php p(date('F j, Y', strtotime($_['subscription']['expires_at']))); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($_['subscription']['is_trial'] && $_['subscription']['trial_days_remaining'] !== null): ?>
                <div class="trial-days-info">
                    <h3><?php p($l->t('Trial Period')); ?></h3>
                    <?php if ($_['subscription']['trial_days_remaining'] > 0): ?>
                        <p class="trial-days-count">
                            <?php p($l->n(
                                '%n day remaining',
                                '%n days remaining',
                                $_['subscription']['trial_days_remaining']
                            )); ?>
                        </p>
                    <?php else: ?>
                        <p class="trial-expired">
                            <?php p($l->t('Trial expired')); ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="action-section">
            <?php if ($_['subscription']['is_trial']): ?>
                <div class="trial-message">
                    <p><?php p($l->t('You are currently using a trial account with limited storage.')); ?></p>
                    <?php if ($_['subscription']['trial_days_remaining'] !== null && $_['subscription']['trial_days_remaining'] <= 7): ?>
                        <p class="trial-warning">
                            <span class="icon-warning"></span>
                            <?php if ($_['subscription']['trial_days_remaining'] === 0): ?>
                                <?php p($l->t('Your trial has expired. Upgrade now to continue using your account.')); ?>
                            <?php else: ?>
                                <?php p($l->t('Your trial expires soon. Upgrade now to avoid interruption.')); ?>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p><?php p($l->t('Upgrade to a premium subscription to get more storage and features.')); ?></p>
                    <?php endif; ?>
                </div>
                <a href="<?php p($_['webshopUrl']); ?>" class="button primary" target="_blank">
                    <span class="icon-external"></span>
                    <?php p($l->t('Upgrade to Premium')); ?>
                </a>
            <?php else: ?>
                <div class="premium-message">
                    <p><?php p($l->t('Thank you for being a premium subscriber!')); ?></p>
                    <?php if ($_['subscription']['subscription_id']): ?>
                        <p class="subscription-id">
                            <?php p($l->t('Subscription ID')); ?>: 
                            <strong>#<?php p($_['subscription']['subscription_id']); ?></strong>
                        </p>
                    <?php endif; ?>
                </div>
                <a href="<?php p($_['webshopUrl']); ?>" class="button" target="_blank">
                    <span class="icon-external"></span>
                    <?php p($l->t('Manage Subscription')); ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <h3><?php p($l->t('How it works')); ?></h3>
            <ul>
                <?php if ($_['subscription']['is_trial']): ?>
                    <li><?php p($l->t('Click "Upgrade to Premium" to visit our webshop')); ?></li>
                    <li><?php p($l->t('Your account details will be automatically linked')); ?></li>
                    <li><?php p($l->t('Choose a subscription plan that fits your needs')); ?></li>
                    <li><?php p($l->t('Your storage quota will be updated automatically')); ?></li>
                <?php else: ?>
                    <li><?php p($l->t('Click "Manage Subscription" to access your account')); ?></li>
                    <li><?php p($l->t('Upgrade or downgrade your plan anytime')); ?></li>
                    <li><?php p($l->t('View payment history and invoices')); ?></li>
                    <li><?php p($l->t('Update payment methods')); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>