<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\ORM\Mapping as ORM;

/**
 * SourcePagePart
 *
 * @ORM\Table(name="sb_source_page_parts")
 * @ORM\Entity
 */
class SourcePagePart extends \Kunstmaan\PagePartBundle\Entity\AbstractPagePart
{
    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=255, nullable=true)
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", length=255, nullable=true)
     */
    private $link;


    /**
     * Set text
     *
     * @param string $text
     *
     * @return SourcePagePart
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set link
     *
     * @param string $link
     *
     * @return SourcePagePart
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:PageParts:SourcePagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return \Sandbox\WebsiteBundle\Form\PageParts\SourcePagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new \Sandbox\WebsiteBundle\Form\PageParts\SourcePagePartAdminType();
    }
}