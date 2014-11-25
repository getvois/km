<?php
namespace Sandbox\WebsiteBundle\Helper\Menu;

use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Kunstmaan\AdminBundle\Helper\Menu\TopMenuItem;
use Symfony\Component\HttpFoundation\Request;

class ModulesMenuAdaptor implements MenuAdaptorInterface
{

    /**
     * {@inheritDoc}
     */
    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
//        if (!is_null($parent) && 'KunstmaanAdminBundle_modules' == $parent->getRoute()) {
//            $menuItem = new TopMenuItem($menu);
//            $menuItem->setRoute('sandboxwebsitebundle_admin_place');
//            $menuItem->setInternalName('Places');
//            $menuItem->setParent($parent);
//            if (stripos($request->attributes->get('_route'), $menuItem->getRoute()) === 0) {
//                $menuItem->setActive(true);
//                $parent->setActive(true);
//            }
//            $children[] = $menuItem;
//        }
    }

}