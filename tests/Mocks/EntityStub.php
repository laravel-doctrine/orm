<?php

namespace LaravelDoctrine\Tests\Mocks;

/**
 * @Entity
 */
class EntityStub
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    public $id;

    /**
     * @Column(type="string")
     */
    public $name;

    /**
     * @ManyToMany(targetEntity="EntityStub")
     * @JoinTable(name="stub_stubs",
     *      joinColumns={@JoinColumn(name="owner_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="owned_id", referencedColumnName="id")}
     * )
     */
    public $others;
}