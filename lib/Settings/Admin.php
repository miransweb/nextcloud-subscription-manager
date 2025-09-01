<?php
namespace OCA\SubscriptionManager\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCA\SubscriptionManager\AppInfo\Application;
use OCP\AppFramework\Services\IAppConfig;

class Admin implements ISettings {
    private IAppConfig $appConfig;

    public function __construct(IAppConfig $appConfig) {
        $this->appConfig = $appConfig;
    }

    public function getForm(): TemplateResponse {
        $parameters = [
            'webshop_url' => $this->appConfig->getAppValue('webshop_url', 'https://webshop.example.com'),
            'deployer_api_url' => $this->appConfig->getAppValue('deployer_api_url', ''),
            'deployer_api_key' => $this->appConfig->getAppValue('deployer_api_key', ''),
            'shared_secret' => $this->appConfig->getAppValue('shared_secret', ''),
            'default_trial_days' => $this->appConfig->getAppValue('default_trial_days', '14'),
        ];

        return new TemplateResponse(Application::APP_ID, 'admin', $parameters);
    }

    public function getSection(): string {
        return 'additional';
    }

    public function getPriority(): int {
        return 50;
    }
}