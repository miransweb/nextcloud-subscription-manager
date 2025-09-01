<?php
namespace OCA\SubscriptionManager\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\SubscriptionManager\Service\SubscriptionService;
use OCA\SubscriptionManager\AppInfo\Application;

class ApiController extends Controller {
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
     */
    public function getStatus(): JSONResponse {
        try {
            $status = $this->service->getSubscriptionStatus();
            return new JSONResponse($status);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function getWebshopUrl(): JSONResponse {
        try {
            $action = $this->request->getParam('action', 'subscribe');
            $url = $this->service->getWebshopUrl($action);
            return new JSONResponse(['url' => $url]);
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 500);
        }
    }
}