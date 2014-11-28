<?php

namespace Sandbox\WebsiteBundle\Form\Article;

use Kunstmaan\ArticleBundle\Form\AbstractArticlePageAdminType;
use Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository;
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
        parent::buildForm($builder, $options);

        $builder
            //->add('author')
            ->add('fromPlaces', 'entity', [
                'multiple' => true,
                'class' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage', 'required' => false,
                'query_builder' => function(PlaceOverviewPageRepository $er) {
                    $locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                    return $er->getByLang($locale);
                }
            ]
        )->add('places', 'entity', [
                'multiple' => true,
                'class' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage', 'required' => false,
                'query_builder' => function(PlaceOverviewPageRepository $er) {
                    $locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                    return $er->getByLang($locale);
                }
            ]
        )->add('topImage')
        ->add('hosts');
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
