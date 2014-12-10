<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\NodeBundle\Entity\Node;

/**
 * Subscription
 *
 * @ORM\Table(name="sb_subscription")
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\SubscriptionRepository")
 */
class Subscription extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", length=255)
     */
    private $host;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;


    /**
     * @var Collection
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\NodeBundle\Entity\Node")
     */
    private $node;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=2)
     */
    private $lang;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=40)
     */
    private $hash;

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return $this Subscription
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Subscription
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set lang
     *
     * @param string $lang
     * @return Subscription
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string 
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Subscription
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set node
     *
     * @param Node $node
     * @return Subscription
     */
    public function setNode(Node $node = null)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Get node
     *
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }
}
