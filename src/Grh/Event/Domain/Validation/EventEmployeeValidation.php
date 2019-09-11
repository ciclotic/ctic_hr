<?php
namespace CTIC\Grh\Event\Domain\Validation;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;

trait EventEmployeeValidation
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
    }
}