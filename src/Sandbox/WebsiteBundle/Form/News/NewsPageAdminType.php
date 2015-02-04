<?php

namespace Sandbox\WebsiteBundle\Form\News;

use Kunstmaan\ArticleBundle\Form\AbstractArticlePageAdminType;
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
        //parent::buildForm($builder, $options);
        $builder->add('id', 'hidden');
        $builder->add('title', null, array('label' => 'Name', 'label_attr' => ['style' => 'font-weight:bold;']));
        $builder->add('pageTitle', null ,['label_attr' => ['style' => 'font-weight:bold;']]);
        $builder->add('summary', null, ['label_attr' => ['style' => 'font-weight:bold;']]);

        $builder->add('link', null, ['label_attr' => ['style' => 'font-weight:bold;']]);
        $builder->add('priceFrom', null, ['label_attr' => ['style' => 'font-weight:bold;']]);
        $builder->add('priceFromLabel', 'priceFromLabel');

        $builder->add(
            'date',
            'datetime',
            array(
                'required' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy',
            )
        );
        $builder//->add('author')
        ->add('fromPlaces', 'place')
        ->add('places', 'place')
        //->add('translate') //slugpart error on translated language; no page parts copied
        ->add('topImage')
        ->add('hosts');
        $builder->add('dateUntil', 'date',
            array(
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
            )
        );
        $builder->add('companies', 'company');

        $builder->add('image', 'media', array(
            'pattern' => 'KunstmaanMediaBundle_chooser',
            'mediatype' => 'image',
            'required' => false,
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
        ]);

        $builder->add('tags', 'kunstmaan_taggingbundle_tags');
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
