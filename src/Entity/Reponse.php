<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 350)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $Question = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisateur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_edit = null;

    #[ORM\Column(nullable: true)]
    private ?int $vote = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'votes')]
    private Collection $voters;

    public function __construct()
    {
        $this->voters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->Question;
    }

    public function setQuestion(?Question $Question): static
    {
        $this->Question = $Question;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getDateEdit(): ?\DateTimeInterface
    {
        return $this->date_edit;
    }

    public function setDateEdit(?\DateTimeInterface $date_edit): static
    {
        $this->date_edit = $date_edit;

        return $this;
    }

    public function getVote(): ?int
    {
        return $this->vote;
    }

    public function setVote(?int $vote): static
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getVoters(): Collection
    {
        return $this->voters;
    }

    public function addVoter(User $voter): static
    {
        if (!$this->voters->contains($voter)) {
            $this->voters->add($voter);
            $voter->addVote($this);
        }

        return $this;
    }

    public function removeVoter(User $voter): static
    {
        if ($this->voters->removeElement($voter)) {
            $voter->removeVote($this);
        }

        return $this;
    }


}
