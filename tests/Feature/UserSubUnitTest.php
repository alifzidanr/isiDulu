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

    public function test_tables_show_25_rows_by_default_and_the_baris_picker(): void
    {
        $this->actingAs($this->admin)->get(route('master.user.index'))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->perPage() === 25)
            ->assertSee('aria-label="Baris per halaman"', false);
    }

    public function test_per_page_comes_from_the_query_and_ignores_odd_values(): void
    {
        $this->actingAs($this->admin)->get(route('master.user.index', ['per_page' => 10]))
            ->assertViewHas('users', fn ($users) => $users->perPage() === 10);

        $this->actingAs($this->admin)->get(route('master.user.index', ['per_page' => 9999]))
            ->assertViewHas('users', fn ($users) => $users->perPage() === 25);
    }

    public function test_public_form_lists_units_and_their_sub_units(): void
    {
        $this->get(route('public.form'))
            ->assertOk()
            ->assertSee('Cari unit, sub unit atau kampus', false)
            ->assertSee('Helpdesk')
            ->assertSee('Payroll');
    }

    public function test_public_form_refuses_a_sub_unit_of_another_unit(): void
    {
        $this->post(route('public.store'), [
            'nama_pemohon' => 'Ani', 'kontak_pemohon' => '0812', 'id_unit' => 4, 'id_sub_unit' => 2,
            'inventaris' => 'n', 'keluhan' => 'Printer rusak',
        ])->assertSessionHasErrors('id_sub_unit');
    }

    public function test_user_list_can_be_searched_by_name_or_email(): void
    {
        User::forceCreate(['id_kampus' => 1, 'access_level' => 2, 'nama_lengkap' => 'Siti Aminah', 'email' => 'siti@x.id', 'password' => 'x']);
        User::forceCreate(['id_kampus' => 1, 'access_level' => 2, 'nama_lengkap' => 'Budi Santoso', 'email' => 'budi.s@x.id', 'password' => 'x']);

        $names = fn ($q) => $this->actingAs($this->admin)->get(route('master.user.index', ['q' => $q]))
            ->viewData('users')->pluck('nama_lengkap')->all();

        $this->assertSame(['Siti Aminah'], $names('aminah'));
        $this->assertSame(['Budi Santoso'], $names('BUDI.S'));
        $this->assertCount(3, $names(''));

        $this->actingAs($this->admin)->get(route('master.user.index', ['q' => 'zzz']))
            ->assertSee('Tidak ada user yang cocok');
    }
}
