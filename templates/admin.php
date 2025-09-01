<?php
script('subscriptionmanager', 'admin');
style('subscriptionmanager', 'admin');
?>

<div id="subscriptionmanager-admin" class="section">
    <h2><?php p($l->t('Subscription Manager Settings')); ?></h2>
    
    <div class="settings-section">
        <h3><?php p($l->t('Webshop Configuration')); ?></h3>
        
        <div class="form-group">
            <label for="webshop-url"><?php p($l->t('Webshop URL')); ?></label>
            <input type="url" 
                   id="webshop-url" 
                   name="webshop_url" 
                   value="<?php p($_['webshop_url']); ?>"
                   placeholder="https://webshop.example.com" />
            <p class="settings-hint">
                <?php p($l->t('The URL of your WooCommerce webshop')); ?>
            </p>
        </div>
        
        <div class="form-group">
            <label for="deployer-api-url"><?php p($l->t('Deployer API URL')); ?></label>
            <input type="url" 
                   id="deployer-api-url" 
                   name="deployer_api_url" 
                   value="<?php p($_['deployer_api_url']); ?>"
                   placeholder="https://deployer.example.com" />
            <p class="settings-hint">
                <?php p($l->t('The URL of your Deployer API service')); ?>
            </p>
        </div>
        
        <div class="form-group">
            <label for="deployer-api-key"><?php p($l->t('Deployer API Key')); ?></label>
            <input type="password" 
                   id="deployer-api-key" 
                   name="deployer_api_key" 
                   value="<?php p($_['deployer_api_key']); ?>"
                   placeholder="••••••••••••••••" />
            <p class="settings-hint">
                <?php p($l->t('Your Deployer API authentication key')); ?>
            </p>
        </div>
        
        <div class="form-group">
            <label for="shared-secret"><?php p($l->t('Shared Secret')); ?></label>
            <input type="password" 
                   id="shared-secret" 
                   name="shared_secret" 
                   value="<?php p($_['shared_secret']); ?>"
                   placeholder="••••••••••••••••" />
            <button type="button" id="generate-secret" class="button">
                <?php p($l->t('Generate')); ?>
            </button>
            <p class="settings-hint">
                <?php p($l->t('Shared secret for secure communication with the webshop')); ?>
            </p>
        </div>
        
        <div class="form-group">
            <label for="default-trial-days"><?php p($l->t('Default Trial Days')); ?></label>
            <input type="number" 
                   id="default-trial-days" 
                   name="default_trial_days" 
                   value="<?php p($_['default_trial_days'] ?? 14); ?>"
                   min="1"
                   max="365" />
            <p class="settings-hint">
                <?php p($l->t('Number of days for trial period (used as fallback when not provided by API)')); ?>
            </p>
        </div>
        
        <div class="form-actions">
            <button id="subscriptionmanager-save" class="button primary">
                <?php p($l->t('Save')); ?>
            </button>
            <span class="msg"></span>
        </div>
    </div>
    
    <div class="settings-section">
        <h3><?php p($l->t('Integration Status')); ?></h3>
        <div id="integration-status">
            <p><?php p($l->t('Click "Test Connection" to verify your settings')); ?></p>
            <button id="test-connection" class="button">
                <?php p($l->t('Test Connection')); ?>
            </button>
            <div id="test-results"></div>
        </div>
    </div>
</div>