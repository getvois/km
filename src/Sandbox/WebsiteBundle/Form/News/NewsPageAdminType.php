<?php

namespace Sandbox\WebsiteBundle\Form\News;

use Kunstmaan\ArticleBundle\Form\AbstractArticlePageAdminType;
use Sandbox\WebsiteBundle\Repository\Company\CompanyOverviewPageRepository;
use Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The admin type for News pages
 */
class NewsPageAdminType extends AbstractArticlePageAdminType
{

    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add(
            'date',
            'datetime',
            array(
                'required' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy',
                'label_attr' => ['style' => 'font-weight:bold;']
            )
        );
        $builder//->add('author')
        ->add('fromPlaces', 'place', ['label_attr' => ['style' => 'font-weight:bold;']])
        ->add('places', 'place', ['label_attr' => ['style' => 'font-weight:bold;']])
        //->add('translate') //slugpart error on translated language; no page parts copied
        ->add('topImage', 'entity', [
            'class' => 'Sandbox\WebsiteBundle\Entity\TopImage',
            'label_attr' => ['style' => 'font-weight:bold;'
            ]])
        ->add('hosts', 'entity', [
            'class' => 'Sandbox\WebsiteBundle\Entity\Host',
            'label_attr' => ['style' => 'font-weight:bold;'
            ]]);
        $builder->add('dateUntil', 'date',
            array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label_attr' => ['style' => 'font-weight:bold;']
            )
        );
        $builder->add('link');
        $builder->add('companies', 'company', ['label_attr' => ['style' => 'font-weight:bold;']]);

        $builder->add('image', 'media', array(
            'pattern' => 'KunstmaanMediaBundle_chooser',
            'mediatype' => 'image',
            'required' => false,
            'label_attr' => ['style' => 'font-weight:bold;']
          ));
//        $builder->add('imgSize', 'choice', [
//            'choices' => [
//              '1000' => '1000x',
//              '300l' => '300x left',
//              '300r' => '300x right',
//            ]]
//        );
        $builder->add('imgSize', 'checkbox', [
            'label' => "Large img",
            'required' => false,
            'label_attr' => ['style' => 'font-weight:bold;']
        ]);

        $builder->add('priceFrom');
        $builder->add('priceFromLabel', 'priceFromLabel', ['label_attr' => ['style' => 'font-weight:bold;']]);

        $builder->add('tags', 'kunstmaan_taggingbundle_tags', ['label_attr' => ['style' => 'font-weight:bold;']]);
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\News\NewsPage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'NewsPage';
    }
}
