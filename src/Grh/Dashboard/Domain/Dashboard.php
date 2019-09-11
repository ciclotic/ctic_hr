<?php
namespace CTIC\Grh\Dashboard\Domain;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\Grh\Dashboard\Domain\Validation\DashboardValidation;

/**
 * @ApiResource
 * @ORM\Entity(repositoryClass="CTIC\Grh\Dashboard\Infrastructure\Repository\DashboardRepository")
 * @ORM\Table(name="DashboardGrh")
 */
class Dashboard implements DashboardInterface
{
    use IdentifiableTrait;
    use DashboardValidation;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $name;
}