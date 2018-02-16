<?php

namespace UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Poi", mappedBy="owner", cascade={"remove"})
     */
    protected $pois;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    /**
     * Add pois
     *
     * @param \AppBundle\Entity\Poi $pois
     *
     * @return User
     */
    public function addPoi(\AppBundle\Entity\Poi $poi)
    {
        $this->pois[] = $poi;
        $poi->setOwner($this);

        return $this;
    }

    /**
     * Remove pois
     *
     * @param \AppBundle\Entity\Poi $pois
     */
    public function removePoi(\AppBundle\Entity\Poi $poi)
    {
        $this->pois->removeElement($poi);
    }

    /**
     * Get pois
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPois()
    {
        return $this->pois;
    }
}
