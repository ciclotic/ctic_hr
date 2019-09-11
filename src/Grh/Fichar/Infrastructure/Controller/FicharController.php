<?php
namespace CTIC\Grh\Fichar\Infrastructure\Controller;

use CTIC\App\Base\Domain\EntityActions;
use CTIC\App\Base\Infrastructure\Controller\ResourceController;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\App\Base\Infrastructure\View\ResourceGridView;
use CTIC\App\Base\Infrastructure\View\Rest\View;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Fichar\Application\CreateFichar;
use CTIC\Grh\Fichar\Domain\Command\FicharCommand;
use CTIC\Grh\Fichar\Domain\Fichar;
use CTIC\Grh\Fichar\Infrastructure\Repository\FicharRepository;
use CTIC\Grh\Report\Domain\ReportFilter;
use Sylius\Component\Grid\Parameters;
use CTIC\App\Base\Infrastructure\View\GridView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FicharController extends ResourceController
{

    /**
     * @var FicharRepository
     */
    protected $repository;

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function availableAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if(!$this->isGrantedOr403($configuration))
        {
            return $this->redirectToLogin($configuration);
        }

        $reportFilter = new ReportFilter();
        $fromDate = new \DateTime('now');
        $fromDate->modify('-1 day');
        $reportFilter->fromDate = $fromDate;
        $reportFilter->toDate = new \DateTime('now');
        $reportFilter->toDate->modify('+1 day');
        $resources = $this->repository->findByRangeDate($reportFilter, 'DESC');
        $resourcesToReturn = array();
        foreach ($resources as $resource) {
            $employeeId = $resource->getEmployee()->getId();
            if (empty($resourcesToReturn[$employeeId])) {
                if ($resource->getAction() == Fichar::IN) {
                    $resourcesToReturn[$employeeId] = $resource;
                } else {
                    $resourcesToReturn[$employeeId] = 'to delete';
                }
            }
        }
        foreach ($resourcesToReturn as $key => $resourceToReturn) {
            if ($resourceToReturn == 'to delete') {
                unset($resourcesToReturn[$key]);
            }
        }

        $this->eventDispatcher->dispatchMultiple(EntityActions::INDEX, $configuration, $resources);

        $view = View::create($resourceToReturn);

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate($this->getResourceName() . '/listAvailable.html'))
                ->setTemplateVar($this->metadata->getPluralName())
                ->setData([
                    'configuration'                     => $configuration,
                    'metadata'                          => $this->metadata,
                    'resources'                         => $resourcesToReturn,
                    $this->metadata->getPluralName()    => $resources,
                ])
            ;
        }

        return $this->viewHandler->handle($view, $configuration->getRequest());
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if(!$this->isGrantedOr403($configuration))
        {
            return $this->redirectToLogin($configuration);
        }
        $parametersToAdd = array();
        if ($this->metadata->hasParameter('grid')) {
            $parametersGrid = $this->metadata->getParameter('grid');
            foreach ($parametersGrid as $key => $parameterGrid)
            {
                $parametersToAdd[$key] = $parameterGrid;
            }
        }
        $parameters = new Parameters($parametersToAdd);
        /** @var ResourceGridView $resources */
        $resources = $this->resourcesCollectionProvider->get($configuration, $this->repository, $parameters);

        $this->eventDispatcher->dispatchMultiple(EntityActions::INDEX, $configuration, $resources);

        $view = View::create($resources);

        if ($configuration->isHtmlRequest()) {
            $parametersToGridView = new Parameters($configuration->getParameters()->all());
            $gridView = new GridView($resources, $resources->getDefinition(), $parametersToGridView);
            $view
                ->setTemplate($configuration->getTemplate($this->getResourceName() . '/' . EntityActions::INDEX . '.html'))
                ->setTemplateVar($this->metadata->getPluralName())
                ->setData([
                    'configuration'                     => $configuration,
                    'metadata'                          => $this->metadata,
                    'resources'                         => $resources,
                    'gridview'                          => $gridView,
                    $this->metadata->getPluralName()    => $resources,
                ])
            ;
        }

        return $this->viewHandler->handle($view, $configuration->getRequest());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws
     */
    public function ficharAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $action = 'fichar';

        if(!$this->isGrantedOr403($configuration))
        {
            return $this->redirectToLogin($configuration);
        }

        $employee = null;
        $inOut = 1;
        $date = '';
        if ($request->isMethod('POST')) {
            $barcode = $request->get('barcode');
            $inOut = $request->get('inOut');
            $latitude = $request->get('latitude');
            $longitude = $request->get('longitude');

            if (!empty($barcode)) {
                $employeeRepository = $this->manager->getRepository(Employee::class);
                /** @var Employee $employee */
                $employee = $employeeRepository->findOneBy(array('barcode' => $barcode));
            }

            if (is_object($employee) && get_class($employee) == Employee::class) {
                $identity = $request->getSession()->get('identity');

                if (!empty($identity) && !empty($identity['roles']) && !empty($identity['id'])) {
                    $roles = $identity['roles'];
                    if (!empty($roles[0]) && $roles[0] == 3) {
                        $userRelated = $employee->getUserRelated();

                        if (empty($userRelated) || $userRelated->getId() != $identity['id']) {
                            $view = View::create();

                            if ($configuration->isHtmlRequest()) {
                                $view
                                    ->setTemplate($configuration->getTemplate($action. '.html'))
                                    ->setTemplateVar($this->metadata->getPluralName())
                                    ->setData([
                                        'configuration' => $configuration,
                                        'metadata' => $this->metadata,
                                        'employee'  => null,
                                        'inOut' => 1,
                                        'date' => ''
                                    ])
                                ;
                            }

                            return $this->viewHandler->handle($view, $configuration->getRequest());
                        }
                    }
                }


                $newResourceCommand = new FicharCommand();
                $newResourceCommand->date = new \DateTime();
                $newResourceCommand->employee = $employee;
                $newResourceCommand->action = $inOut;
                $newResourceCommand->latitude = $latitude;
                $newResourceCommand->longitude = $longitude;
                $newResource = CreateFichar::create($newResourceCommand);

                $this->repository->add($newResource);

                $date = $newResourceCommand->date->format('Y-m-d H:i:s');
            }
        }

        $view = View::create();

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate($action. '.html'))
                ->setTemplateVar($this->metadata->getPluralName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'employee'  => $employee,
                    'inOut' => $inOut,
                    'date' => $date
                ])
            ;
        }

        return $this->viewHandler->handle($view, $configuration->getRequest());
    }
}