# Entities

Out of the box this package uses the default Laravel connection from the `.env` file `DB_CONNECTION`, which means that you are ready to start fetching and persisting.

```php
<?php

$scientist = new Scientist(
    'Albert', 
    'Einstein'
);

$scientist->addTheory(
    new Theory('Theory of relativity')
);

EntityManager::persist($scientist);
EntityManager::flush();
```

Unlike Eloquent, Doctrine is not an Active Record pattern, but a Data Mapper pattern. Every Active Record model extends a base class (with all the database logic), which has a lot of overhead and dramatically slows down your application with thousands or millions of records.
Doctrine entities don't extend any class, they are just regular PHP classes with properties and getters and setters. 
Generally the properties are protected or private, so they only can be accessed through getters and setters.

The domain/business logic is completely separated from the persistence logic. 
This means we have to tell Doctrine how it should map the columns from the database to our Entity class. In this example we are using annotations. Other possiblities are YAML, XML or PHP arrays.

Entities are objects with identity. Their identity has a conceptual meaning inside your domain. In an application each article has a unique id. You can uniquely identify each article by that id.

Relations are stored in `ArrayCollection`. It's advised to always set this default value in the constructor. `$this->theories = new ArrayCollection`. 
You can easily add on new relations with `->add()`, remove them with `->removeElement()` or check if the relation is already defined with `->contains()`

The `Scientist` entity used in the example above looks like this when using annotations for the meta data.

```php
<?php

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="scientist")
 */
class Scientist
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $firstname;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $lastname;
    
    /**
    * @ORM\OneToMany(targetEntity="Theory", mappedBy="scientist", cascade={"persist"})
    * @var ArrayCollection|Theory[]
    */
    protected $theories;
    
    /**
    * @param $firstname
    * @param $lastname
    */
    public function __construct($firstname, $lastname)
    {
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        
        $this->theories = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }
    
    public function getLastname()
    {
        return $this->lastname;
    }
    
    public function addTheory(Theory $theory)
    {
        if(!$this->theories->contains($theory)) {
            $theory->setScientist($this);
            $this->theories->add($theory);
        }
    }
    
    public function getTheories()
    {
        return $this->theories;
    }
}
```

The related `Theory` entity would look like this:

```php
<?php

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="theories")
 */
class Theory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;
    
    /**
    * @ORM\ManyToOne(targetEntity="Scientist", inversedBy="theories")
    * @var Scientist
    */
    protected $scientist;
    
    /**
    * @param $title
    */
    public function __construct($title)
    {
        $this->title = $title;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setScientist(Scientist $scientist)
    {
        $this->scientist = $scientist;
    }
    
    public function getScientist()
    {
        return $this->scientist;
    }
}
```
