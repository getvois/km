<?php

namespace Sandbox\WebsiteBundle\Helper;


class NewsLetterEmailAccount {
    private $user;
    private $password;
    private $locale;
    private $filterPatterns = [];

    function __construct($user, $password, $locale)
    {
        $this->user = $user;
        $this->password = $password;
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getFilterPatterns()
    {
        return $this->filterPatterns;
    }

    /**
     * @param mixed $filterPatterns
     */
    public function setFilterPatterns($filterPatterns)
    {
        $this->filterPatterns = $filterPatterns;
    }

}