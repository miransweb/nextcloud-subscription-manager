<?php
namespace OCA\SubscriptionManager\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCA\SubscriptionManager\Service\SubscriptionService;
use OCA\SubscriptionManager\AppInfo\Application;

class PageController extends Controller {
    private SubscriptionService $service;

    public function __construct(
        IRequest $request,
        SubscriptionService $service
    ) {
        parent::__construct(Application::APP_ID, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index(): TemplateResponse {
        try {
            $status = $this->service->getSubscriptionStatus();
            $webshopUrl = $this->service->getWebshopUrl($status['is_trial'] ? 'subscribe' : 'manage');
            
            $params = [
                'subscription' => $status,
                'webshopUrl' => $webshopUrl
            ];
        } catch (\Exception $e) {
            $params = [
                'error' => $e->getMessage(),
                'subscription' => [
                    'status' => 'unknown',
                    'is_trial' => true
                ],
                'webshopUrl' => '#'
            ];
        }

        return new TemplateResponse(Application::APP_ID, 'index', $params);
    }
}