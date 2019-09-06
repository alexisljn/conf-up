<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VoteRepository")
 */
class Vote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * * @Assert\Range(
     *      min = 0,
     *      max = 5,
     *      minMessage = "You must choose at least {{ limit }} as value",
     *      maxMessage = "You must choose a number under {{ limit }} "
     * )
     * @ORM\Column(type="integer")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="votes")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Conference", inversedBy="votes")
     */
    private $conference;

    public function getId()
    {
        return $this->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue(int $value)
    {
        $this->value = $value;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getConference()
    {
        return $this->conference;
    }

    public function setConference(Conference $conference)
    {
        $this->conference = $conference;

        return $this;
    }
}
