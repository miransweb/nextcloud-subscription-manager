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
        $results = [
            'deployer_api' => false,
            'webshop_reachable' => false,
            'error' => null
        ];

        try {
            // Test Deployer API
            $deployerUrl = $this->appConfig->getAppValue('deployer_api_url', '');
            $deployerKey = $this->appConfig->getAppValue('deployer_api_key', '');

            if (!empty($deployerUrl) && !empty($deployerKey)) {
                $ch = curl_init($deployerUrl . '/health');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $deployerKey,
                        'Content-Type: application/json'
                    ]
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $results['deployer_api'] = ($httpCode === 200);
            }

            // Test Webshop
            $webshopUrl = $this->appConfig->getAppValue('webshop_url', '');

            if (!empty($webshopUrl)) {
                $ch = curl_init($webshopUrl);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_NOBODY => true
                ]);

                curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $results['webshop_reachable'] = ($httpCode >= 200 && $httpCode < 400);
            }

        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }

        return new JSONResponse($results);
    }
}