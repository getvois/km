<?php

namespace Sandbox\WebsiteBundle\AdminList;


class UserAdminListConfigurator extends \Kunstmaan\UserManagementBundle\AdminList\UserAdminListConfigurator{
    /**
     * Get entity name
     *
     * @return string
     */
    public function getEntityName()
    {
        return 'User';
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
}