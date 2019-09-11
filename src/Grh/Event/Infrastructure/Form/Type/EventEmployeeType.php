<?php

namespace CTIC\Grh\Event\Infrastructure\Form\Type;

use CTIC\App\Base\Infrastructure\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventEmployeeType extends EntityType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'eventemployee';
    }
}
