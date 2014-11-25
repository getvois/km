<?php

namespace Sandbox\WebsiteBundle\Controller\News;

use Sandbox\WebsiteBundle\AdminList\News\NewsAuthorAdminListConfigurator;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AdminListConfiguratorInterface;
use Kunstmaan\ArticleBundle\Controller\AbstractArticleAuthorAdminListController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * The AdminList controller for the NewsAuthor
 */
class NewsAuthorAdminListController extends AbstractArticleAuthorAdminListController
{

    /**
     * @return AdminListConfiguratorInterface
     */
    public function createAdminListConfigurator()
    {
        return new NewsAuthorAdminListConfigurator($this->em, $this->aclHelper, $this->locale, PermissionMap::PERMISSION_EDIT);
    }

    /**
     * The index action
     *
     * @Route("/", name="sandboxwebsitebundle_admin_news_newsauthor")
     */
    public function indexAction()
    {
        return parent::doIndexAction($this->getAdminListConfigurator());
    }

    /**
     * The add action
     *
     * @Route("/add", name="sandboxwebsitebundle_admin_news_newsauthor_add")
     * @Method({"GET", "POST"})
     * @return array
     */
    public function addAction()
    {
        return parent::doAddAction($this->getAdminListConfigurator());
    }

    /**
     * The edit action
     *
     * @param int $id
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="sandboxwebsitebundle_admin_news_newsauthor_edit")
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function editAction($id)
    {
        return parent::doEditAction($this->getAdminListConfigurator(), $id);
    }

    /**
     * The delete action
     *
     * @param int $id
     *
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="sandboxwebsitebundle_admin_news_newsauthor_delete")
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function deleteAction($id)
    {
        return parent::doDeleteAction($this->getAdminListConfigurator(), $id);
    }

    /**
     * Export action
     *
     * @param $_format
     *
     * @Route("/export.{_format}", requirements={"_format" = "csv"}, name="sandboxwebsitebundle_admin_news_newsauthor_export")
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function exportAction($_format)
    {
        return parent::doExportAction($this->getAdminListConfigurator(), $_format);
    }

}
