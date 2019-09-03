<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ConferenceRepository")
 */
class Conference
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     */
    private $average;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Vote", mappedBy="conference")
     */
    private $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->average = 0;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getAverage()
    {
        return $this->average;
    }

    public function setAverage(float $average)
    {
        $this->average = $average;

        return $this;
    }

    /**
     * @return Collection|Vote[]
     */
    public function getVotes()
    {
        return $this->votes;
    }

    public function addVote(Vote $vote)
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setConference($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote)
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getConference() === $this) {
                $vote->setConference(null);
            }
        }

        return $this;
    }
}
