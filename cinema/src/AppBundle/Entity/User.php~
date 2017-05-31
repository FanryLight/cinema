<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $coins;

    public function __construct()
    {
        parent::__construct();
        $this->coins = 0;
        // your own logic
    }

    /**
     * Set coins
     *
     * @param integer $coins
     *
     * @return User
     */
    public function setCoins($coins)
    {
        $this->coins = $coins;

        return $this;
    }

    /**
     * Get coins
     *
     * @return integer
     */
    public function getCoins()
    {
        return $this->coins;
    }
}
