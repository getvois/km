<?php
namespace Sandbox\WebsiteBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class SubscribeForm {

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $node;

    /**
     * @var bool
     */
    private $subscribe;

    /**
     * @return boolean
     */
    public function isSubscribe()
    {
        return $this->subscribe;
    }

    /**
     * @param boolean $subscribe
     */
    public function setSubscribe($subscribe)
    {
        $this->subscribe = $subscribe;
    }

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getNode()
    {
        //return implode(",", $this->node);
        return $this->node;
    }

    /**
     * @param string $node
     * @return $this
     */
    public function setNode($node)
    {
        $this->node = $node;
        return $this;
    }


} 