<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfilePasswordTest extends TestCase
{
    private User $user;

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
            $t->string('telegram_id')->nullable();
            $t->unsignedInteger('id_pegawai_kehadiran')->nullable();
            $t->timestamps();
        });
        (require base_path('database/migrations/2026_11_28_000002_add_password_changed_at_to_user_table.php'))->up();

        $this->user = User::forceCreate([
            'id_kampus' => 1, 'access_level' => 2, 'nama_lengkap' => 'Ani', 'email' => 'ani@example.com',
            'password' => Hash::make('lama-sekali'), 'status' => 'active',
        ]);
    }

    public function test_password_changes_with_the_current_password(): void
    {
        $this->actingAs($this->user)
            ->put('/profil/password', ['current_password' => 'lama-sekali', 'password' => 'baru-sekali', 'password_confirmation' => 'baru-sekali'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('baru-sekali', $this->user->fresh()->password));
    }

    public function test_wrong_or_missing_current_password_is_refused(): void
    {
        $this->actingAs($this->user)
            ->put('/profil/password', ['current_password' => 'salah', 'password' => 'baru-sekali', 'password_confirmation' => 'baru-sekali'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($this->user)
            ->put('/profil/password', ['password' => 'baru-sekali', 'password_confirmation' => 'baru-sekali'])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('lama-sekali', $this->user->fresh()->password));
    }

    public function test_recent_passkey_confirmation_replaces_the_current_password(): void
    {
        $this->actingAs($this->user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put('/profil/password', ['password' => 'baru-sekali', 'password_confirmation' => 'baru-sekali'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('baru-sekali', $this->user->fresh()->password));
    }
}
