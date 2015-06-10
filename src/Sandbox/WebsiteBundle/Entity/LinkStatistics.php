<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;

/**
 * LinkStatistics
 *
 * @ORM\Table(name="sb_link_statistics")
 * @ORM\Entity
 */
class LinkStatistics extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=512)
     */
    private $url;

    /**
     * @var integer
     *
     * @ORM\Column(name="clicks", type="integer")
     */
    private $clicks;

    /**
     * Set url
     *
     * @param string $url
     *
     * @return LinkStatistics
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set clicks
     *
     * @param integer $clicks
     *
     * @return LinkStatistics
     */
    public function setClicks($clicks)
    {
        $this->clicks = $clicks;

        return $this;
    }

    /**
     * Get clicks
     *
     * @return integer
     */
    public function getClicks()
    {
        return $this->clicks;
    }
}

