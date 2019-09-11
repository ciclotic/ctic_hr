<?php
namespace CTIC\Grh\Employee\Infrastructure\Controller;

use CTIC\App\Base\Domain\EntityActions;
use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\Base\Infrastructure\Controller\Command\ResourceControllerCommand;
use CTIC\App\Base\Infrastructure\Controller\ResourceController;
use CTIC\App\Base\Infrastructure\File\Uploader;
use CTIC\App\Base\Infrastructure\Request\RequestConfiguration;
use CTIC\App\Base\Infrastructure\View\Rest\View;
use CTIC\Grh\Employee\Application\CreateEmployeeAttached;
use CTIC\Grh\Employee\Domain\Command\EmployeeAttachedCommand;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Employee\Domain\EmployeeAttached;
use CTIC\Grh\Employee\Domain\EmployeeAttachedInterface;
use CTIC\Grh\Employee\Domain\EmployeeGrowth;
use CTIC\Grh\Employee\Infrastructure\Form\Type\EmployeeGrowthType;
use CTIC\Grh\Employee\Infrastructure\Repository\EmployeeAttachedRepository;
use CTIC\App\User\Domain\User;
use Doctrine\Common\Collections\ArrayCollection;
use Mpdf\Mpdf;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends ResourceController
{
    /**
     * @var Employee|null
     */
    protected $resource;
    /**
     * @var array|null
     */
    protected $oldEmployeeAttacheds;

    protected $attachedPath;

    public function __construct(ResourceControllerCommand $command)
    {
        parent::__construct($command);

        $this->attachedPath = getcwd() . EmployeeAttachedInterface::ATTACHED_PATH;
    }

    /**
     * @param EntityInterface|Employee $resource
     * @param RequestConfiguration $configuration
     *
     * @return bool
     *
     * @throws
     */
    protected function uploadAttached(EntityInterface $resource, RequestConfiguration $configuration): bool
    {
        $session = $configuration->getRequest()->getSession();
        $dbName = $session->get('dbName');
        if (empty($dbName)) {
            throw new \Exception('Cannot get dbName to employee attach');
        }
        $employeeId = $resource->getId();
        $addedToAttachedPath = '/' . $dbName . '/' . $employeeId;
        $attachedPath = $this->attachedPath . $addedToAttachedPath;

        $uploader = new Uploader($attachedPath);
        $employeeAttacheds = $resource->getAttached();
        $resource->setAttached(new ArrayCollection());

        if(empty($employeeAttacheds))
        {
            return false;
        }

        $newEmployeeAttach = new ArrayCollection();
        foreach ($employeeAttacheds as $key => $employeeAttached) {
            if (empty($employeeAttached)) {
                continue;
            }
            $attached = $employeeAttached->getAttached();
            if ((is_object($attached) && get_class($attached) == File::class && $attached->getFilename() == 'control.pdf') ||
                empty($attached['tmp_name']) ||
                empty($attached['name']) ||
                empty($attached['type'])
            ) {
                if (!empty($this->oldEmployeeAttacheds[$key])) {
                    $employeeAttached->setAttached($this->oldEmployeeAttacheds[$key]);
                    $employeeAttached->employee = $resource;
                }
                $newEmployeeAttach->add($employeeAttached);
                continue;
            }
            $attachedFile = new UploadedFile($attached['tmp_name'], $attached['name'], $attached['type']);
            $fileName = $uploader->upload($attachedFile);

            $attachedCommand = new EmployeeAttachedCommand();
            $attachedCommand->attached = $addedToAttachedPath . '/' . $fileName;
            $attachedCommand->employee = $resource;

            $newAttached = CreateEmployeeAttached::create($attachedCommand);

            if (!empty($employeeAttacheds)) {
                $newEmployeeAttach->add($newAttached);
            }
        }

        /** @var EmployeeAttachedRepository $employeeAttachRepository */
        $employeeAttachRepository = $this->manager->getRepository(EmployeeAttached::class);
        foreach ($this->oldEmployeeAttacheds as $oldEmployeeAttached) {
            $inNew = false;
            /** @var EmployeeAttached $employeeAttach */
            foreach ($newEmployeeAttach as $employeeAttach) {
                $newAttached = $employeeAttach->getAttached();
                if (is_object($newAttached) && get_class($newAttached) == File::class) {
                    $newAttached = $newAttached->getFilename();
                }
                if ($oldEmployeeAttached == $newAttached) {
                    $inNew = true;
                }
            }
            if ($inNew == false) {
                $employeeAttached = $employeeAttachRepository->findOneBy(array('attached' => $oldEmployeeAttached));
                if (!empty($employeeAttached)) {
                    $this->manager->remove($employeeAttached);
                    $this->manager->flush();
                }
            }
        }

        $resource->setAttached($newEmployeeAttach);

        return true;
    }

    /**
     * @param EntityInterface|Employee $resource
     * @param RequestConfiguration $configuration
     */
    protected function completeCreateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $this->prepareCreateEntity($resource, $configuration);
    }

    /**
     * @param EntityInterface|Employee $resource
     * @param RequestConfiguration $configuration
     *
     * @throws
     */
    protected function completeUpdateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $this->uploadAttached($resource, $configuration);
    }

    /**
     * @param EntityInterface|Employee $resource
     * @param RequestConfiguration $configuration
     *
     * @throws
     */
    protected function prepareUpdateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $session = $configuration->getRequest()->getSession();
        $dbName = $session->get('dbName');
        if (empty($dbName)) {
            throw new \Exception('Cannot get dbName to employee attach');
        }
        $employeeId = $resource->getId();
        $addedToAttachedPath = '/' . $dbName . '/' . $employeeId;

        $employeeAttacheds = $resource->getAttached();
        foreach ($employeeAttacheds as $key => $employeeAttached) {
            $this->oldEmployeeAttacheds[$key] = $employeeAttached->getAttached();
            if (is_object($this->oldEmployeeAttacheds[$key]) && get_class($this->oldEmployeeAttacheds[$key]) == File::class) {
                $this->oldEmployeeAttacheds[$key] = $addedToAttachedPath . '/' .$this->oldEmployeeAttacheds[$key]->getFilename();
            }
        }
    }

    /**
     * @param EntityInterface|Employee $resource
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

    /**
     * @param EntityInterface|Employee $resource
     * @param RequestConfiguration $configuration
     */
    protected function postCreateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
        $this->uploadAttached($resource, $configuration);

        $this->repository->add($resource);
    }

    /**
     * @param EntityInterface|Employee $resource
     * @param RequestConfiguration $configuration
     */
    protected function postUpdateEntity(EntityInterface $resource, RequestConfiguration $configuration): void
    {
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function growthAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        if(!$this->isGrantedOr403($configuration))
        {
            return $this->redirectToLogin($configuration);
        }
        $newResource = new EmployeeGrowth();

        $form = $this->resourceFormFactory->create($configuration, $newResource, EmployeeGrowthType::class);

        if ($request->isMethod('POST') && $form->handleRequest()->isValid()) {
            /** @var EmployeeGrowth $newResource */
            $newResource = $form->getData();

            try{
                $mpdf = new Mpdf();
                $employees = $newResource->employee;
                $session = $request->getSession();
                $company = $session->get('company');
                $taxName = $company['taxName'];
                $taxIdentification = $company['taxIdentification'];
                $administratorName = $company['administratorName'];
                $administratorIdentification = $company['administratorIdentification'];
                $address = $company['address'];
                //$postalCode = $company['postalCode'];
                $town = $company['town'];
                //$country = $company['country'];
                $ccc = $company['ccc'];
                if (!empty($employees)) {
                    /** @var Employee $employee */
                    foreach ($employees as $employee) {
                        $employeeName = $employee->getName();
                        $employeeSurname = $employee->getSurname();
                        $employeeDni = $employee->getDni();
                        $employeeSocialSecurity = $employee->getSocialSecurity();
                        $growthDate = $newResource->fromDate->format('d') . ' de ' . $newResource->fromDate->format('m') . ' de ' . $newResource->fromDate->format('Y');
                        $hours = $newResource->hours;
                        $contractualDate = $newResource->contractualDate->format('d') . ' de ' . $newResource->contractualDate->format('m') . ' de ' . $newResource->contractualDate->format('Y');
                        $mpdf->AddPage();
                        /**
                         *'<br><br><h1 style="font-size: 14px; font-weight: bold; text-decoration: underline; text-align: center;">NOVACIÓ CONTRACTUAL</h1><br><br>' .
                        '<div>D’una banda <b>DANIEL CASTELLANO CARDOSO</b>, provist de DNI 46607773K actuant com administrador de la empresa POTENKIM EVENTS,S.L. amb CIF B64975006 i CCC 08/168018372 i domicili social a Manresa, Avda. Els Dolors, nº 17,</div><br><br>' .
                        '<div>D’una altre banda <b>' . $employeeName . ' ' . $employeeSurname . '</b> provista de DNI ' . $employeeDni . ' i NAF ' . $employeeSocialSecurity . '</div><br><br>' .
                        '<h1 style="font-size: 14px; font-weight: bold; text-decoration: underline; text-align: center;">ACORDEN</h1><br><br>' .
                        '<div>Que <b>a partir del dia ' . $growthDate . '</b> es produeix una novació contractual, consistent en una modificació de jornada de treball passant a una jornada parcial de ' . $hours . ' hores/mes; (horari establert en annex al contracte de treball, clàusula 2ª).</div><br><br>' .
                        '<div>Per  aquest motiu empresa  i  treballador signen en conformitat,</div><br><br>' .
                        '<div>Manresa a ' . $contractualDate . '</div><br><br>' .
                        '<div>El/La Treballador/a&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empresa</span></div>'
                         */
                        $mpdf->WriteHTML('<br><br><h1 style="font-size: 14px; font-weight: bold; text-decoration: underline; text-align: center;">NOVACIÓ CONTRACTUAL</h1><br><br>' .
                            '<div>D’una banda <b>' . $administratorName . '</b>, provist de DNI ' . $administratorIdentification . ' actuant com administrador de la empresa ' . $taxName . ' amb CIF ' . $taxIdentification . ' i CCC ' . $ccc . ' i domicili social a ' . $town . ', ' . $address . ',</div><br><br>' .
                            '<div>D’una altre banda <b>' . $employeeName . ' ' . $employeeSurname . '</b> provista de DNI ' . $employeeDni . ' i NAF ' . $employeeSocialSecurity . '</div><br><br>' .
                            '<h1 style="font-size: 14px; font-weight: bold; text-decoration: underline; text-align: center;">ACORDEN</h1><br><br>' .
                            '<div>Que <b>a partir del dia ' . $growthDate . '</b> es produeix una novació contractual, consistent en una modificació de jornada de treball passant a una jornada parcial de ' . $hours . ' hores/mes; (horari establert en annex al contracte de treball, clàusula 2ª).</div><br><br>' .
                            '<div>Per  aquest motiu empresa  i  treballador signen en conformitat,</div><br><br>' .
                            '<div>' . $town . ' a ' . $contractualDate . '</div><br><br>' .
                            '<div>El/La Treballador/a&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empresa</span></div>'
                        );
                    }
                }
                $mpdf->Output();
            } catch (\Exception $e) {
                new \Exception($e->getMessage());
            }

            $this->flashHelper->addSuccessFlash($configuration, EntityActions::CREATE, $newResource);

            return $this->redirectHandler->redirectToIndex($configuration);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle(View::create($form, Response::HTTP_BAD_REQUEST), $configuration->getRequest());
        }

        $formView = $form->createView();
        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $newResource,
                'form' => $formView,
            ])
            ->setTemplate($configuration->getTemplate($this->getResourceName() . '/growth.html'))
        ;

        return $this->viewHandler->handle($view, $configuration->getRequest());
    }
}