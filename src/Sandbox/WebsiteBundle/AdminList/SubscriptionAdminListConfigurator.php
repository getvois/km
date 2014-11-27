<?php

namespace Sandbox\WebsiteBundle\AdminList;

use Doctrine\ORM\EntityManager;

use Sandbox\WebsiteBundle\Form\SubscriptionAdminType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;

/**
 * The admin list configurator for Subscription
 */
class SubscriptionAdminListConfigurator extends AbstractDoctrineORMAdminListConfigurator
{

    /**
     * @param EntityManager $em        The entity manager
     * @param AclHelper     $aclHelper The acl helper
     */
    public function __construct(EntityManager $em, AclHelper $aclHelper = null)
    {
        parent::__construct($em, $aclHelper);
        $this->setAdminType(new SubscriptionAdminType());
    }

    /**
     * Configure the visible columns
     */
    public function buildFields()
    {
        $this->addField('email', 'Email', true);
        $this->addField('node', 'Node', true);
        $this->addField('lang', 'Lang', true);
        $this->addField('active', 'Active', true);
    }

    /**
     * Build filters for admin list
     */
    public function buildFilters()
    {
        $this->addFilter('email', new ORM\StringFilterType('email'), 'Email');
        $this->addFilter('lang', new ORM\StringFilterType('lang'), 'Lang');
        $this->addFilter('active', new ORM\BooleanFilterType('active'), 'Active');
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
        return 'Subscription';
    }

}
