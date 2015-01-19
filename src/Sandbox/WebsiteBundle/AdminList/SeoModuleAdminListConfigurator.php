<?php

namespace Sandbox\WebsiteBundle\AdminList;

use Doctrine\ORM\EntityManager;

use Sandbox\WebsiteBundle\Form\SeoModuleAdminType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;

/**
 * The admin list configurator for SeoModule
 */
class SeoModuleAdminListConfigurator extends AbstractDoctrineORMAdminListConfigurator
{
    /**
     * @param EntityManager $em        The entity manager
     * @param AclHelper     $aclHelper The acl helper
     */
    public function __construct(EntityManager $em, AclHelper $aclHelper = null)
    {
        parent::__construct($em, $aclHelper);
        $this->setAdminType(new SeoModuleAdminType());
    }

    /**
     * Configure the visible columns
     */
    public function buildFields()
    {
        $this->addField('hosts', 'Hosts', true);
        $this->addField('lang', 'Lang', true);
        $this->addField('entity', 'Entity', true);
        $this->addField('top', 'Top', true);
        $this->addField('bottom', 'Bottom', true);
        $this->addField('footer', 'Footer', true);
    }

    /**
     * Build filters for admin list
     */
    public function buildFilters()
    {
        $this->addFilter('lang', new ORM\StringFilterType('lang'), 'Lang');
        $this->addFilter('entity', new ORM\StringFilterType('entity'), 'Entity');
        $this->addFilter('top', new ORM\StringFilterType('top'), 'Top');
        $this->addFilter('bottom', new ORM\StringFilterType('bottom'), 'Bottom');
        $this->addFilter('footer', new ORM\StringFilterType('footer'), 'Footer');
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
        return 'SeoModule';
    }
}
