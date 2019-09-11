<?php

namespace CTIC\Grh\Fichar\Infrastructure\Form\Type;

use CTIC\App\Base\Infrastructure\Doctrine\Form\Type\EntityType;
use CTIC\App\Base\Infrastructure\Form\Type\AbstractResourceType;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Employee\Infrastructure\Repository\EmployeeRepository;
use CTIC\Grh\Fichar\Domain\Fichar;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

final class FicharType extends AbstractResourceType
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
            ->add('date', DateTimeType::class, [
                'label' => 'Fecha',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día',
                    'hour' => 'Hora', 'minute' => 'Minutos'
                )
            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude',
                'required'  => false
            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude',
                'required'  => false
            ])
            ->add('action', ChoiceType::class, [
                'label' => 'Acción',
                'choices'   => array(
                    'Entrada'    => Fichar::IN,
                    'Salida'     => Fichar::OUT
                )
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'ctic_app_employee_low';
    }
}
