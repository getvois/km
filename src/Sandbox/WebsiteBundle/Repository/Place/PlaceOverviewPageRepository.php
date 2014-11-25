<?php

namespace Sandbox\WebsiteBundle\Repository\Place;

use Kunstmaan\ArticleBundle\Repository\AbstractArticleOverviewPageRepository;

/**
 * Repository class for the PlaceOverviewPage
 */
class PlaceOverviewPageRepository extends AbstractArticleOverviewPageRepository
{

    public function getByLang($lang)
    {
        $pageIds = $this->getEntityManager()->createQueryBuilder()
            ->select('v.refId')
            ->from('KunstmaanNodeBundle:NodeVersion', 'v')
            ->join('v.nodeTranslation', 't')
            ->join('t.node', 'n')
            ->where('n.deleted = 0')
            ->where('t.lang = :lang')
            ->andWhere('v.refEntityName = :name')
            ->andWhere('v.type = :type')
            ->setParameter(':type', 'public')
            ->setParameter(":lang", $lang)
            ->setParameter(":name", 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage')
            ->getQuery()
            ->getResult();

        $ids = [];
        foreach ($pageIds as $id) {
            $ids[] = $id['refId'];
        }

        return $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from('SandboxWebsiteBundle:Place\PlaceOverviewPage', 'p')
            ->where('p.id IN(:ids)')
            ->orderBy('p.title')
            ->setParameter(':ids', $ids);

    }
}
