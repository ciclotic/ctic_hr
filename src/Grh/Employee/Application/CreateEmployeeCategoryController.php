<?php
namespace CTIC\Grh\Employee\Application;

use CTIC\App\Base\Application\CreateResourceController;
use CTIC\App\Base\Domain\Metadata;
use CTIC\App\Base\Infrastructure\Controller\Command\ResourceControllerCommand;
use CTIC\App\Base\Infrastructure\Controller\ResourceController;
use CTIC\App\Base\Infrastructure\Repository\PermissionRepositoryInterface;
use CTIC\Grh\Employee\Domain\Command\EmployeeCategoryCommand;
use CTIC\Grh\Employee\Domain\EmployeeCategory;
use CTIC\Grh\Employee\Infrastructure\Controller\EmployeeCategoryController;
use CTIC\Grh\Employee\Infrastructure\Form\Type\EmployeeCategoryType;
use Metadata\Driver\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CTIC\App\Base\Infrastructure\Form\FormRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CreateEmployeeCategoryController extends CreateResourceController
{
    /**
     * @param FormRegistry $formRegistry
     * @param ResourceControllerCommand $command
     *
     * @return void
     */
    public static function completeFormRegistry(
        FormRegistry $formRegistry,
        ResourceControllerCommand $command
    ): void
    {
        parent::completeFormRegistry($formRegistry, $command);

        $formRegistry->addType(new EmployeeCategoryType(EmployeeCategory::class));
    }

    /**
     * @param ResourceControllerCommand $command
     * @param PermissionRepositoryInterface $permissionRepository
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param TranslatorInterface $translator
     * @param string $defaultLocale
     * @param Request $request
     * @param FileLocatorInterface $fileLocator
     * @param ContainerInterface $container
     * @param array $twigParams
     *
     * @return void
     *
     * @throws \Exception
     */
    public static function completeCommand(
        ResourceControllerCommand $command,
        PermissionRepositoryInterface $permissionRepository,
        RouterInterface $router,
        SessionInterface $session,
        TranslatorInterface $translator,
        $defaultLocale,
        Request $request,
        FileLocatorInterface $fileLocator,
        ContainerInterface $container,
        array $twigParams
    ): void
    {

        if(empty($command->repository))
        {
            $command->repository = self::getRepository($command->manager, $command->resource);
        }
        if(empty($command->metadata))
        {
            $command->metadata = self::getMetadata(
                'EmployeeCategory',
                'App',
                array(
                    'permission'    => true,
                    'repository'    => $command->repository,
                    'driver'        => '',
                    'templates'     => '',
                    'header'        => 'categoría empleados',
                    'route'         => array(
                        'root'      => 'trabajadores_categoria',
                        'actions'   => array(
                            'list'  => 'listado',
                            'show'  => 'mostrar',
                            'create'    =>'crear',
                            'update'    =>'modificar',
                            'delete'    =>'eliminar',
                            'bulkDelete'    => 'eliminargrupo',
                            'download'    => 'descargar'
                        )
                    ),
                    'classes'       => array(
                        'form'      => 'CTIC\Grh\Employee\Infrastructure\Form\Type\EmployeeCategoryType'
                    ),
                    'grid'          => array(
                        'actionGroups'  => array(
                            'bulk'  => array(
                                'delete'  => array(
                                    'type'      => 'delete',
                                    'enabled'   => true,
                                    'position'  => 10,
                                    'name'      => 'delete',
                                    'label'     => 'Eliminar'
                                ),
                                'download'  => array(
                                    'type'      => 'download',
                                    'enabled'   => true,
                                    'position'  => 20,
                                    'name'      => 'download',
                                    'label'     => 'Descargar'
                                )
                            ),
                            'item'  => array(
                                'show'  => array(
                                    'type'      => 'show',
                                    'enabled'   => true,
                                    'position'  => 10,
                                    'name'      => 'show',
                                    'label'     => 'Mostrar'
                                ),
                                'edit'  => array(
                                    'type'      => 'edit',
                                    'enabled'   => true,
                                    'position'  => 20,
                                    'name'      => 'edit',
                                    'label'     => 'Editar'
                                ),
                                'delete'  => array(
                                    'type'      => 'delete',
                                    'enabled'   => true,
                                    'position'  => 30,
                                    'name'      => 'delete',
                                    'label'     => 'Eliminar'
                                )
                            )
                        ),
                        'paginate'  => true,
                        'limit'     => 10,
                        'sortable'  => true,
                        'sorting'   => array(
                            'id'    => 'ASC'
                        ),
                        'fields'    => array(
                            'id'    => array(
                                'type'          => 'text',
                                'enabled'       => true,
                                'isSortable'    => true,
                                'name'          => 'id',
                                'label'         => 'Id',
                                'position'      => '10'
                            ),
                            'name'    => array(
                                'type'          => 'text',
                                'isSortable'    => true,
                                'name'          => 'name',
                                'label'         => 'Nombre',
                                'position'      => '20'
                            )
                        ),
                        'filterable' => true,
                        'filters'    => array(
                            'id'    => array(
                                'type'          => 'text',
                                'enabled'       => true,
                                'name'          => 'id',
                                'label'         => 'Id',
                                'formOptions'   => array(),
                                'position'      => '10'
                            ),
                            'name'    => array(
                                'type'          => 'text',
                                'enabled'       => true,
                                'name'          => 'name',
                                'label'         => 'Nombre',
                                'formOptions'   => array(),
                                'position'      => '20'
                            )
                        )
                    )
                )
            );
        }
        $command->authorizationChecker = self::getAuthorizationChecker($permissionRepository);
        $command->redirectHandler = self::getRedirectHandler($router);
        if(empty($command->eventDispatcher))
        {
            $command->eventDispatcher = self::getEventDispatcher();
        }
        $command->flashHelper = self::getFlashHelper($session, $translator, $defaultLocale);
        $engine = self::getEngine($twigParams, $command, $router);
        $command->viewHandler = self::getViewHandler($router, $fileLocator, $request, $engine);
        if(empty($command->stateMachine))
        {
            $command->stateMachine = self::getStateMachine();
        }
        if(empty($command->resourceUpdateHandler))
        {
            $command->resourceUpdateHandler = self::getResourceUpdateHandler($command->stateMachine);
        }
        if(empty($command->resourceDeleteHandler))
        {
            $command->resourceDeleteHandler = self::getResourceDeleteHandler();
        }
        if(empty($command->newResourceFactory))
        {
            throw new \Exception('Create resource service not found.');
        }
        if(empty($command->resource))
        {
            throw new \Exception('Resource class not found.');
        }
        if(empty($command->manager))
        {
            throw new \Exception('Entity manager service not found.');
        }
        if(empty($command->command))
        {
            throw new \Exception('Resource command not found.');
        }
        if(empty($command->resourceFormFactory))
        {
            $command->resourceFormFactory = self::getResourceFormFactory($command);
        }
        if(empty($command->resourcesCollectionProvider))
        {
            $command->resourcesCollectionProvider = self::getResourcesCollectionProvider();
        }
        if(empty($command->requestConfigurationFactory))
        {
            $command->requestConfigurationFactory = self::getRequestConfigurationFactory($container, $command->resource);
        }
        if(empty($command->singleResourceProvider))
        {
            $command->singleResourceProvider = self::getSingleResourceProvider();
        }
    }

    public static function setDefaultCommand(ResourceControllerCommand $command): void
    {
        if(empty($command->newResourceFactory))
        {
            $command->newResourceFactory = new CreateEmployeeCategory();
        }
        if(empty($command->resource))
        {
            $command->resource = EmployeeCategory::class;
        }
        if(empty($command->command))
        {
            $command->command = new EmployeeCategoryCommand();
        }
    }

    public static function getResourceControllerClass(): string
    {
        return EmployeeCategoryController::class;
    }

    /**
     * @inheritdoc
     */
    public static function create(
        ResourceControllerCommand $command,
        PermissionRepositoryInterface $permissionRepository,
        RouterInterface $router,
        SessionInterface $session,
        TranslatorInterface $translator,
        $defaultLocale,
        Request $request,
        FileLocatorInterface $fileLocator,
        ContainerInterface $container,
        array $twigParams
    ): ResourceController
    {
        self::setDefaultCommand($command);
        self::completeCommand(
            $command,
            $permissionRepository,
            $router,
            $session,
            $translator,
            $defaultLocale,
            $request,
            $fileLocator,
            $container,
            $twigParams
        );

        $resourceControllerClass = static::getResourceControllerClass();
        $resourceController = new $resourceControllerClass($command);

        return $resourceController;
    }
}