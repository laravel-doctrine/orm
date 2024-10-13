========
Metadata
========

Because Doctrine entities do not extend any smart ancestor class, we have to tell
Doctrine how to map the data from the database into the entity. There are
multiple ways of doing this.


Attributes
==========

The attributes driver requires php 8 or above. It searches the entities in
the `app` folder, but you can change this to whatever folder
(or multiple folders) you like. Attributes means, that you will use attributes
to indicate the column mappings.

.. code-block:: php

  namespace App\Entities;

  use Doctrine\ORM\Mapping\Column;
  use Doctrine\ORM\Mapping\Entity;
  use Doctrine\ORM\Mapping\GeneratedValue;
  use Doctrine\ORM\Mapping\Id;
  use Doctrine\ORM\Mapping\Table;

  #[Entity]
  #[Table(name: "articles")]
  class Article
  {
      #[Id, Column(type: "integer"), GeneratedValue()]
      protected $id;

      #[Column(type: "string")]
      protected $title;

      public function getId()
      {
          return $this->id;
      }

      public function getTitle()
      {
          return $this->title;
      }

      public function setTitle($title)
      {
          $this->title = $title;
      }
  }

More about the attributes driver:
https://www.doctrine-project.org/projects/doctrine-orm/en/2.11/reference/attributes-reference.html


XML
===

Another option is to us XML mappings. It's better to change the
 meta data paths to something like ``config_path('doctrine_orm_metadata')``.

App.Entities.Article.dcm.xml

.. code-block:: xml

  <?xml version="1.0" encoding="UTF-8"?>
  <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                            http://raw.github.com/doctrine/doctrine2/master/doctrine-mapping.xsd">

      <entity name="App\Entities\Article" table="articles">
          <id name="id" type="integer" column="id">
              <generator strategy="AUTO"/>
              <sequence-generator sequence-name="tablename_seq" allocation-size="100" initial-value="1" />
          </id>
          <field name="title" column="title" type="string" />
      </entity>
  </doctrine-mapping>


More information about XML mappings:
https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/xml-mapping.html

Config files
------------

This package adds another option, which leverages Laravel's config system.
In addition to setting `meta`, this also requires setting `mapping_file`.
You could for example create a `config/mappings.php` file to provide mapping
information. Here is an example of setting the `meta` and `mapping_file`
config properties inside of `config/doctrine.php` to use config file-based
metadata.

.. code-block:: php

  return [
      'managers' => [
          'default' => [
              'meta'       => env('DOCTRINE_METADATA', 'config'),
              'mapping_file' => 'mappings',


The array structure in `config/mappings.php`:

.. code-block:: php

  return [
      'App\Entities\Article' => [
          'type'   => 'entity',
          'table'  => 'articles',
          'id'     => [
              'id' => [
                  'type'     => 'integer',
                  'generator' => [
                      'strategy' => 'auto'
                  ]
              ],
          ],
          'fields' => [
              'title' => [
                  'type' => 'string'
              ]
          ]
      ]
  ];


Static PHP
==========

When you change the meta data driver setting to `static_php`, your entities
will expect a `loadMetadata` method.

.. code-block:: php

class Article
{
    protected $id;
    protected $title;

    public static function loadMetadata(Doctrine\ORM\Mapping\ClassMetadata $metadata)
    {
        $metadata->mapField(array(
           'id'        => true,
           'fieldName' => 'id',
           'type'      => 'integer'
        ));

        $metadata->mapField(array(
           'fieldName' => 'title',
           'type'      => 'string'
        ));
    }
}
```

More on the StaticPHP driver:
https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/php-mapping.html


.. role:: raw-html(raw)
   :format: html

.. include:: footer.rst