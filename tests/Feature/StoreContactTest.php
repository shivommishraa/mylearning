<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_shows_the_ssg_mart_map_and_directions(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d112178.64480204455', false)
            ->assertSee('!2sSSG%20Mart', false)
            ->assertSee('title="SSG MART location on Google Maps"', false)
            ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination=28.5222018%2C77.40182545', false);
    }
}
