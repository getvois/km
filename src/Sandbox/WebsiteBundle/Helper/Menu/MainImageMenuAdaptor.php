<?php
namespace Sandbox\WebsiteBundle\Helper\Menu;


use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Kunstmaan\AdminBundle\Helper\Menu\TopMenuItem;
use Symfony\Component\HttpFoundation\Request;

class MainImageMenuAdaptor implements MenuAdaptorInterface {

    /**
     * In this method you can add children for a specific parent, but also remove and change the already created children
     *
     * @param MenuBuilder $menu The MenuBuilder
     * @param MenuItem[] &$children The current children
     * @param MenuItem|null $parent The parent Menu item
     * @param Request $request The Request
     */
    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
//        if (!is_null($parent) && 'KunstmaanAdminBundle_modules' == $parent->getRoute()) {
//            $menuItem = new TopMenuItem($menu);
//            $menuItem->setRoute('sandboxwebsitebundle_admin_mainimage');
//            $menuItem->setInternalName('Main Image');
//            $menuItem->setParent($parent);
//            if (stripos($request->attributes->get('_route'), $menuItem->getRoute()) === 0) {
//                $menuItem->setActive(true);
//                $parent->setActive(true);
//            }
//            $children[] = $menuItem;
//        }
    }
}