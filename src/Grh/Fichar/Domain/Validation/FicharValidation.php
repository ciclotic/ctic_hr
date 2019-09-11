<?php
namespace CTIC\Grh\Fichar\Domain\Validation;

use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Mapping\ClassMetadata;

trait FicharValidation
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('date', new NotNull());
    }
}