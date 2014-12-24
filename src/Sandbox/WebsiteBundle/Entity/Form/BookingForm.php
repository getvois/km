<?php
namespace Sandbox\WebsiteBundle\Entity\Form;


use Doctrine\Common\Collections\ArrayCollection;

class BookingForm {
    private $passengers;

    private $email;
    private $phone;

    private $ccNumber;
    private $ccExpMonth;
    private $ccExpYear;
    private $ccCVC;
    private $ccName;

    function __construct()
    {
        $this->passengers = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getCcCVC()
    {
        return $this->ccCVC;
    }

    /**
     * @param mixed $ccCVC
     * @return $this
     */
    public function setCcCVC($ccCVC)
    {
        $this->ccCVC = $ccCVC;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCcExpMonth()
    {
        return $this->ccExpMonth;
    }

    /**
     * @param mixed $ccExpMonth
     * @return $this
     */
    public function setCcExpMonth($ccExpMonth)
    {
        $this->ccExpMonth = $ccExpMonth;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCcExpYear()
    {
        return $this->ccExpYear;
    }

    /**
     * @param mixed $ccExpYear
     * @return $this
     */
    public function setCcExpYear($ccExpYear)
    {
        $this->ccExpYear = $ccExpYear;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCcName()
    {
        return $this->ccName;
    }

    /**
     * @param mixed $ccName
     * @return $this
     */
    public function setCcName($ccName)
    {
        $this->ccName = $ccName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCcNumber()
    {
        return $this->ccNumber;
    }

    /**
     * @param mixed $ccNumber
     * @return $this
     */
    public function setCcNumber($ccNumber)
    {
        $this->ccNumber = $ccNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return Passenger[]
     */
    public function getPassengers()
    {
        return $this->passengers;
    }

    /**
     * @param mixed $passenger
     * @return $this
     */
    public function setPassengers($passenger)
    {
        $this->passengers = $passenger;
        return $this;
    }


    /**
     * @param mixed $passenger
     * @return $this
     */
    public function addPassenger($passenger)
    {
        $this->passengers->add($passenger);
        return $this;
    }

    /**
     * @param mixed $passenger
     * @return $this
     */
    public function removePassenger($passenger)
    {
        $this->passengers->removeElement($passenger);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }


} 