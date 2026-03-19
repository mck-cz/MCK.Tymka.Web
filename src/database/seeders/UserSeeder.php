<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make('test');

        // Generate UUIDs
        SeederIds::$michal = Str::uuid()->toString();
        SeederIds::$jan    = Str::uuid()->toString();
        SeederIds::$pavel  = Str::uuid()->toString();
        SeederIds::$eva    = Str::uuid()->toString();
        SeederIds::$martin = Str::uuid()->toString();
        SeederIds::$tomas  = Str::uuid()->toString();
        SeederIds::$sofie  = Str::uuid()->toString();
        SeederIds::$matej  = Str::uuid()->toString();
        SeederIds::$ema    = Str::uuid()->toString();
        SeederIds::$jakub  = Str::uuid()->toString();
        SeederIds::$adam   = Str::uuid()->toString();
        SeederIds::$lucie  = Str::uuid()->toString();
        SeederIds::$petra  = Str::uuid()->toString();
        SeederIds::$david  = Str::uuid()->toString();

        $userTemplate = fn ($id, $first, $last, $email, $overrides = []) => array_merge([
            'id'                => $id,
            'first_name'        => $first,
            'last_name'         => $last,
            'nickname'          => null,
            'email'             => $email,
            'phone'             => null,
            'password'          => $password,
            'avatar_path'       => null,
            'address'           => null,
            'birth_date'        => null,
            'is_minor'          => false,
            'can_self_manage'   => false,
            'status'            => 'active',
            'locale'            => 'cs',
            'notification_preferences' => null,
            'email_verified_at' => $now,
            'remember_token'    => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ], $overrides);

        DB::table('users')->insert([
            // Club 1 owner (admin)
            $userTemplate(SeederIds::$michal, 'Michal', 'Kašpařík', 'admin@tymko.cz'),
            // Coach 1 (head_coach U9+U12, also club admin)
            $userTemplate(SeederIds::$jan, 'Jan', 'Novák', 'trener@tymko.test'),
            // Coach 2 (assistant in club 1, head_coach in club 2)
            $userTemplate(SeederIds::$pavel, 'Pavel', 'Dvořák', 'trener2@tymko.test'),
            // Parent 1 — member in BOTH clubs (cross-club scenario)
            $userTemplate(SeederIds::$eva, 'Eva', 'Svobodová', 'rodic@tymko.test'),
            // Parent 2
            $userTemplate(SeederIds::$martin, 'Martin', 'Procházka', 'rodic2@tymko.test'),
            // Child 1 (Eva's) — in both clubs
            $userTemplate(SeederIds::$tomas, 'Tomáš', 'Svoboda', null, [
                'password' => null, 'birth_date' => '2017-05-15',
                'is_minor' => true, 'email_verified_at' => null,
            ]),
            // Child 2 (Eva's) — only in club 1
            $userTemplate(SeederIds::$sofie, 'Sofie', 'Svobodová', null, [
                'password' => null, 'birth_date' => '2019-03-12',
                'is_minor' => true, 'email_verified_at' => null,
            ]),
            // Child 3 (Eva's) — in both clubs
            $userTemplate(SeederIds::$matej, 'Matěj', 'Svoboda', null, [
                'password' => null, 'birth_date' => '2015-07-28',
                'is_minor' => true, 'email_verified_at' => null,
            ]),
            // Child 4 (Martin's)
            $userTemplate(SeederIds::$ema, 'Ema', 'Procházková', null, [
                'password' => null, 'birth_date' => '2018-02-20',
                'is_minor' => true, 'email_verified_at' => null,
            ]),
            // Child 3 (Martin's)
            $userTemplate(SeederIds::$jakub, 'Jakub', 'Procházka', null, [
                'password' => null, 'birth_date' => '2016-08-10',
                'is_minor' => true, 'email_verified_at' => null,
            ]),
            // Teenager — self-managing athlete
            $userTemplate(SeederIds::$adam, 'Adam', 'Novotný', 'adam@tymko.test', [
                'birth_date' => '2010-11-03', 'is_minor' => true, 'can_self_manage' => true,
            ]),
            // Club 2 owner
            $userTemplate(SeederIds::$lucie, 'Lucie', 'Marková', 'lucie@tymko.test'),
            // Placeholder child (no guardian linked — unlinked scenario)
            $userTemplate(SeederIds::$petra, 'Petra', 'Nová', 'placeholder_petra@tymko.placeholder', [
                'password' => Hash::make(Str::random(64)),
                'status' => 'placeholder', 'birth_date' => '2017-09-22',
                'is_minor' => true, 'email_verified_at' => null,
            ]),
            // Adult athlete in club 2
            $userTemplate(SeederIds::$david, 'David', 'Kratochvíl', 'david@tymko.test'),
        ]);

        // -----------------------------------------------------------------
        // Additional parents
        // -----------------------------------------------------------------
        SeederIds::$hana = Str::uuid()->toString();
        SeederIds::$jiri = Str::uuid()->toString();

        DB::table('users')->insert([
            $userTemplate(SeederIds::$hana, 'Hana', 'Králová', 'hana@tymko.test'),
            $userTemplate(SeederIds::$jiri, 'Jiří', 'Marek', 'jiri@tymko.test'),
        ]);

        // -----------------------------------------------------------------
        // Bulk athletes — U9 (6 kids, birth 2017-2019)
        // -----------------------------------------------------------------
        $u9Kids = [
            ['Filip',   'Král',      '2017-08-22', SeederIds::$hana],   // Hana's
            ['Tereza',  'Králová',   '2018-11-05', SeederIds::$hana],   // Hana's
            ['Oliver',  'Šťastný',   '2017-02-14', null],               // placeholder
            ['Natálie', 'Veselá',    '2018-06-30', null],               // placeholder
            ['Vojtěch', 'Kučera',    '2017-12-01', null],               // placeholder
            ['Anna',    'Pokorná',   '2019-01-18', null],               // placeholder
        ];

        $childTemplate = fn ($first, $last, $birth) => $userTemplate(
            Str::uuid()->toString(), $first, $last, null,
            ['password' => null, 'birth_date' => $birth, 'is_minor' => true, 'email_verified_at' => null]
        );
        $placeholderTemplate = fn ($first, $last, $birth) => $userTemplate(
            Str::uuid()->toString(), $first, $last,
            'placeholder_' . strtolower($first) . '@tymko.placeholder',
            [
                'password' => Hash::make(Str::random(64)),
                'status' => 'placeholder',
                'birth_date' => $birth,
                'is_minor' => true,
                'email_verified_at' => null,
            ]
        );

        foreach ($u9Kids as $kid) {
            $row = $kid[3] ? $childTemplate($kid[0], $kid[1], $kid[2]) : $placeholderTemplate($kid[0], $kid[1], $kid[2]);
            DB::table('users')->insert($row);
            SeederIds::$extraU9[] = $row['id'];
        }

        // -----------------------------------------------------------------
        // Bulk athletes — U12 (6 kids, birth 2014-2016)
        // -----------------------------------------------------------------
        $u12Kids = [
            ['Daniel',  'Marek',     '2014-04-10', SeederIds::$jiri],   // Jiří's
            ['Šimon',   'Bartoš',    '2015-09-25', null],               // placeholder
            ['Eliška',  'Horáková',  '2014-07-08', null],               // placeholder
            ['Dominik', 'Fiala',     '2016-03-19', null],               // placeholder
            ['Nikola',  'Urbanová',  '2015-11-14', null],               // placeholder
            ['Richard', 'Blažek',    '2014-12-02', null],               // placeholder
        ];

        foreach ($u12Kids as $kid) {
            $row = $kid[3] ? $childTemplate($kid[0], $kid[1], $kid[2]) : $placeholderTemplate($kid[0], $kid[1], $kid[2]);
            DB::table('users')->insert($row);
            SeederIds::$extraU12[] = $row['id'];
        }

        // -----------------------------------------------------------------
        // Bulk athletes — Juniors (5 kids, birth 2011-2014)
        // -----------------------------------------------------------------
        $juniorsKids = [
            ['Samuel',   'Marek',     '2012-05-20', SeederIds::$jiri],  // Jiří's (cross-club)
            ['Kryštof',  'Novotný',   '2011-10-30', null],              // placeholder
            ['Štěpán',   'Veselý',    '2013-01-15', null],              // placeholder
            ['Barbora',  'Černá',     '2012-08-07', null],              // placeholder
            ['Ondřej',   'Kratochvíl','2011-06-22', null],              // placeholder
        ];

        foreach ($juniorsKids as $kid) {
            $row = $kid[3] ? $childTemplate($kid[0], $kid[1], $kid[2]) : $placeholderTemplate($kid[0], $kid[1], $kid[2]);
            DB::table('users')->insert($row);
            SeederIds::$extraJuniors[] = $row['id'];
        }

        // -----------------------------------------------------------------
        // Guardian relationships
        // -----------------------------------------------------------------
        $guardianRecord = fn ($guardianId, $childId, $rel) => [
            'id'          => Str::uuid()->toString(),
            'guardian_id' => $guardianId,
            'child_id'    => $childId,
            'relationship' => $rel,
            'is_primary'  => true,
            'created_at'  => $now,
        ];

        DB::table('user_guardians')->insert([
            // Eva → Tomáš, Sofie, Matěj
            $guardianRecord(SeederIds::$eva, SeederIds::$tomas, 'mother'),
            $guardianRecord(SeederIds::$eva, SeederIds::$sofie, 'mother'),
            $guardianRecord(SeederIds::$eva, SeederIds::$matej, 'mother'),
            // Martin → Ema, Jakub
            $guardianRecord(SeederIds::$martin, SeederIds::$ema, 'father'),
            $guardianRecord(SeederIds::$martin, SeederIds::$jakub, 'father'),
            // Hana → Filip, Tereza (first 2 in extraU9)
            $guardianRecord(SeederIds::$hana, SeederIds::$extraU9[0], 'mother'),
            $guardianRecord(SeederIds::$hana, SeederIds::$extraU9[1], 'mother'),
            // Jiří → Daniel (first in extraU12), Samuel (first in extraJuniors)
            $guardianRecord(SeederIds::$jiri, SeederIds::$extraU12[0], 'father'),
            $guardianRecord(SeederIds::$jiri, SeederIds::$extraJuniors[0], 'father'),
            // Petra, Oliver, Natálie, Vojtěch, Anna, Šimon, Eliška, Dominik,
            // Nikola, Richard, Kryštof, Štěpán, Barbora, Ondřej — NO guardian
        ]);

        $this->command->info('UserSeeder: 33 uživatelů a 9 vazeb rodič-dítě vytvořeno.');
    }
}
