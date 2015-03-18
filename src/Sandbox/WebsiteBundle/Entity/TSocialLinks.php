<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait TSocialLinks {

    /**
     * @var bool
     *
     * @ORM\Column(name="post_on_fb", type="boolean", nullable=true)
     */
    private $post_on_fb;

    /**
     * @return boolean
     */
    public function isPostOnFb()
    {
        return $this->post_on_fb;
    }

    /**
     * @param boolean $post_on_fb
     */
    public function setPostOnFb($post_on_fb)
    {
        $this->post_on_fb = $post_on_fb;
    }
}