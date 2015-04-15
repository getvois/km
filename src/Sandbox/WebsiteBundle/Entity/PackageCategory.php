<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PackageCategory
 *
 * @ORM\Table(name="sb_package_category")
 * @ORM\Entity
 */
class PackageCategory extends \Kunstmaan\AdminBundle\Entity\AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * Set name
     *
     * @param string $name
     * @return PackageCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
}
