<?php

namespace Sandbox\WebsiteBundle\AdminList;

use Doctrine\ORM\EntityManager;

use Sandbox\WebsiteBundle\Form\GooglePlaceAdminType;
use Kunstmaan\AdminListBundle\AdminList\FilterType\ORM;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AbstractDoctrineORMAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\AclHelper;

/**
 * The admin list configurator for GooglePlace
 */
class GooglePlaceAdminListConfigurator extends AbstractDoctrineORMAdminListConfigurator
{

    /**
     * @param EntityManager $em        The entity manager
     * @param AclHelper     $aclHelper The acl helper
     */
    public function __construct(EntityManager $em, AclHelper $aclHelper = null)
    {
        parent::__construct($em, $aclHelper);
        $this->setAdminType(new GooglePlaceAdminType());
    }

    /**
     * Configure the visible columns
     */
    public function buildFields()
    {
        $this->addField('title', 'Title', true);
        $this->addField('type', 'Type', true);
        $this->addField('latitude', 'Latitude', true);
        $this->addField('longitude', 'Longitude', true);
        $this->addField('description', 'Description', true);
        $this->addField('url', 'Url', true);
    }

    /**
     * Build filters for admin list
     */
    public function buildFilters()
    {
        $this->addFilter('title', new ORM\StringFilterType('title'), 'Title');
        $this->addFilter('type', new ORM\StringFilterType('type'), 'Type');
        $this->addFilter('latitude', new ORM\StringFilterType('latitude'), 'Latitude');
        $this->addFilter('longitude', new ORM\StringFilterType('longitude'), 'Longitude');
        $this->addFilter('description', new ORM\StringFilterType('description'), 'Description');
        $this->addFilter('url', new ORM\StringFilterType('url'), 'Url');
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
        return 'GooglePlace';
    }

}
