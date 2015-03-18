<?php

namespace Sandbox\WebsiteBundle\Entity;


trait TSocialLinks {

    /**
     * @var bool
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