<?php

namespace Sandbox\WebsiteBundle\Form\Article;

use Kunstmaan\ArticleBundle\Form\AbstractArticlePageAdminType;
use Sandbox\WebsiteBundle\Repository\Company\CompanyOverviewPageRepository;
use Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
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
        parent::buildForm($builder, $options);

        $builder
            //->add('author')
            ->add('fromPlaces', 'place', ['label_attr' => ['style' => 'font-weight:bold;']])
            ->add('places', 'place', ['label_attr' => ['style' => 'font-weight:bold;']])
            ->add('topImage', 'entity', [
                'class' => 'Sandbox\WebsiteBundle\Entity\TopImage',
                'label_attr' => ['style' => 'font-weight:bold;'
                ]])
        ->add('hosts', 'entity', [
            'empty_data'  => null,
            'class' => 'Sandbox\WebsiteBundle\Entity\Host',
            'required' => false, 'multiple' => true,
            'label_attr' => ['style' => 'font-weight:bold;']
        ]);

        $builder->add('companies', 'company', ['label_attr' => ['style' => 'font-weight:bold;']]);

        $builder->add('image', 'media', array(
            'pattern' => 'KunstmaanMediaBundle_chooser',
            'mediatype' => 'image',
            'required' => false,
            'label_attr' => ['style' => 'font-weight:bold;']
          ));

        $builder->add('imgSize', 'choice', [
            'choices' => [
                '1000' => '1000x',
                '300l' => '300x left',
                '300r' => '300x right',
            ],
            'label_attr' => ['style' => 'font-weight:bold;']
        ]);

        $builder->add('imageOnlyOnPreview');

        $builder->add('priceFrom');
        $builder->add('priceFromLabel', 'priceFromLabel');
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
