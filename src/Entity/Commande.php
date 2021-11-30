<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\AST\Functions\SumFunction;

/**
 * @ORM\Entity(repositoryClass=CommandeRepository::class)
 */
class Commande
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reservation", mappedBy="commande")
     * @ORM\JoinColumn(nullable=true)
     */
    private $reservations;

    // ATTENTION A CORRIGER : remplacer le inversedBy="user" par "inversedBy="commandes" !!!!!!!
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="user")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    public function __construct() {
        $this->creationDate = new \DateTime('now'); //Nous initialisons notre variable datant la création de Commande
        $this->reservations = new ArrayCollection();
        $this->setStatus('panier');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setCommande($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCommande() === $this) {
                $reservation->setCommande(null);
            }
        }

        return $this;
    }

    public function getTotalPrice()
    {
        //Cette fonction calcule le prix total de tous les produits réservés par la Commande
        //Le calcul étant donc la somme de toutes les réservations calculée ains : product->price * quantity
        
        $totalPrice = 0;
        $reservations = $this->reservations;

        foreach($reservations as $reservation) {
            /* $product = $reservation->getProduct();
            $prix = $product->getPrice();
            $quantity = $reservation->getQuantity();
            $reservationPrice = $prix * $quantity;
            $totalPrice = $totalPrice + $reservationPrice; */
            $totalPrice += $reservation->getProduct()->getPrice() * $reservation->getQuantity();
        }

        return $totalPrice;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

}
