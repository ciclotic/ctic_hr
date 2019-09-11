<?php

namespace CTIC\Grh\Event\Infrastructure\Form\Type;

use CTIC\App\Base\Infrastructure\Form\Type\AbstractResourceType;
use CTIC\App\Base\Infrastructure\Doctrine\Form\Type\EntityType;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\Grh\Employee\Domain\Employee;
use CTIC\Grh\Event\Domain\EventEmployee;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class EventType extends AbstractResourceType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('POST')
            ->add('name', TextType::class, [
                'label' => 'Nombre',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción'
            ])
            ->add('iCalUID', TextType::class, [
                'label' => 'iCal UID',
            ])
            ->add('fromDate', DateTimeType::class, [
                'label' => 'Fecha inicio evento',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día',
                    'hour' => 'Hora', 'minute' => 'Minutos'
                )
            ])
            ->add('shiftChangeDate', DateTimeType::class, [
                'label' => 'Cambio de turno dentro del mismo evento (si solo hay un evento poner la misma fecha que la fecha incio de evento)',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día',
                    'hour' => 'Hora', 'minute' => 'Minutos'
                )
            ])
            ->add('toDate', DateTimeType::class, [
                'label' => 'Final de evento',
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Día',
                    'hour' => 'Hora', 'minute' => 'Minutos'
                ),
                'format' => 'yyyy-MM-dd H:i',
            ])
            ->add('eventEmployee', EventEmployeeType::class, [
                'label'         => 'Empleados (Multiselección)',
                'class'         => Employee::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.enabled = 1') //  AND el.id is NULL AND epa.id is NULL
                        /*->leftJoin('e.employeeLow', 'el', 'WITH', 'el.toDate > :now')
                        ->leftJoin('e.employeePersonalAffairs', 'epa', 'WITH', 'epa.toDate > :now')
                        ->setParameter('now', new \DateTime())*/
                        ->orderBy('e.id', 'ASC');
                },
                'expanded'      => false,
                'multiple'      => true,
                'choice_label' => 'name'
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'ctic_app_event';
    }
}
