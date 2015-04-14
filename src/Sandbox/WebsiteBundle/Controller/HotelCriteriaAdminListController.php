<?php

namespace Sandbox\WebsiteBundle\Controller;

use Sandbox\WebsiteBundle\AdminList\HotelCriteriaAdminListConfigurator;
use Kunstmaan\AdminListBundle\Controller\AdminListController;
use Kunstmaan\AdminListBundle\AdminList\Configurator\AdminListConfiguratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * The admin list controller for HotelCriteria
 */
class HotelCriteriaAdminListController extends AdminListController
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
            $this->configurator = new HotelCriteriaAdminListConfigurator($this->getEntityManager());
        }

        return $this->configurator;
    }

    /**
     * The index action
     *
     * @Route("/", name="sandboxwebsitebundle_admin_hotelcriteria")
     */
    public function indexAction(Request $request)
    {
        return parent::doIndexAction($this->getAdminListConfigurator(), $request);
    }

    /**
     * The add action
     *
     * @Route("/add", name="sandboxwebsitebundle_admin_hotelcriteria_add")
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
     * @Route("/{id}", requirements={"id" = "\d+"}, name="sandboxwebsitebundle_admin_hotelcriteria_edit")
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
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="sandboxwebsitebundle_admin_hotelcriteria_delete")
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
     * @Route("/export.{_format}", requirements={"_format" = "csv|xlsx"}, name="sandboxwebsitebundle_admin_hotelcriteria_export")
     * @Method({"GET", "POST"})
     * @return array
     */
    public function exportAction(Request $request, $_format)
    {
        return parent::doExportAction($this->getAdminListConfigurator(), $_format, $request);
    }
}
