<?php
namespace CTIC\Grh\Fichar\Domain;

use CTIC\App\Base\Domain\EntityInterface;
use CTIC\App\Base\Domain\IdentifiableInterface;

interface FicharInterface extends IdentifiableInterface, EntityInterface
{
    public const IN = 1;
    public const OUT = 0;

    public function getEmployee();
    public function getDate(): \DateTime;
    public function getAction();

    public function getLatitude(): ?string;
    public function setLatitude($latitude): void;
    public function getLongitude(): ?string;
    public function setLongitude($longitude): void;
}