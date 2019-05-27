<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="Books")
 */
class Book
{
    function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $publication_date;

    /**
     * @ORM\ManyToMany(targetEntity="Author", inversedBy="books")
     * @ORM\JoinTable(name="books_authors")
     */
    private $authors;
}


/**
 * @ORM\Entity
 * @ORM\Table(name="Authors")
 */
class Author
{
    function __construct($name)
    {
        $this->name = $name;
        $this->books = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Book", mappedBy="authors")
     */
    private $books;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    public function save(ObjectManager $entityManager)
    {
        $entityManager->persist($this);
        $entityManager->flush();
    }

    public function delete(ObjectManager $entityManager)
    {
        $entityManager->remove($this);
        $entityManager->flush();
    }

    /**
     * @return mixed
     */
    public function getBooks()
    {
        return $this->books;
    }

    /**
     * @param mixed $books
     */
    public function setBooks($books): void
    {
        $this->books = $books;
    }
}