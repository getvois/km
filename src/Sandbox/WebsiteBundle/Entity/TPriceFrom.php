<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait TPriceFrom {

    /**
     * @var string
     *
     * @ORM\Column(name="price_from", type="string", length=255, nullable=true)
     *
     */
    private $priceFrom;

    /**
     * @return string
     */
    public function getPriceFrom()
    {
        return $this->priceFrom;
    }

    /**
     * @param string $priceFrom
     * @return $this
     */
    public function setPriceFrom($priceFrom)
    {
        $this->priceFrom = $priceFrom;
        return $this;
    }


} 