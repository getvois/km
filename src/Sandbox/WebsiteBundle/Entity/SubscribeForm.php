<?php
namespace Sandbox\WebsiteBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class SubscribeForm {

    /**
     * @var int
     *
     * @Assert\NotBlank()
     */
    private $node;

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
     * @return int
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param int $node
     * @return $this
     */
    public function setNode($node)
    {
        $this->node = $node;
        return $this;
    }


} 