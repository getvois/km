<?php
namespace Sandbox\WebsiteBundle\Entity;


interface IHostable {
    /**
     * Add hosts
     * @param Host $host
     */
    public function addHost(Host $host);

    /**
     * Remove hosts
     *
     * @param Host $host
     */
    public function removeHost(Host $host);

    /**
     * Get hosts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getHosts();
} 