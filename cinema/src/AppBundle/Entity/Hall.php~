<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="hall")
 */
class Hall
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
     * @ORM\Column(type="string", length=60)
     */
    private $cinemaName;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $hallName;

    /**
     * @ORM\Column(type="integer")
     */
    private $rows;

    /**
     * @ORM\Column(type="integer")
     */
    private $seats;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cinemaName
     *
     * @param string $cinemaName
     *
     * @return Hall
     */
    public function setCinemaName($cinemaName)
    {
        $this->cinemaName = $cinemaName;

        return $this;
    }

    /**
     * Get cinemaName
     *
     * @return string
     */
    public function getCinemaName()
    {
        return $this->cinemaName;
    }

    /**
     * Set hallName
     *
     * @param string $hallName
     *
     * @return Hall
     */
    public function setHallName($hallName)
    {
        $this->hallName = $hallName;

        return $this;
    }

    /**
     * Get hallName
     *
     * @return string
     */
    public function getHallName()
    {
        return $this->hallName;
    }

    /**
     * Set rows
     *
     * @param integer $rows
     *
     * @return Hall
     */
    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * Get rows
     *
     * @return integer
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Set seats
     *
     * @param integer $seats
     *
     * @return Hall
     */
    public function setSeats($seats)
    {
        $this->seats = $seats;

        return $this;
    }

    /**
     * Get seats
     *
     * @return integer
     */
    public function getSeats()
    {
        return $this->seats;
    }
}
