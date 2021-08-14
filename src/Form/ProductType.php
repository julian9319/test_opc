<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class, array(
                'label'     => 'Código',
                'attr'      => array(
                    'class' => 'form-control form-control-sm',
                ),
                'required'  => true,
            ))   
            ->add('name', TextType::class, array(
                'label'     => 'Nombre',
                'attr'      => array('class' => 'form-control form-control-sm'),
                'required'  => true,
            ))            
            ->add('brand', TextType::class, array(
                'label'     => 'Marca',
                'attr'      => array('class' => 'form-control form-control-sm'),
                'required'  => true,
            ))
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'row'=>3],
            ])
            ->add('active', ChoiceType::class, array(
                'label'    => 'Estado',
                'choices' => array(
                    'Activo'    => 1,
                    'Inactivo'  => 0
                ),
                'attr' => array('class' => 'form-control form-control-sm'),
                'required' => true
            ))
            ->add('price', NumberType::class, [
                'label'    => 'Precio',
                'required' => true,
                'attr'     => array(
                    'class' => 'form-control form-control-sm',
                    'min'  => 0,
                    'step' => 1,
                ),
            ])
            ->add('fkCategory', EntityType::class, array(
                'class' => 'App\Entity\Category',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.active=1')
                        ->orderBy('c.name', 'DESC');
                },
                'choice_label' => function ($category) {
                return $category->getName();
                },
                'label' => "Categoria: ",
                'attr' => array(
                    'class' => 'form-control form-control-sm',
                    'required' => true,
                ),
            ))  
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
