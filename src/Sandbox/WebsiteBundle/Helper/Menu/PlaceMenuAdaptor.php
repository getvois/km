<?php

namespace Sandbox\WebsiteBundle\Helper\Menu;

use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Kunstmaan\AdminBundle\Helper\Menu\TopMenuItem;
use Symfony\Component\HttpFoundation\Request;

class PlaceMenuAdaptor implements MenuAdaptorInterface
{

    private $overviewpageIds = array();

    /**
     * @param EntityManager $em The entity manager
     */
    public function __construct(EntityManager $em)
    {
        $overviewpageNodes = $em->getRepository('KunstmaanNodeBundle:Node')->findByRefEntityName('Sandbox\\WebsiteBundle\\Entity\\Place\\PlaceOverviewPage');
        foreach ($overviewpageNodes as $overviewpageNode) {
            $this->overviewpageIds[] = $overviewpageNode->getId();
        }
    }

    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
        if (!is_null($parent) && 'KunstmaanAdminBundle_modules' == $parent->getRoute()) {
            // Page
            $menuitem = new TopMenuItem($menu);
            $menuitem->setRoute('sandboxwebsitebundle_admin_place_placepage');
            $menuitem->setInternalName('Places');
            $menuitem->setParent($parent);
            if (stripos($request->attributes->get('_route'), $menuitem->getRoute()) === 0) {
                $menuitem->setActive(true);
                $parent->setActive(true);
            }
            $children[] = $menuitem;
//            // Author
//            $menuitem = new TopMenuItem($menu);
//            $menuitem->setRoute('sandboxwebsitebundle_admin_place_placeauthor');
//            $menuitem->setInternalName('Place Authors');
//            $menuitem->setParent($parent);
//            if (stripos($request->attributes->get('_route'), $menuitem->getRoute()) === 0) {
//                $menuitem->setActive(true);
//                $parent->setActive(true);
//            }
//            $children[] = $menuitem;
        }

//        //don't load children
//        if (!is_null($parent) && 'KunstmaanNodeBundle_nodes_edit' == $parent->getRoute()) {
//            foreach ($children as $key => $child) {
//                if ('KunstmaanNodeBundle_nodes_edit' == $child->getRoute()){
//                    $params = $child->getRouteParams();
//                    $id = $params['id'];
//                    if (in_array($id, $this->overviewpageIds)) {
//                        $child->setChildren(array());
//                    }
//                }
//            }
//        }
    }
}
