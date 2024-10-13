========
Entities
========

Out of the box, this package uses the default Laravel connection from
the ``.env`` file ``DB_CONNECTION``, which means that you are ready to start
fetching and persisting.

.. code-block:: php

  $scientist = new Scientist(
      'Albert',
      'Einstein'
  );

  $scientist->addTheory(
      new Theory('Theory of relativity')
  );

  EntityManager::persist($scientist);
  EntityManager::flush();

Unlike Eloquent, Doctrine is **not** an Active Record pattern, but a
Data Mapper pattern. Every Active Record model extends a base class
(with all the database logic), which has a lot of overhead and dramatically
slows down your application with thousands or millions of records.
Doctrine entities don't extend any class, they are just regular PHP classes
with properties and getters and setters.
Generally the properties are protected or private, so they only can be accessed
through getters and setters.

However, with property hooks in 8.4, entities composed of gloal properties only
may become the norm.

The domain/business logic is completely separated from the persistence logic.
This means we have to tell Doctrine how it should map the columns from the
database to our Entity class. In this example we are using annotations.
Other possiblities are YAML, XML or PHP arrays.

Entities are objects with identity. Their identity has a conceptual meaning
inside your domain. In an application each article has a unique id. You can
uniquely identify each article by that id.

Relations are stored in ``ArrayCollection``. It's advised to always set this
default value in the constructor. ``$this->theories = new ArrayCollection()``.
You can easily add on new relations with ``->add()``, remove them with
``->removeElement()`` or check if the relation is already defined with
``->contains()``

The ``Scientist`` entity used in the example above looks like this when using
annotations for the meta data.


.. code-block:: php

  use Doctrine\ORM\Mapping as ORM;
  use Doctrine\Common\Collections\ArrayCollection;
  use Doctrine\Common\Collections\Collection;

  #[ORM\Entity]
  class Scientist
  {
      #[ORM\Id]
      #[ORM\Column(type: "integer")]
      #[ORM\GeneratedValue(strategy: "AUTO")]
      private int $id;

      #[ORM\Column(type: "string", nullable: false)]
      private string $firstname;

      #[ORM\Column(type: "string", nullable: false)]
      private string $lastname;

      #[ORM\OneToMany(targetEntity: \Theory::class, mappedBy: "scientist")]
      private Collection $theories;

      /**
      * @param $firstname
      * @param $lastname
      */
      public function __construct()
      {
          $this->theories = new ArrayCollection();
      }

      public function getId(): int
      {
          return $this->id;
      }

      public function getFirstname(): string
      {
          return $this->firstname;
      }

      public function getLastname(): string
      {
          return $this->lastname;
      }

      public function addTheory(Theory $theory): self
      {
          if (! $this->theories->contains($theory)) {
              $theory->setScientist($this);
              $this->theories->add($theory);
          }

          return $this;
      }

      public function getTheories(): Collection
      {
          return $this->theories;
      }
  }


The related `Theory` entity would look like this:

.. code-block:: php

  use Doctrine\ORM\Mapping as ORM;

  #[ORM\Entity]
  class Theory
  {
      #[ORM\Id]
      #[ORM\Column(type: "integer")]
      #[ORM\GeneratedValue(strategy: "AUTO")]
      private int $id;

      #[ORM\Column(type: "string", nullable: false)]
      private string $title;

      #[ORM\ManyToOne(targetEntity: \Scientist::class, inversedBy: "theories")]
      #[ORM\JoinColumn(name: "scientist_id", referencedColumnName: "id", nullable: false)]
      private Scientist $scientist;

      public function __construct()
      {
      }

      public function getId(): int
      {
          return $this->id;
      }

      public function getTitle(): string
      {
          return $this->title;
      }

      public function setScientist(Scientist $scientist): self
      {
          $this->scientist = $scientist;

          return $this;
      }

      public function getScientist(): Scientist
      {
          return $this->scientist;
      }
  }


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst