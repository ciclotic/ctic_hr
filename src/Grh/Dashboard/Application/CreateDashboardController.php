<?php
namespace CTIC\Grh\Dashboard\Application;

use CTIC\App\Base\Application\CreateResourceController;
use CTIC\App\Base\Infrastructure\Controller\Command\ResourceControllerCommand;
use CTIC\App\Base\Infrastructure\Controller\ResourceController;
use CTIC\App\Base\Infrastructure\Repository\PermissionRepositoryInterface;
use CTIC\Grh\Dashboard\Domain\Command\DashboardCommand;
use CTIC\Grh\Dashboard\Domain\Dashboard;
use CTIC\Grh\Dashboard\Infrastructure\Controller\DashboardController;
use Metadata\Driver\FileLocatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CreateDashboardController extends CreateResourceController
{
    public static function setDefaultCommand(ResourceControllerCommand $command): void
    {
        if(empty($command->newResourceFactory))
        {
            $command->newResourceFactory = new CreateDashboard();
        }
        if(empty($command->resource))
        {
            $command->resource = Dashboard::class;
        }
        if(empty($command->command))
        {
            $command->command = new DashboardCommand();
        }
    }

    public static function getResourceControllerClass(): string
    {
        return DashboardController::class;
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

        $resourceControllerClass = self::getResourceControllerClass();
        $resourceController = new $resourceControllerClass($command);

        return $resourceController;
    }
}