<?php

namespace Sandbox\WebsiteBundle\AdminList;

use Doctrine\ORM\EntityManager;

use Sandbox\WebsiteBundle\Form\AdAdminType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;

/**
 * The admin list configurator for Ad
 */
class AdAdminListConfigurator extends AbstractDoctrineORMAdminListConfigurator
{
    /**
     * @param EntityManager $em        The entity manager
     * @param AclHelper     $aclHelper The acl helper
     */
    public function __construct(EntityManager $em, AclHelper $aclHelper = null)
    {
        parent::__construct($em, $aclHelper);
        $this->setAdminType(new AdAdminType());
    }

    /**
     * Configure the visible columns
     */
    public function buildFields()
    {
        $this->addField('name', 'Name', true);
        $this->addField('campaign', 'Campaign', true);
        $this->addField('lang', 'Lang', true);
        $this->addField('position', 'Position', true);
        $this->addField('html', 'Html', true);
        $this->addField('weight', 'Weight', true);
    }

    /**
     * Build filters for admin list
     */
    public function buildFilters()
    {
        $this->addFilter('name', new ORM\StringFilterType('name'), 'Name');
        $this->addFilter('campaign', new ORM\StringFilterType('campaign'), 'Campaign');
        $this->addFilter('lang', new ORM\StringFilterType('lang'), 'Lang');
        $this->addFilter('position', new ORM\StringFilterType('position'), 'Position');
        $this->addFilter('html', new ORM\StringFilterType('html'), 'Html');
        $this->addFilter('weight', new ORM\NumberFilterType('weight'), 'Weight');
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
        return 'Ad';
    }
}
