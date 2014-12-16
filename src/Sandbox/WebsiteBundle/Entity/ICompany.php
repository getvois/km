<?php
namespace Sandbox\WebsiteBundle\Entity;


use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;

interface ICompany {
    /**
     * Add companies
     *
     * @param CompanyOverviewPage $companies
     * @return HasNodeInterface
     */
    public function addCompany(CompanyOverviewPage $companies);

    /**
     * Remove companies
     *
     * @param CompanyOverviewPage $companies
     */
    public function removeCompany(CompanyOverviewPage $companies);

    /**
     * Get companies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCompanies();
} 