<?php

namespace CTIC\Grh\Employee\Infrastructure\Form\Type;

use CTIC\App\Base\Infrastructure\Doctrine\Form\Type\EntityType;
use CTIC\App\Base\Infrastructure\Form\Type\AbstractResourceType;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Employee\Infrastructure\Repository\EmployeeRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

final class EmployeePersonalAffairsType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('POST')
            ->add('employee', EntityType::class, [
                'label'         => 'Empleado',
                'class'         => Employee::class,
                'query_builder' => function (EmployeeRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.name', 'ASC');
                },
                'choice_label' => 'name'
            ])
            ->add('fromDate', DateTimeType::class, [
                'label' => 'Fecha desde',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día',
                    'hour' => 'Hora', 'minute' => 'Minutos'
                )
            ])
            ->add('toDate', DateTimeType::class, [
                'label' => 'Fecha hasta (Rellenar aunque no se sepa aún)',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día',
                    'hour' => 'Hora', 'minute' => 'Minutos'
                ),
                'format' => 'yyyy-MM-dd H:i',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'ctic_app_employee_personal_affairs';
    }
}
