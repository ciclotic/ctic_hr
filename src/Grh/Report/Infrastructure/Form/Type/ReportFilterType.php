<?php

namespace CTIC\Grh\Report\Infrastructure\Form\Type;

use CTIC\App\Base\Infrastructure\Doctrine\Form\Type\EntityType;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Employee\Infrastructure\Repository\EmployeeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

final class ReportFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('POST')
            ->add('send', HiddenType::class)
            ->add('sendFichar', HiddenType::class)
            ->add('fromDate', DateTimeType::class, [
                'label' => 'Fecha desde',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día' ,
                    'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo'
                )
            ])
            ->add('toDate', DateTimeType::class, [
                'label' => 'Fecha hasta',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día' ,
                    'hour' => 'Hora', 'minute' => 'Minuto', 'second' => 'Segundo'
                )
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'ctic_app_report_filter';
    }
}
