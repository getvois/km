<?php
namespace Sandbox\WebsiteBundle\Entity\Form;


class Passenger {
    private $firstName;
    private $lastName;
    private $sex;
    private $birthDay;
    private $nationality;
    private $bNum;

    /**
     * @return mixed
     */
    public function getBNum()
    {
        return $this->bNum;
    }

    /**
     * @param mixed $bNum
     * @return $this
     */
    public function setBNum($bNum)
    {
        $this->bNum = $bNum;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBirthDay()
    {
        return $this->birthDay;
    }

    /**
     * @param mixed $birthDay
     * @return $this
     */
    public function setBirthDay($birthDay)
    {
        $this->birthDay = $birthDay;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param mixed $nationality
     * @return $this
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param mixed $sex
     * @return $this
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
        return $this;
    }


} 