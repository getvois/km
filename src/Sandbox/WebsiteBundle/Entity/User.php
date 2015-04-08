<?php

namespace Sandbox\WebsiteBundle\Entity;

use Kunstmaan\AdminBundle\Entity\BaseUser;

use Doctrine\ORM\Mapping as ORM;

/**
 * User entity
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\UserRepository")
 * @ORM\Table(name="sb_users")
 */
class User extends BaseUser{

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", nullable=true)
     */
    private $hash;
    /**
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;
    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", nullable=true)
     */
    private $host;
    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", nullable=true)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", nullable=true)
     */
    private $city;

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }



    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    protected function getFormTypeClass()
    {
        return 'SandBox\WebsiteBundle\Form\UserType';
    }

    protected function getAdminListConfiguratorClass()
    {
        return 'SandBox\WebsiteBundle\AdminList\UserAdminListConfigurator';
    }

    public function getGroups()
    {
        $groups = parent::getGroups();
        if(!$groups) return [];
        return $groups;
    }


}