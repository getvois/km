<?php

namespace Sandbox\WebsiteBundle\AdminList;

use Doctrine\ORM\EntityManager;

use Sandbox\WebsiteBundle\Form\MainImageAdminType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;

/**
 * The admin list configurator for MainImage
 */
class MainImageAdminListConfigurator extends AbstractDoctrineORMAdminListConfigurator
{

    /**
     * @param EntityManager $em        The entity manager
     * @param AclHelper     $aclHelper The acl helper
     */
    public function __construct(EntityManager $em, AclHelper $aclHelper = null)
    {
        parent::__construct($em, $aclHelper);
        $this->setAdminType(new MainImageAdminType());
    }

    /**
     * Configure the visible columns
     */
    public function buildFields()
    {
        $this->addField('picture', 'Picture', false);
        //$this->addField('title', 'Title', true, 'SandboxWebsiteBundle:AdminList\MainImage:picture.html.twig');
    }

    /**
     * Build filters for admin list
     */
    public function buildFilters()
    {
    }

    /**
     * Get bundle name
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'SandboxWebsiteBundle';
    }

    /**
     * Get entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        return 'MainImage';
    }

}
