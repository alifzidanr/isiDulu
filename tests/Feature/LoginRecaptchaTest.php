<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginRecaptchaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('user', function (Blueprint $t) {
            $t->increments('id_user');
            $t->integer('id_kampus');
            $t->integer('id_unit')->nullable();
            $t->integer('access_level');
            $t->string('nama_lengkap');
            $t->string('email');
            $t->string('password');
            $t->string('status')->nullable();
            $t->string('remember_token')->nullable();
            $t->timestamps();
        });
        DB::table('user')->insert([
            'id_kampus' => 1, 'access_level' => 2, 'nama_lengkap' => 'Ani', 'email' => 'ani@x.id',
            'password' => Hash::make('rahasia123'), 'status' => 'active',
        ]);

        config(['services.recaptcha.site_key' => 'site', 'services.recaptcha.secret_key' => 'secret']);
    }

    private function google(array $response): void
    {
        Http::fake(['www.google.com/recaptcha/*' => Http::response($response)]);
    }

    public function test_login_with_a_good_token_succeeds(): void
    {
        $this->google(['success' => true, 'action' => 'login', 'score' => 0.9]);

        $this->post('/login', ['email' => 'ani@x.id', 'password' => 'rahasia123', 'recaptcha' => 'tok'])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        Http::assertSent(fn ($r) => $r['secret'] === 'secret' && $r['response'] === 'tok');
    }

    public function test_missing_token_is_refused(): void
    {
        $this->google(['success' => true, 'action' => 'login', 'score' => 0.9]);

        $this->post('/login', ['email' => 'ani@x.id', 'password' => 'rahasia123'])->assertSessionHasErrors('recaptcha');
        $this->assertGuest();
    }

    public function test_low_score_or_wrong_action_is_refused(): void
    {
        $this->google(['success' => true, 'action' => 'login', 'score' => 0.1]);
        $this->post('/login', ['email' => 'ani@x.id', 'password' => 'rahasia123', 'recaptcha' => 'tok'])->assertSessionHasErrors('recaptcha');

        $this->google(['success' => true, 'action' => 'other', 'score' => 0.9]);
        $this->post('/login', ['email' => 'ani@x.id', 'password' => 'rahasia123', 'recaptcha' => 'tok'])->assertSessionHasErrors('recaptcha');
        $this->assertGuest();
    }

    public function test_without_keys_login_works_as_before(): void
    {
        config(['services.recaptcha.site_key' => null, 'services.recaptcha.secret_key' => null]);
        Http::fake();

        $this->post('/login', ['email' => 'ani@x.id', 'password' => 'rahasia123'])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        Http::assertNothingSent();
    }
}
