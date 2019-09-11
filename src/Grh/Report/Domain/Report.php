<?php
namespace CTIC\Grh\Report\Domain;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use CTIC\App\Base\Domain\IdentifiableTrait;
use CTIC\Grh\Report\Domain\Validation\ReportValidation;

/**
 * @ApiResource
 * @ORM\Entity(repositoryClass="CTIC\Grh\Report\Infrastructure\Repository\ReportRepository")
 */
class Report implements ReportInterface
{
    use IdentifiableTrait;
    use ReportValidation;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    public $name;
}