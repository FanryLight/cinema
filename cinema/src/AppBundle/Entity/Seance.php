<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SeanceRepository")
 * @ORM\Table(name="seance")
 */
class Seance
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
     * @ORM\ManyToOne(targetEntity="Movie")
     * @ORM\JoinColumn(name="movie_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $movie;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $time;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="Hall")
     * @ORM\JoinColumn(name="hall_id", referencedColumnName="id")
     */
    private $hall;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $date;

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
     * Set time
     *
     * @param string $time
     *
     * @return Seance
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set date
     *
     * @param string $date
     *
     * @return Seance
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set hall
     *
     * @param \AppBundle\Entity\Hall $hall
     *
     * @return Seance
     */
    public function setHall(\AppBundle\Entity\Hall $hall = null)
    {
        $this->hall = $hall;

        return $this;
    }

    /**
     * Get hall
     *
     * @return \AppBundle\Entity\Hall
     */
    public function getHall()
    {
        return $this->hall;
    }

    /**
     * Set movie
     *
     * @param \AppBundle\Entity\Movie $movie
     *
     * @return Seance
     */
    public function setMovie(\AppBundle\Entity\Movie $movie = null)
    {
        $this->movie = $movie;

        return $this;
    }

    /**
     * Get movie
     *
     * @return \AppBundle\Entity\Movie
     */
    public function getMovie()
    {
        return $this->movie;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Seance
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }
}
