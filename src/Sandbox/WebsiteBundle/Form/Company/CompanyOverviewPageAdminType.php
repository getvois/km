<?php

namespace Sandbox\WebsiteBundle\Form\Company;

use Kunstmaan\ArticleBundle\Form\AbstractArticleOverviewPageAdminType;
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
