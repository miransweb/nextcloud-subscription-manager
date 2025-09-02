<?php
namespace OCA\SubscriptionManager\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Services\IAppConfig;
use OCA\SubscriptionManager\AppInfo\Application;

class AdminController extends Controller {
    private IAppConfig $appConfig;

    public function __construct(
        IRequest $request,
        IAppConfig $appConfig
    ) {
        parent::__construct(Application::APP_ID, $request);
        $this->appConfig = $appConfig;
    }

    /**
     * @NoCSRFRequired
     */
    public function saveSettings(): JSONResponse {
        try {
            $settings = $this->request->getParams();
            
            // Save each setting
            foreach (['webshop_url', 'deployer_api_url', 'deployer_api_key', 'shared_secret', 'default_trial_days'] as $key) {
                if (isset($settings[$key])) {
                    $this->appConfig->setAppValue($key, $settings[$key]);
                }
            }
            
            return new JSONResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JSONResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoCSRFRequired
     */
    public function testConnection(): JSONResponse {
        // Implement connection testing logic here
        return new JSONResponse([
            'deployer_api' => false,
            'webshop_reachable' => false,
            'error' => 'Test not implemented yet'
        ]);
    }
}