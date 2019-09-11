<?php
namespace CTIC\Grh\Employee\Infrastructure\Controller;

use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\Base\Infrastructure\Controller\ResourceController;
use CTIC\App\Base\Infrastructure\Request\RequestConfiguration;
use CTIC\Grh\Employee\Domain\EmployeeLow;
use CTIC\App\User\Domain\User;

class EmployeeLowController extends ResourceController
{
    /**
     * @param EntityInterface|EmployeeLow $resource
     * @param RequestConfiguration $configuration
     */
    protected function completeCreateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $this->prepareCreateEntity($resource, $configuration);
    }

    /**
     * @param EntityInterface|EmployeeLow $resource
     * @param RequestConfiguration $configuration
     */
    protected function completeUpdateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
    }

    /**
     * @param EntityInterface|EmployeeLow $resource
     * @param RequestConfiguration $configuration
     */
    protected function prepareUpdateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
    }

    /**
     * @param EntityInterface|EmployeeLow $resource
     * @param RequestConfiguration $configuration
     */
    protected function prepareCreateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $identity = $configuration->getRequest()->getSession()->get('identity');
        if (!empty($identity) && !empty($identity['data']) && !empty($identity['data']['id']))
        {
            $userRespository = $this->manager->getRepository(User::class);

            /** @var User $userCreator */
            $userCreator = $userRespository->find($identity['data']['id']);

            $resource->setUserCreator($userCreator);
        }
    }
}