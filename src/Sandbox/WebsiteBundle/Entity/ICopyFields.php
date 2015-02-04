<?php

namespace Sandbox\WebsiteBundle\Entity;


use Kunstmaan\MediaBundle\Entity\Media;

interface ICopyFields {

    /**
     * @param \DateTime $date
     */
    public function setDate($date);

    /**
     * @return \DateTime
     */
    public function getDate();

    /**
     * Set topImage
     *
     * @param TopImage $topImage
     * @return ICopyFields
     */
    public function setTopImage(TopImage $topImage = null);

    /**
     * Get topImage
     *
     * @return TopImage
     */
    public function getTopImage();

    /**
     * @return Media
     */
    public function getImage();

    /**
     * @param Media $image
     * @return ICopyFields
     */
    public function setImage($image);

    /**
     * @return string
     */
    public function getPriceFromLabel();

    /**
     * @param string $priceFromLabel
     * @return ICopyFields
     */
    public function setPriceFromLabel($priceFromLabel);

}