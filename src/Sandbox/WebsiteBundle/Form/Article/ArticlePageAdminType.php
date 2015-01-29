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
            ->add('fromPlaces', 'entity', [
                'multiple' => true,
                'class' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage', 'required' => false,
                'query_builder' => function(PlaceOverviewPageRepository $er) {
                    $request = Request::createFromGlobals();
                    var_dump($request->getLocale());
                    if(array_key_exists('REQUEST_URI', $_SERVER)){
                        $locale = (substr(str_replace("app_dev.php/", "", $_SERVER['REQUEST_URI']), 1, 2));//get locale from url(not the best way)
                    }
                    else if (array_key_exists('PATH_INFO', $_SERVER)){
                        $locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                    }
                    else{
                        $locale = 'en';
                    }
                    //$locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                    return $er->getByLang($locale);
                }
            ]
        )->add('places', 'entity', [
                'multiple' => true,
                'class' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage', 'required' => false,
                'query_builder' => function(PlaceOverviewPageRepository $er) {
                    if(array_key_exists('REQUEST_URI', $_SERVER)){
                        $locale = (substr(str_replace("app_dev.php/", "", $_SERVER['REQUEST_URI']), 1, 2));//get locale from url(not the best way)
                    }
                    else if (array_key_exists('PATH_INFO', $_SERVER)){
                        $locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                    }
                    else{
                        $locale = 'en';
                    }
                    //$locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                    return $er->getByLang($locale);
                }
            ]
        )->add('topImage')
        ->add('hosts', 'entity', ['empty_data'  => null, 'class' => 'Sandbox\WebsiteBundle\Entity\Host', 'required' => false, 'multiple' => true]);

        $builder->add('companies', 'entity', [
            'multiple' => true,
            'class' => 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage', 'required' => false,
            'query_builder' => function(CompanyOverviewPageRepository $er) {
                if(array_key_exists('REQUEST_URI', $_SERVER)){
                    $locale = (substr(str_replace("app_dev.php/", "", $_SERVER['REQUEST_URI']), 1, 2));//get locale from url(not the best way)
                }
                else if (array_key_exists('PATH_INFO', $_SERVER)){
                    $locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                }
                else{
                    $locale = 'en';
                }
                //$locale = (substr($_SERVER['PATH_INFO'], 1, 2));//get locale from url(not the best way)
                return $er->getByLang($locale);
            }
        ]);

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
            ]]
        );

        $builder->add('imageOnlyOnPreview');

        $builder->add('priceFrom');
        $builder->add('priceFromLabel', 'choice', ['choices' => [
            '' => '',
            'day' => 'day',
            'trip' => 'trip',
            'stay' => 'stay',
            'room' => 'room',
            'one way' => 'one way',
            'return' => 'return',
            'off' => 'off',
        ]]);
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
