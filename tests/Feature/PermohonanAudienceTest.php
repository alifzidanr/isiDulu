<?php

namespace Tests\Feature;

use App\Models\Permohonan;
use App\Models\User;
use App\Notifications\NewPermohonanNotification;
use App\Support\PermohonanAudience;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\PasskeyFixtures;
use Tests\TestCase;

/** A permohonan belongs to users of its kampus, unit or sub unit: they see it and get its Telegram messages. */
class PermohonanAudienceTest extends TestCase
{
    use PasskeyFixtures;

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
        Schema::create('permohonan', function (Blueprint $t) {
            $t->increments('id_permohonan');
            $t->date('tanggal')->nullable();
            $t->string('nama_pemohon');
            $t->string('kontak_pemohon')->nullable();
            $t->integer('id_unit');
            $t->integer('id_sub_unit')->nullable();
            $t->string('inventaris')->nullable();
            $t->text('keluhan')->nullable();
            $t->integer('id_user')->nullable();
            $t->integer('status_permohonan')->default(0);
            $t->timestamps();
        });

        DB::table('kampus')->insert(['id_kampus' => 2, 'nama_kampus' => 'Surabaya']);
        DB::table('unit')->insert([
            ['id_unit' => 5, 'id_kampus' => 1, 'nama_unit' => 'Keuangan'],
            ['id_unit' => 6, 'id_kampus' => 2, 'nama_unit' => 'Surabaya TU'],
        ]);
        DB::table('sub_unit')->insert(['id_sub_unit' => 9, 'nama_sub_unit' => 'Payroll', 'id_unit' => 5]);

        DB::table('permohonan')->insert([
            ['id_permohonan' => 1, 'nama_pemohon' => 'A', 'id_unit' => 4, 'id_sub_unit' => null],  // kampus 1, IT Support
            ['id_permohonan' => 2, 'nama_pemohon' => 'B', 'id_unit' => 5, 'id_sub_unit' => 9],     // kampus 1, Keuangan › Payroll
            ['id_permohonan' => 3, 'nama_pemohon' => 'C', 'id_unit' => 6, 'id_sub_unit' => null],  // kampus 2
        ]);
    }

    private function user(string $email, array $placement): User
    {
        return User::forceCreate(array_merge([
            'id_kampus' => null, 'id_unit' => null, 'id_sub_unit' => null, 'access_level' => 2,
            'nama_lengkap' => $email, 'email' => $email, 'password' => 'x',
        ], $placement));
    }

    private function visibleIds(User $user): array
    {
        return PermohonanAudience::visibleTo(Permohonan::query(), $user)->orderBy('id_permohonan')->pluck('id_permohonan')->all();
    }

    public function test_users_see_their_kampus_unit_or_sub_unit(): void
    {
        $this->assertSame([1, 2], $this->visibleIds($this->user('kampus@x', ['id_kampus' => 1])));
        $this->assertSame([2], $this->visibleIds($this->user('unit@x', ['id_unit' => 5])));
        $this->assertSame([2], $this->visibleIds($this->user('sub@x', ['id_sub_unit' => 9])));
        $this->assertSame([3], $this->visibleIds($this->user('sby@x', ['id_kampus' => 2, 'id_unit' => 6])));
        $this->assertSame([1, 2, 3], $this->visibleIds($this->user('none@x', [])));
    }

    public function test_telegram_goes_to_the_group_and_every_assigned_user_once(): void
    {
        config(['services.telegram-bot-api.group_chat_id' => '-100']);
        $this->user('kampus@x', ['id_kampus' => 1, 'telegram_id' => '111']);
        $this->user('unit@x', ['id_kampus' => 1, 'id_unit' => 5, 'telegram_id' => '222']);
        $this->user('sub@x', ['id_unit' => 5, 'id_sub_unit' => 9, 'telegram_id' => '333']);
        $this->user('dup@x', ['id_unit' => 5, 'telegram_id' => '222']);   // same chat as unit@x
        $this->user('sby@x', ['id_kampus' => 2, 'telegram_id' => '999']); // other kampus
        $this->user('notg@x', ['id_kampus' => 1]);                        // no telegram id
        Notification::fake();

        PermohonanAudience::notify(Permohonan::find(2), new NewPermohonanNotification(Permohonan::find(2)));

        $sent = [];
        Notification::assertSentOnDemand(NewPermohonanNotification::class, function ($n, $channels, AnonymousNotifiable $to) use (&$sent) {
            $sent[] = $to->routes['telegram'];

            return true;
        });
        sort($sent);
        $this->assertSame(['-100', '111', '222', '333'], $sent);
    }

    public function test_kampus_users_cover_every_unit_and_sub_unit_of_their_kampus(): void
    {
        $kampus = $this->user('kampus@x', ['id_kampus' => 1]);
        $unit = $this->user('unit@x', ['id_unit' => 5]);
        $sub = $this->user('sub@x', ['id_sub_unit' => 9]);
        $other = $this->user('sby@x', ['id_kampus' => 2]);

        $ids = fn ($id) => PermohonanAudience::usersFor(Permohonan::find($id))->pluck('email')->sort()->values()->all();

        // IT Support (kampus 1): the kampus user only.
        $this->assertSame(['kampus@x'], $ids(1));
        // Keuangan › Payroll (kampus 1): kampus, unit and sub unit users.
        $this->assertSame(['kampus@x', 'sub@x', 'unit@x'], $ids(2));
        // Surabaya TU (kampus 2): only that kampus.
        $this->assertSame(['sby@x'], $ids(3));
    }
}
