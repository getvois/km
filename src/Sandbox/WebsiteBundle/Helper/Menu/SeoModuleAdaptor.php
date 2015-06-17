<?php

namespace Sandbox\WebsiteBundle\Helper\Menu;


use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Kunstmaan\AdminBundle\Helper\Menu\TopMenuItem;
use Symfony\Component\HttpFoundation\Request;

class SeoModuleAdaptor implements MenuAdaptorInterface
{

    /**
     * {@inheritDoc}
     */
    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
        if (!is_null($parent) && 'topmenu_project' == $parent->getRoute()) {
            $menuItem = new TopMenuItem($menu);
            $menuItem->setRoute('sandboxwebsitebundle_admin_seomodule');
            $menuItem->setInternalName('Seo Module');
            $menuItem->setParent($parent);
            if (stripos($request->attributes->get('_route'), $menuItem->getRoute()) === 0) {
                $menuItem->setActive(true);
                $parent->setActive(true);
            }
            $children[] = $menuItem;
        }
    }

}