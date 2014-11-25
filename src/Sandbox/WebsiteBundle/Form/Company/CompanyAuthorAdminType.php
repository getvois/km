<?php

namespace Sandbox\WebsiteBundle\Form\Company;

use Kunstmaan\ArticleBundle\Form\AbstractAuthorAdminType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyAuthorAdminType extends AbstractAuthorAdminType
{

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sandbox\WebsiteBundle\Entity\Company\CompanyAuthor'
        ));
    }

    /**
     * @return string
     */
    function getName()
    {
        return 'Companyauthor_form';
    }

}