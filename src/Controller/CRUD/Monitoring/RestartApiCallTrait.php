<?php

namespace Smart\SonataBundle\Controller\CRUD\Monitoring;

use Smart\CoreBundle\Entity\ApiCallInterface;
use Smart\CoreBundle\Monitoring\ApiCallMonitor;
use Smart\SonataBundle\Admin\AbstractAdmin;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 * @property AbstractAdmin $admin
 */
trait RestartApiCallTrait
{
    public function restartApiCallAction(ApiCallMonitor $apiCallMonitor): Response
    {
        try {
            /** @var ApiCallInterface $apiCall */
            $apiCall = $this->admin->getSubject();
            $apiCallMonitor->restart($apiCall);
            $this->addFlash('sonata_flash_success', $this->trans('monitoring.restart_api_call.success', [], 'admin'));
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $this->trans('monitoring.restart_api_call.error', [], 'admin') . $e->getMessage());
        } finally {
            return  $this->redirectToList();
        }
    }
}
