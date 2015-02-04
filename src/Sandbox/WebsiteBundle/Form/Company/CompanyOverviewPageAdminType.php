<?php

namespace Sandbox\WebsiteBundle\Form\Company;

use Kunstmaan\ArticleBundle\Form\AbstractArticleOverviewPageAdminType;
use Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * The admin type for Companyoverview pages
 */
class CompanyOverviewPageAdminType extends AbstractArticleOverviewPageAdminType
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
        $builder->add('companyId');
        $builder->add('description', 'textarea', array(
            'attr' => array('rows' => 10, 'cols' => 600, 'class' => 'rich_editor'),
            'required' => false,
        ));
        $builder->add('logo', 'media', array(
            'pattern' => 'KunstmaanMediaBundle_chooser',
            'mediatype' => 'image',
            'required' => false,
        ));
        $builder->add('logoAltText', 'text', array(
            'required' => false,
        ));
        $builder->add('linkUrl', 'urlchooser', array(
            'required' => false,
        ));
        $builder->add('linkText', 'text', array(
            'required' => false,
        ));
        $builder->add('linkNewWindow', 'checkbox', array(
            'required' => false,
        ));
        $builder->add('places', 'place');

        $builder->add('affiliateLink');
        $builder->add('cashBackValue');
        $builder->add('cashBackType');

    }
    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'CompanyOverviewPage';
    }
}
