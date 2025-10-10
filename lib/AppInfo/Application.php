<?php
namespace OCA\SubscriptionManager\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
    public const APP_ID = 'subscriptionmanager';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // Register admin section
        $context->registerSettingsSection(\OCA\SubscriptionManager\Settings\AdminSection::class);

        // Register admin settings
        $context->registerSettings(\OCA\SubscriptionManager\Settings\Admin::class);
    }

    public function boot(IBootContext $context): void {
        // Boot logic
    }
}