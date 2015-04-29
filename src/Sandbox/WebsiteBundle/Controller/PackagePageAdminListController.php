<?php

namespace Sandbox\WebsiteBundle\Controller;

use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\ArticleBundle\AdminList\AbstractArticlePageAdminListConfigurator;
use Kunstmaan\ArticleBundle\Controller\AbstractArticlePageAdminListController;
use Sandbox\WebsiteBundle\AdminList\PackagePageAdminListConfigurator;
use Kunstmaan\AdminListBundle\Controller\AdminListController;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AdminListConfiguratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * The admin list controller for PackagePage
 */
class PackagePageAdminListController extends AbstractArticlePageAdminListController//AdminListController
{
//    /**
//     * @var AdminListConfiguratorInterface
//     */
//    private $configurator;
//
//    /**
//     * @return AdminListConfiguratorInterface
//     */
//    public function getAdminListConfigurator()
//    {
//        if (!isset($this->configurator)) {
//            $this->configurator = new PackagePageAdminListConfigurator($this->getEntityManager());
//        }
//
//        return $this->configurator;
//    }

    /**
     * @return AbstractArticlePageAdminListConfigurator
     */
    public function createAdminListConfigurator()
    {
        return new PackagePageAdminListConfigurator($this->em, $this->aclHelper, $this->locale, PermissionMap::PERMISSION_EDIT);
    }

    /**
     * The index action
     *
     * @Route("/", name="sandboxwebsitebundle_admin_pages_packagepage")
     */
    public function indexAction(Request $request)
    {
        return parent::doIndexAction($this->getAdminListConfigurator(), $request);
    }

    /**
     * The add action
     *
     * @Route("/add", name="sandboxwebsitebundle_admin_pages_packagepage_add")
     * @Method({"GET", "POST"})
     * @return array
     */
    public function addAction(Request $request)
    {
        return parent::doAddAction($this->getAdminListConfigurator(), null, $request);
    }

    /**
     * The edit action
     *
     * @param int $id
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="sandboxwebsitebundle_admin_pages_packagepage_edit")
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        return parent::doEditAction($this->getAdminListConfigurator(), $id, $request);
    }

    /**
     * The delete action
     *
     * @param int $id
     *
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="sandboxwebsitebundle_admin_pages_packagepage_delete")
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function deleteAction(Request $request, $id)
    {
        return parent::doDeleteAction($this->getAdminListConfigurator(), $id, $request);
    }

    /**
     * The export action
     *
     * @param string $_format
     *
     * @Route("/export.{_format}", requirements={"_format" = "csv|xlsx"}, name="sandboxwebsitebundle_admin_packagepage_export")
     * @Method({"GET", "POST"})
     * @return array
     */
    public function exportAction(Request $request, $_format)
    {
        return parent::doExportAction($this->getAdminListConfigurator(), $_format, $request);
    }

}
