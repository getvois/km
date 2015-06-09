<?php

namespace Sandbox\WebsiteBundle\Entity\Place;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractArticlePage;
use Kunstmaan\NodeBundle\Controller\SlugActionInterface;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceAuthor;
use Sandbox\WebsiteBundle\Form\Place\PlacePageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\Place\PlacePagePagePartAdminConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Place\PlacePageRepository")
 * @ORM\Table(name="sb_place_pages")
 * @ORM\HasLifecycleCallbacks
 */
class PlacePage extends AbstractArticlePage implements SlugActionInterface
{

//    /**
//     * @ORM\OneToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlacePage", mappedBy="parentPlace")
//     **/
//    private $children;
//
//    /**
//     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlacePage", inversedBy="children")
//     **/
//    private $parentPlace;
//
//
//    /**
//     *
//     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\News\NewsPage", mappedBy="places")
//     */
//    private $news;


    /**
     * @var PlaceAuthor
     *
     * @ORM\ManyToOne(targetEntity="PlaceAuthor")
     * @ORM\JoinColumn(name="place_author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new PlacePageAdminType();
    }

    /**
     * @return array
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new PlacePagePagePartAdminConfigurator());
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Place/PlacePage:view.html.twig';
    }

    /**
     * Before persisting this entity, check the date.
     * When no date is present, fill in current date and time.
     *
     * @ORM\PrePersist
     */
    public function _prePersist()
    {
        // Set date to now when none is set
        if ($this->date == null) {
            $this->setDate(new \DateTime());
        }
    }
//
//    /**
//     * Constructor
//     */
//    public function __construct()
//    {
//        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
//        $this->news = new \Doctrine\Common\Collections\ArrayCollection();
//    }

    public function getControllerAction()
    {
        return "SandboxWebsiteBundle:BackwardCompatibility:service";
    }

    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        /** @var PlacePage $placePage */
        $placePage = $context['page'];
        //var_dump($context['page']); same as recource;

        $places = $placePage->getNews();
        $context['places'] = $places;

    }


//
//
//    /**
//     * Add children
//     *
//     * @param \Sandbox\WebsiteBundle\Entity\Place\PlacePage $children
//     * @return PlacePage
//     */
//    public function addChild(\Sandbox\WebsiteBundle\Entity\Place\PlacePage $children)
//    {
//        $this->children[] = $children;
//
//        return $this;
//    }
//
//    /**
//     * Remove children
//     *
//     * @param \Sandbox\WebsiteBundle\Entity\Place\PlacePage $children
//     */
//    public function removeChild(\Sandbox\WebsiteBundle\Entity\Place\PlacePage $children)
//    {
//        $this->children->removeElement($children);
//    }
//
//    /**
//     * Get children
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getChildren()
//    {
//        return $this->children;
//    }
//
//    /**
//     * Set parentPlace
//     *
//     * @param \Sandbox\WebsiteBundle\Entity\Place\PlacePage $parentPlace
//     * @return PlacePage
//     */
//    public function setParentPlace(\Sandbox\WebsiteBundle\Entity\Place\PlacePage $parentPlace = null)
//    {
//        $this->parentPlace = $parentPlace;
//
//        return $this;
//    }
//
//    /**
//     * Get parentPlace
//     *
//     * @return \Sandbox\WebsiteBundle\Entity\Place\PlacePage
//     */
//    public function getParentPlace()
//    {
//        return $this->parentPlace;
//    }
//
//    /**
//     * Add news
//     *
//     * @param NewsPage $news
//     * @return PlacePage
//     */
//    public function addNews(NewsPage $news)
//    {
//        $this->news[] = $news;
//
//        return $this;
//    }
//
//    /**
//     * Remove news
//     *
//     * @param NewsPage $news
//     */
//    public function removeNews(NewsPage $news)
//    {
//        $this->news->removeElement($news);
//    }
//
//    /**
//     * Get news
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getNews()
//    {
//        return $this->news;
//    }
}
