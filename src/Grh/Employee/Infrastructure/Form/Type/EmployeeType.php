<?php

namespace CTIC\Grh\Employee\Infrastructure\Form\Type;

use CTIC\App\Base\Infrastructure\Doctrine\Form\Type\EntityType;
use CTIC\App\Base\Infrastructure\Form\Type\AbstractResourceType;
use CTIC\App\Base\Infrastructure\Repository\EntityRepository;
use CTIC\App\User\Domain\User;
use CTIC\Grh\Employee\Domain\EmployeeArea;
use CTIC\Grh\Employee\Domain\EmployeeCategory;
use CTIC\Grh\Employee\Domain\EmployeeInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class EmployeeType extends AbstractResourceType
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
            ->add('surname', TextType::class, [
                'label' => 'Apellidos',
            ])
            ->add('dni', TextType::class, [
                'label' => 'DNI',
            ])
            ->add('socialSecurity', TextType::class, [
                'label' => 'Seguridad Social',
            ])
            ->add('accountNumber', TextType::class, [
                'label' => 'Número de cuenta',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Teléfono',
            ])
            ->add('address', TextType::class, [
                'label' => 'Dirección',
            ])
            ->add('email', TextType::class, [
                'label' => 'Correo electrónico',
            ])
            ->add('employeeCategory', EntityType::class, [
                'label'         => 'Categoría (Multiselección)',
                'class'         => EmployeeCategory::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ec')
                        ->orderBy('ec.name', 'ASC');
                },
                'expanded'      => false,
                'multiple'      => true,
                'choice_label' => 'name'
            ])
            ->add('employeeArea', EntityType::class, [
                'label'         => 'Área (Multiselección)',
                'class'         => EmployeeArea::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ea')
                        ->orderBy('ea.name', 'ASC');
                },
                'expanded'      => false,
                'multiple'      => true,
                'choice_label' => 'name'
            ])
            ->add('userRelated', EntityType::class, [
                'label'         => 'Usuario relacionado',
                'class'         => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ea')
                        ->orderBy('ea.name', 'ASC');
                },
                'expanded'      => false,
                'multiple'      => false,
                'choice_label'  => 'name',
                'placeholder'   => 'Escoje una opción',
                'required'      => false
            ])
            ->add('contractType', ChoiceType::class, [
                'label' => 'Interno / Subcontratado',
                'choices'   => array(
                    'Interno'           => EmployeeInterface::CONTRACT_TYPE_INTERN,
                    'Subcontratado'     => EmployeeInterface::CONTRACT_TYPE_SUB
                )
            ])
            ->add('workingDayType', ChoiceType::class, [
                'label' => 'Jornada fija / flexible',
                'choices'   => array(
                    'Fija'          => EmployeeInterface::WORKING_DAY_FIX,
                    'Flexible'      => EmployeeInterface::WORKING_DAY_FLEX
                )
            ])
            ->add('costPerHour', TextType::class, [
                'label' => 'Coste fijo / hora',
            ])
            ->add('pricePerHour', TextType::class, [
                'label' => 'Precio hora',
            ])
            ->add('pricePerHourType', ChoiceType::class, [
                'label' => 'Tipo coste fijo / hora',
                'choices'   => array(
                    'Fijo'    => EmployeeInterface::PRICE_PER_HOUR_FIX,
                    'Hora'    => EmployeeInterface::PRICE_PER_HOUR_FLEX
                )
            ])
            ->add('barcode', TextType::class, [
                'label' => 'Código de barras',
            ])
            ->add('enabled', ChoiceType::class, [
                'label' => 'Habilitado',
                'choices'   => array(
                    'Si'    => 1,
                    'No'    => 0
                )
            ])
            ->add('attached', CollectionType::class, [
                'label'                 => 'Ficheros adjuntos',
                'entry_type'            => EmployeeAttachedType::class,
                'prototype'             => true,
                'allow_add'             => true,
                'allow_delete'          => true
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'ctic_app_employee';
    }
}
