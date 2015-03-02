<?php

namespace Sandbox\WebsiteBundle\Twig\Extension;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Twig_SimpleFunction;

class GetSubNodesExtension extends \Twig_Extension{

    /** @var  EntityManager */
    private $em;
    private $acl;

    function __construct(EntityManager $em, $acl)
    {
        $this->em = $em;
        $this->acl = $acl;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('get_sub_nodes', array($this, 'getSubNodes')),
        );
    }

    public function getSubNodes(Node $node, $lang){
        return $this->em->getRepository('KunstmaanNodeBundle:Node')
            ->getChildNodes($node->getId(), $lang, "VIEW", $this->acl);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'get_sub_nodes';
    }
}