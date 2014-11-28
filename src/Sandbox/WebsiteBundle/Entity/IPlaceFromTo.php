<?php
namespace Sandbox\WebsiteBundle\Entity;


use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;

interface IPlaceFromTo {

    /**
     * Get full entity name
     * @return string
     */
    public function getEntityName();

    /**
     * Add fromPlaces
     *
     * @param PlaceOverviewPage $fromPlaces
     */
    public function addFromPlace(PlaceOverviewPage $fromPlaces);

    /**
     * Add place
     *
     * @param PlaceOverviewPage $children
     */
    public function addPlace(PlaceOverviewPage $children);

    /**
     * Get places
     *
     * @return \Doctrine\Common\Collections\Collection|PlaceOverviewPage[]
     */
    public function getPlaces();

    /**
     * Get fromPlaces
     *
     * @return \Doctrine\Common\Collections\Collection|PlaceOverviewPage[]
     */
    public function getFromPlaces();

    /**
     * Remove all places
     */
    public function removeAllPlaces();

    /**
     * Remove all from places
     */
    public function removeAllFromPlaces();
} 