<?php

namespace Sandbox\WebsiteBundle\Repository\Company;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Kunstmaan\ArticleBundle\Repository\AbstractArticleOverviewPageRepository;

/**
 * Repository class for the CompanyOverviewPage
 */
class CompanyOverviewPageRepository extends AbstractArticleOverviewPageRepository
{

    public function getActive()
    {
        $res = $this->findActiveOverviewPages();

        $ids = [];
        foreach ($res as $page) {
            $ids[] = $page->getId();
        }

        return $this->getEntityManager()->createQueryBuilder()
            ->select('n')
            ->from('SandboxWebsiteBundle:Company\CompanyOverviewPage', 'n')
            ->where('n.id IN(:ids)')
            ->setParameter(":ids", $ids);

    }
}
