<?php

namespace Sandbox\WebsiteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * The type for SeoModule
 */
class SeoModuleAdminType extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hosts');
        $builder->add('lang', 'choice', ['choices' => [
            'fi' => 'fi',
            'en' => 'en',
            'de' => 'de',
            'fr' => 'fr',
            'ru' => 'ru',
            'se' => 'se',
            'ee' => 'ee',
        ]]);
        $builder->add('entity', 'choice', ['choices' => [
//            "Kunstmaan\\SitemapBundle\\Entity\\SitemapPage" => "SitemapPage",
            "Sandbox\\WebsiteBundle\\Entity\\Article\\ArticleOverviewPage" => "ArticleOverviewPage",
            "Sandbox\\WebsiteBundle\\Entity\\Article\\ArticlePage" => "ArticlePage",
            "Sandbox\\WebsiteBundle\\Entity\\Company\\CompanyOverviewPage" => "CompanyPage",
            //"Sandbox\\WebsiteBundle\\Entity\\Company\\CompanyPage" => "CompanyPage",
            "Sandbox\\WebsiteBundle\\Entity\\News\\NewsOverviewPage" => "NewsOverviewPage",
            "Sandbox\\WebsiteBundle\\Entity\\News\\NewsPage" => "NewsPage",
//            "Sandbox\\WebsiteBundle\\Entity\\Pages\\ContentPage" => "ContentPage",
//            "Sandbox\\WebsiteBundle\\Entity\\Pages\\FormPage" => "FormPage",
            "Sandbox\\WebsiteBundle\\Entity\\Pages\\HomePage" => "HomePage",
//            "Sandbox\\WebsiteBundle\\Entity\\Pages\\SatelliteOverviewPage" => "SatelliteOverviewPage",
            "Sandbox\\WebsiteBundle\\Entity\\Place\\PlaceOverviewPage" => "PlaceOverviewPage",
        ]]);
        $builder->add('top', 'textarea', ['label' => "Top(wildcards:[title])", 'required' => false]);
        $builder->add('bottom');
        $builder->add('footer');
        $builder->add('metaTitle');
        $builder->add('metaDescription');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'seomodule_form';
    }
}
