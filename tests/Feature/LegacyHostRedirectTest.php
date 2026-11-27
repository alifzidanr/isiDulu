<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegacyHostRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://layanan-ti.al-azhar.id', 'app.legacy_hosts' => 'isidulu.bag-itd.my.id']);
    }

    public function test_browsers_on_the_old_host_are_sent_to_the_new_one(): void
    {
        $this->get('http://isidulu.bag-itd.my.id/login?x=1')
            ->assertStatus(301)
            ->assertRedirect('https://layanan-ti.al-azhar.id/login?x=1');
    }

    public function test_the_roster_push_still_works_on_the_old_host(): void
    {
        // Unsigned, so 401 — the point is that it isn't redirected.
        $this->postJson('http://isidulu.bag-itd.my.id/kehadiran/sync', ['pegawai' => []])->assertUnauthorized();
    }

    public function test_the_new_host_is_served_normally(): void
    {
        $this->get('http://layanan-ti.al-azhar.id/login')->assertOk();
    }
}
