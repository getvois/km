<?php

namespace Sandbox\WebsiteBundle\Helper;


use Doctrine\ORM\EntityManager;

class NodeHelper {

    private $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * @param $class string format: 'Sandbox\WebsiteBundle\Entity\News\NewsPage'
     * @param $offset
     * @param $limit
     * @param null $host
     */
    public function getFullNodes($class, $lang = null, $offset = 0, $limit = null, $host = null)
    {
        $dql = "SELECT p, nv, nt, n
FROM $class p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node
WHERE n.deleted = 0
AND n.refEntityName = '$class'
AND nt.online = 1";

        if ($lang) $dql .= " AND nt.lang = :lang ";

        $query = $this->em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);


        if($limit){
            $query->setMaxResults($limit);
            if($offset){
                $query->setFirstResult($offset);
            }
        }

        $objects = $this->parsResult($query->getResult(), $class);

        return $objects;
    }


    /**
     * @param $where string (p, nv, nt, n tables) AND $where
     * @param array $params array [":key" => "value"]
     * @param $class string format: 'Sandbox\WebsiteBundle\Entity\News\NewsPage'
     * @param null $lang
     * @param int $offset
     * @param null $limit
     * @param null $host
     * @return FullNode[]
     */
    public function getFullNodesWithParam($where, $params = [], $class, $lang = null, $offset = 0, $limit = null, $host = null, $orderBy = '')
    {
        $dql = "SELECT p, nv, nt, n
FROM $class p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node ";

        if($class == 'Sandbox\WebsiteBundle\Entity\News\NewsPage'
        || $class == 'Sandbox\WebsiteBundle\Entity\Article\ArticlePage'){
            $dql .= ' LEFT JOIN Kunstmaan\MediaBundle\Entity\Media m WITH p.image = m.id ';
        }

        $dql .= " WHERE n.deleted = 0
AND n.refEntityName = '$class'
AND nt.online = 1";

        if ($lang) $dql .= " AND nt.lang = :lang ";

        if($where)
            $dql .= " AND " . $where;

        if($orderBy)
            $dql .= " ORDER BY " . $orderBy;

        $query = $this->em->createQuery($dql);
        if($lang) $query->setParameter(':lang', $lang);

        foreach ($params as $key => $value) {
            $query->setParameter($key, $value);
        }

        if($limit){
            $query->setMaxResults($limit);
            if($offset){
                $query->setFirstResult($offset);
            }
        }

        $objects = $this->parsResult($query->getResult(), $class);

        return $objects;
    }

    private function parsResult($objects, $class)
    {
        $result = [];
        $fullNode = null;
        foreach ($objects as $object) {
            if($object instanceof $class){
                $fullNode = new FullNode();
                $fullNode->setPage($object);
            }elseif($object instanceof \Kunstmaan\NodeBundle\Entity\NodeVersion){
                $fullNode->setVersion($object);
            }elseif($object instanceof \Kunstmaan\NodeBundle\Entity\NodeTranslation){
                $fullNode->setTranslation($object);
            }elseif($object instanceof \Kunstmaan\NodeBundle\Entity\Node){
                $fullNode->setNode($object);

                $result[] = $fullNode;
            }
        }

        return $result;
    }

}