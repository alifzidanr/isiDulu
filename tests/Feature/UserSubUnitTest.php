<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\PasskeyFixtures;
use Tests\TestCase;

/** Master User: a user's sub unit has to belong to their unit. */
class UserSubUnitTest extends TestCase
{
    use PasskeyFixtures;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('sub_unit', function (Blueprint $t) {
            $t->increments('id_sub_unit');
            $t->string('nama_sub_unit');
            $t->integer('id_unit');
            $t->timestamps();
        });
        (require base_path('database/migrations/2026_11_28_000001_add_id_sub_unit_to_user_table.php'))->up();

        DB::table('unit')->insert(['id_unit' => 5, 'id_kampus' => 1, 'nama_unit' => 'Keuangan']);
        DB::table('sub_unit')->insert([
            ['id_sub_unit' => 1, 'nama_sub_unit' => 'Helpdesk', 'id_unit' => 4],
            ['id_sub_unit' => 2, 'nama_sub_unit' => 'Payroll', 'id_unit' => 5],
        ]);

        $this->admin = User::forceCreate([
            'id_kampus' => 1, 'access_level' => 0, 'nama_lengkap' => 'Admin',
            'email' => 'admin@x.id', 'password' => bcrypt('x'),
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Budi', 'email' => 'budi@x.id', 'password' => 'rahasia1',
            'access_level' => 2, 'id_kampus' => 1, 'id_unit' => 4, 'id_sub_unit' => 1,
        ], $override);
    }

    public function test_user_gets_a_sub_unit_of_their_unit(): void
    {
        $this->actingAs($this->admin)->post(route('master.user.store'), $this->payload())->assertSessionHasNoErrors();

        $this->assertSame(1, (int) User::where('email', 'budi@x.id')->value('id_sub_unit'));
    }

    public function test_a_sub_unit_from_another_unit_is_refused(): void
    {
        $this->actingAs($this->admin)->post(route('master.user.store'), $this->payload(['id_sub_unit' => 2]))
            ->assertSessionHasErrors('id_sub_unit');

        $this->assertFalse(User::where('email', 'budi@x.id')->exists());
    }

    public function test_sub_unit_is_optional(): void
    {
        $this->actingAs($this->admin)->post(route('master.user.store'), $this->payload(['id_sub_unit' => null]))->assertSessionHasNoErrors();

        $this->assertNull(User::where('email', 'budi@x.id')->value('id_sub_unit'));
    }
}
