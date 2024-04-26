<?php

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: TrackRepository::class)]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['read','create','update'])]
    #[Assert\NotBlank(groups: ['create'])]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['read','create','update'])]
    private ?int $duration = null;

    #[ORM\ManyToOne(inversedBy: 'tracks')]
    private ?Album $track_album = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getTrackAlbum(): ?Album
    {
        return $this->track_album;
    }

    public function setTrackAlbum(?Album $track_album): static
    {
        $this->track_album = $track_album;

        return $this;
    }
}
