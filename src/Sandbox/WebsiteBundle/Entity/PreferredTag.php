<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\TaggingBundle\Entity\Tag;

/**
 * PreferredTag
 *
 * @ORM\Table(name="sb_preferred_tag")
 * @ORM\Entity
 */
class PreferredTag extends AbstractEntity
{
    /**
     * @var Tag
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\TaggingBundle\Entity\Tag")
     */
    private $tag;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=true)
     */
    private $class;

    /**
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * Set class
     *
     * @param string $class
     * @return PreferredTag
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }
}
