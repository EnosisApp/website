<?php

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppUser
 *
 * @ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass="ApiBundle\Repository\AppUserRepository")
 */
class AppUser
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
     * @ORM\Column(name="identifier", type="string", length=64, unique=true)
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=64, nullable=true, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64, nullable=true)
     */
    private $password;

    /**
     * @var array
     *
     * @ORM\Column(name="infos", type="json_array")
     */
    private $infos = [];

    /**
     * @var array
     *
     * @ORM\Column(name="age", type="string", length=12)
     */
    private $age = "";

    /**
     * @var array
     *
     * @ORM\Column(name="handicapLevel", type="boolean")
     */
    private $handicap;

    public function __construct() {
        $this->handicap = false;
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
     * Set identifier
     *
     * @param string $identifier
     *
     * @return AppUser
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return AppUser
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return AppUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set infos
     *
     * @param array $infos
     *
     * @return AppUser
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;

        return $this;
    }

    /**
     * Get infos
     *
     * @return array
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * Set age
     *
     * @param string $age
     *
     * @return AppUser
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return string
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set handicap
     *
     * @param boolean $handicap
     *
     * @return AppUser
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
}
