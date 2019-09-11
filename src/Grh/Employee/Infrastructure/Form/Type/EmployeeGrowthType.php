<?php

namespace CTIC\Grh\Employee\Infrastructure\Form\Type;

use CTIC\App\Base\Infrastructure\Doctrine\Form\Type\EntityType;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Employee\Infrastructure\Repository\EmployeeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

final class EmployeeGrowthType extends AbstractType
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
                'multiple'      => true,
                'class'         => Employee::class,
                'query_builder' => function (EmployeeRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->orderBy('e.name', 'ASC');
                },
                'choice_label' => function ($employee) {
                    return $employee->getName() . ' ' . $employee->getSurname();
                }
            ])
            ->add('fromDate', DateType::class, [
                'label' => 'Fecha desde',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día'
                )
            ])
            ->add('contractualDate', DateType::class, [
                'label' => 'Fecha contratación',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día'
                ),
                'format' => 'yyyy-MM-dd H:i',
            ])
            ->add('hours', NumberType::class, [
                'label' => 'Horas'
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'ctic_app_employee_growth';
    }
}
