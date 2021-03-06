<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use ApiBundle\SessionManager\SessionManager;


/**
 * Poi
 *
 * @ORM\Table(name="poi")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PoiRepository")
 */
class Poi
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float")
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lon", type="float")
     */
    private $lon;

    /**
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     */
    private $caption;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Beacon", mappedBy="poi", cascade={"persist", "remove"})
     */
    private $beacons;

    /**
     * @var bool
     *
     * @ORM\Column(name="handicap", type="boolean")
     */
    private $handicap = 0;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="pois")
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=92)
     */
    private $city;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $cityLat;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    private $cityLon;

    /**
     * @var string
     *
     * @ORM\Column(name="pushNotification", type="string", length=140, nullable=true)
     */
    private $pushNotification;

    /**
     * @ORM\PostUpdate
     * @ORM\PostPersist
     */
    public function updateCityLatLonAndPersistToElasticSearchDatabase()
    {
        $client = SessionManager::_getClient();
        $params = [
            'index' => 'app',
            'type' => 'poi',
            'id' => $this->getId(),
            'body' => [
                'location' => [
                    'lat' => $this->getLat(),
                    'lon' => $this->getLon()
                ],
                'city' => $this->getCityLat().";".$this->getCityLon(),
                'caption' => $this->getCaption(),
                'address' => $this->getAddress(),
                'type' => $this->getType(),
                'accessible' => $this->getHandicap(),
                'pushNotification' => $this->getPushNotification()
            ]
        ];

        $response = $client->index($params);

        SessionManager::updateBeaconsInElasticForPoi($this);
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        $params = [
            'index' => 'app',
            'type' => 'poi',
            'id' => $this->getId()
        ];

        SessionManager::_getClient()->delete($params);
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Poi
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set lat
     *
     * @param float $lat
     *
     * @return Poi
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lon
     *
     * @param float $lon
     *
     * @return Poi
     */
    public function setLon($lon)
    {
        $this->lon = $lon;

        return $this;
    }

    /**
     * Get lon
     *
     * @return float
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * Set caption
     *
     * @param string $caption
     *
     * @return Poi
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Poi
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->beacons = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add beacon
     *
     * @param \AppBundle\Entity\Beacon $beacon
     *
     * @return Poi
     */
    public function addBeacon(\AppBundle\Entity\Beacon $beacon)
    {
        $this->beacons[] = $beacon;
        $beacon->setPoi($this);

        return $this;
    }

    /**
     * Remove beacon
     *
     * @param \AppBundle\Entity\Beacon $beacon
     */
    public function removeBeacon(\AppBundle\Entity\Beacon $beacon)
    {
        $this->beacons->removeElement($beacon);
    }

    /**
     * Get beacons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBeacons()
    {
        return $this->beacons;
    }

    /**
     * Set handicap
     *
     * @param boolean $handicap
     *
     * @return Poi
     */
    public function setHandicap($handicap)
    {
        $this->handicap = $handicap;

        return $this;
    }

    /**
     * Get handicap
     *
     * @return boolean
     */
    public function getHandicap()
    {
        return $this->handicap;
    }

    /**
     * Set owner
     *
     * @param \UserBundle\Entity\User $owner
     *
     * @return Poi
     */
    public function setOwner(\UserBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \UserBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Poi
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Poi
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set cityLat
     *
     * @param float $cityLat
     *
     * @return Poi
     */
    public function setCityLat($cityLat)
    {
        $this->cityLat = $cityLat;

        return $this;
    }

    /**
     * Get cityLat
     *
     * @return float
     */
    public function getCityLat()
    {
        return $this->cityLat;
    }

    /**
     * Set cityLon
     *
     * @param float $cityLon
     *
     * @return Poi
     */
    public function setCityLon($cityLon)
    {
        $this->cityLon = $cityLon;

        return $this;
    }

    /**
     * Get cityLon
     *
     * @return float
     */
    public function getCityLon()
    {
        return $this->cityLon;
    }

    /**
     * Set pushNotification
     *
     * @param string $pushNotification
     *
     * @return Poi
     */
    public function setPushNotification($pushNotification)
    {
        $this->pushNotification = $pushNotification;

        return $this;
    }

    /**
     * Get pushNotification
     *
     * @return string
     */
    public function getPushNotification()
    {
        return $this->pushNotification;
    }
}
