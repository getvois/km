<?php

namespace Sandbox\WebsiteBundle\Controller;

use Sandbox\WebsiteBundle\AdminList\PlaceAdminListConfigurator;
use Kunstmaan\AdminListBundle\Controller\AdminListController;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AdminListConfiguratorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * The admin list controller for Place
 */
class PlaceAdminListController extends AdminListController
{

    /**
     * @var AdminListConfiguratorInterface
     */
    private $configurator;

    /**
     * @return AdminListConfiguratorInterface
     */
    public function getAdminListConfigurator()
    {
        if (!isset($this->configurator)) {
            $this->configurator = new PlaceAdminListConfigurator($this->getDoctrine()->getManager());
        }

        return $this->configurator;
    }

    /**
     * The index action
     *
     * @Route("/", name="sandboxwebsitebundle_admin_place")
     */
    public function indexAction()
    {
        return parent::doIndexAction($this->getAdminListConfigurator());
    }

    /**
     * The add action
     *
     * @Route("/add", name="sandboxwebsitebundle_admin_place_add")
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
     * @Route("/{id}", requirements={"id" = "\d+"}, name="sandboxwebsitebundle_admin_place_edit")
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
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="sandboxwebsitebundle_admin_place_delete")
     * @Method({"GET", "POST"})
     *
     * @return array
     */
    public function deleteAction($id)
    {
        return parent::doDeleteAction($this->getAdminListConfigurator(), $id);
    }

    /**
     * The export action
     *
     * @param string $_format
     *
     * @Route("/export.{_format}", requirements={"_format" = "csv|xlsx"}, name="sandboxwebsitebundle_admin_place_export")
     * @Method({"GET", "POST"})
     * @return array
     */
    public function exportAction($_format)
    {
        return parent::doExportAction($this->getAdminListConfigurator(), $_format);
    }

}
