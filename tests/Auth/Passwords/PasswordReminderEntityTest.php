<?php

use LaravelDoctrine\ORM\Auth\Passwords\PasswordReminder;

class PasswordReminderEntityTest extends PHPUnit_Framework_TestCase
{
    public function test_init_new_entity()
    {
        $reminder = new PasswordReminder('email', 'token');

        $this->assertInstanceOf(PasswordReminder::class, $reminder);
    }

    public function test_can_get_created_at_date()
    {
        $reminder = new PasswordReminder('email', 'token');

        $this->assertEquals(new DateTime('now'), $reminder->getCreatedAt());
    }
}
