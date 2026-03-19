<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $membership = fn ($userId, $clubId, $role) => [
            'id'        => Str::uuid()->toString(),
            'user_id'   => $userId,
            'club_id'   => $clubId,
            'role'      => $role,
            'status'    => 'active',
            'joined_at' => $now,
        ];

        // -----------------------------------------------------------------
        // Club 1: FK Zlín Mládež
        // -----------------------------------------------------------------
        SeederIds::$club = Str::uuid()->toString();

        DB::table('clubs')->insert([
            'id'            => SeederIds::$club,
            'name'          => 'FK Zlín Mládež',
            'slug'          => 'fk-zlin-mladez',
            'primary_sport' => 'football',
            'address'       => 'Stadion Letná, Zlín',
            'logo_url'      => null,
            'color'         => '#1B6B4A',
            'bank_account'  => 'CZ6508000000001234567890',
            'settings'      => json_encode([
                'assistant_can_create_training' => true,
                'default_locale' => 'cs',
                'event_in_progress_minutes' => 60,
            ]),
            'billing_plan'  => 'pro',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        DB::table('club_memberships')->insert([
            $membership(SeederIds::$michal, SeederIds::$club, 'owner'),
            $membership(SeederIds::$jan,    SeederIds::$club, 'admin'),
            $membership(SeederIds::$pavel,  SeederIds::$club, 'member'),
            $membership(SeederIds::$eva,    SeederIds::$club, 'member'),
            $membership(SeederIds::$martin, SeederIds::$club, 'member'),
            $membership(SeederIds::$adam,   SeederIds::$club, 'member'),
            // Children
            $membership(SeederIds::$tomas,  SeederIds::$club, 'member'),
            $membership(SeederIds::$sofie,  SeederIds::$club, 'member'),
            $membership(SeederIds::$matej,  SeederIds::$club, 'member'),
            $membership(SeederIds::$ema,    SeederIds::$club, 'member'),
            $membership(SeederIds::$jakub,  SeederIds::$club, 'member'),
            // New parents
            $membership(SeederIds::$hana,   SeederIds::$club, 'member'),
            $membership(SeederIds::$jiri,   SeederIds::$club, 'member'),
        ]);

        // Bulk athletes — Club 1 (U9 + U12 kids)
        $bulkClub1 = [];
        foreach (array_merge(SeederIds::$extraU9, SeederIds::$extraU12) as $userId) {
            $bulkClub1[] = $membership($userId, SeederIds::$club, 'member');
        }
        DB::table('club_memberships')->insert($bulkClub1);

        // -----------------------------------------------------------------
        // Club 2: BK Olomouc
        // -----------------------------------------------------------------
        SeederIds::$club2 = Str::uuid()->toString();

        DB::table('clubs')->insert([
            'id'            => SeederIds::$club2,
            'name'          => 'BK Olomouc',
            'slug'          => 'bk-olomouc',
            'primary_sport' => 'basketball',
            'address'       => 'Sportovní hala, Olomouc',
            'logo_url'      => null,
            'color'         => '#2563EB',
            'bank_account'  => 'CZ9901000000009876543210',
            'settings'      => json_encode([
                'assistant_can_create_training' => false,
                'default_locale' => 'cs',
                'event_in_progress_minutes' => 90,
            ]),
            'billing_plan'  => 'basic',
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        DB::table('club_memberships')->insert([
            $membership(SeederIds::$lucie,  SeederIds::$club2, 'owner'),
            $membership(SeederIds::$pavel,  SeederIds::$club2, 'member'),   // coach in both clubs
            $membership(SeederIds::$eva,    SeederIds::$club2, 'member'),   // parent in both clubs
            $membership(SeederIds::$tomas,  SeederIds::$club2, 'member'),   // child in both clubs
            $membership(SeederIds::$matej,  SeederIds::$club2, 'member'),   // child in both clubs
            $membership(SeederIds::$david,  SeederIds::$club2, 'member'),   // adult athlete
            $membership(SeederIds::$petra,  SeederIds::$club2, 'member'),   // placeholder child
            $membership(SeederIds::$jiri,   SeederIds::$club2, 'member'),   // parent (cross-club)
        ]);

        // Bulk athletes — Club 2 (Juniors kids)
        $bulkClub2 = [];
        foreach (SeederIds::$extraJuniors as $userId) {
            $bulkClub2[] = $membership($userId, SeederIds::$club2, 'member');
        }
        DB::table('club_memberships')->insert($bulkClub2);

        $this->command->info('ClubSeeder: 2 kluby a ' . (18 + count(SeederIds::$extraU9) + count(SeederIds::$extraU12) + count(SeederIds::$extraJuniors) + 3) . ' členství vytvořeno.');
    }
}
