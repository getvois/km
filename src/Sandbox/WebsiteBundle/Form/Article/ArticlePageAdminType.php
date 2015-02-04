<?php

namespace Sandbox\WebsiteBundle\Form\Article;

use Kunstmaan\ArticleBundle\Form\AbstractArticlePageAdminType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The admin type for Article pages
 */
class ArticlePageAdminType extends AbstractArticlePageAdminType
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
        $builder->add('pageTitle', null, ['label_attr' => ['style' => 'font-weight:bold;']]);
        $builder->add('summary', null, ['label_attr' => ['style' => 'font-weight:bold;']]);

        $builder->add('priceFrom', null, ['required' => false, 'label_attr' => ['style' => 'font-weight:bold;']]);
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
        $builder->add('fromPlaces', 'place');
        $builder->add('places', 'place');

        $builder->add('companies', 'company');
        $builder->add('tags', 'kunstmaan_taggingbundle_tags');

        $builder->add('topImage');



        $builder->add('image', 'media', array(
            'pattern' => 'KunstmaanMediaBundle_chooser',
            'mediatype' => 'image',
            'required' => false,
          ));

        $builder->add('imgSize', 'choice', [
            'choices' => [
                '1000' => '1000x',
                '300l' => '300x left',
                '300r' => '300x right',
            ],
        ]);

        $builder->add('imageOnlyOnPreview');

        $builder->add('hosts');
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Article\ArticlePage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ArticlePage';
    }
}
