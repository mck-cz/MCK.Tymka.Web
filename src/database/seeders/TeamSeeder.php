<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Season
        SeederIds::$season = Str::uuid()->toString();

        DB::table('seasons')->insert([
            'id'         => SeederIds::$season,
            'club_id'    => SeederIds::$club,
            'name'       => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date'   => '2026-06-30',
        ]);

        // Teams
        SeederIds::$teamU9  = Str::uuid()->toString();
        SeederIds::$teamU12 = Str::uuid()->toString();

        DB::table('teams')->insert([
            [
                'id'           => SeederIds::$teamU9,
                'club_id'      => SeederIds::$club,
                'season_id'    => SeederIds::$season,
                'name'         => 'U9 Přípravka',
                'sport'        => 'football',
                'age_category' => 'U9',
                'color'        => null,
                'is_active'    => true,
                'is_archived'  => false,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => SeederIds::$teamU12,
                'club_id'      => SeederIds::$club,
                'season_id'    => SeederIds::$season,
                'name'         => 'U12 Žáci',
                'sport'        => 'football',
                'age_category' => 'U12',
                'color'        => null,
                'is_active'    => true,
                'is_archived'  => false,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        // Reusable team membership template
        $tmTemplate = fn ($id, $teamId, $userId, $role, $overrides = []) => array_merge([
            'id'        => $id,
            'team_id'   => $teamId,
            'user_id'   => $userId,
            'role'      => $role,
            'status'    => 'active',
            'position'  => null,
            'jersey_number' => null,
            'federation_id' => null,
            'federation_status' => null,
            'federation_registered_at' => null,
            'federation_membership_valid_until' => null,
            'federation_link_type' => null,
            'license_type' => null,
            'license_valid_until' => null,
            'attendance_required' => true,
            'joined_at' => $now,
        ], $overrides);

        // Team memberships — U9
        SeederIds::$tmJanU9   = Str::uuid()->toString();
        SeederIds::$tmPavelU9 = Str::uuid()->toString();
        SeederIds::$tmTomasU9 = Str::uuid()->toString();
        SeederIds::$tmEmaU9   = Str::uuid()->toString();
        SeederIds::$tmSofieU9 = Str::uuid()->toString();

        DB::table('team_memberships')->insert([
            $tmTemplate(SeederIds::$tmJanU9, SeederIds::$teamU9, SeederIds::$jan, 'head_coach'),
            $tmTemplate(SeederIds::$tmPavelU9, SeederIds::$teamU9, SeederIds::$pavel, 'assistant_coach'),
            $tmTemplate(SeederIds::$tmTomasU9, SeederIds::$teamU9, SeederIds::$tomas, 'athlete', [
                'position' => 'útočník', 'jersey_number' => 9,
                'federation_id' => 'FACR-2017-0412', 'federation_status' => 'youth',
                'federation_registered_at' => '2023-09-01', 'federation_membership_valid_until' => '2026-08-31',
                'federation_link_type' => 'facr',
            ]),
            $tmTemplate(SeederIds::$tmEmaU9, SeederIds::$teamU9, SeederIds::$ema, 'athlete', [
                'position' => 'záložník', 'jersey_number' => 7,
                'federation_id' => 'FACR-2018-0891', 'federation_status' => 'youth',
                'federation_registered_at' => '2024-01-15', 'federation_membership_valid_until' => '2026-08-31',
                'federation_link_type' => 'facr',
            ]),
            $tmTemplate(SeederIds::$tmSofieU9, SeederIds::$teamU9, SeederIds::$sofie, 'athlete', [
                'position' => 'obránce', 'jersey_number' => 3,
                'federation_id' => 'FACR-2019-0567', 'federation_status' => 'youth',
                'federation_registered_at' => '2025-01-10', 'federation_membership_valid_until' => '2026-08-31',
                'federation_link_type' => 'facr',
            ]),
        ]);

        // Bulk athletes — U9 (6 kids)
        $u9Positions = ['brankář', 'obránce', 'záložník', 'útočník', 'obránce', 'záložník'];
        $u9Jerseys   = [1, 2, 5, 10, 4, 6];
        $bulkU9 = [];
        foreach (SeederIds::$extraU9 as $i => $userId) {
            $tmId = Str::uuid()->toString();
            SeederIds::$tmExtraU9[] = $tmId;
            $bulkU9[] = $tmTemplate($tmId, SeederIds::$teamU9, $userId, 'athlete', [
                'position' => $u9Positions[$i], 'jersey_number' => $u9Jerseys[$i],
            ]);
        }
        DB::table('team_memberships')->insert($bulkU9);

        // Team memberships — U12
        SeederIds::$tmJanU12   = Str::uuid()->toString();
        SeederIds::$tmJakubU12 = Str::uuid()->toString();
        SeederIds::$tmAdamU12  = Str::uuid()->toString();
        SeederIds::$tmMatejU12 = Str::uuid()->toString();

        DB::table('team_memberships')->insert([
            $tmTemplate(SeederIds::$tmJanU12, SeederIds::$teamU12, SeederIds::$jan, 'head_coach'),
            $tmTemplate(SeederIds::$tmJakubU12, SeederIds::$teamU12, SeederIds::$jakub, 'athlete', [
                'position' => 'brankář', 'jersey_number' => 1,
                'federation_id' => 'FACR-2016-0223', 'federation_status' => 'youth',
                'federation_registered_at' => '2022-09-01', 'federation_membership_valid_until' => '2026-08-31',
                'federation_link_type' => 'facr', 'license_type' => 'B', 'license_valid_until' => '2026-06-30',
            ]),
            $tmTemplate(SeederIds::$tmAdamU12, SeederIds::$teamU12, SeederIds::$adam, 'athlete', [
                'position' => 'obránce', 'jersey_number' => 4,
                'federation_id' => 'FACR-2010-1042', 'federation_status' => 'youth',
                'federation_registered_at' => '2021-01-10', 'federation_membership_valid_until' => '2026-03-30',
                'federation_link_type' => 'facr',
            ]),
            $tmTemplate(SeederIds::$tmMatejU12, SeederIds::$teamU12, SeederIds::$matej, 'athlete', [
                'position' => 'záložník', 'jersey_number' => 6,
                'federation_id' => 'FACR-2015-0789', 'federation_status' => 'youth',
                'federation_registered_at' => '2023-09-01', 'federation_membership_valid_until' => '2026-08-31',
                'federation_link_type' => 'facr',
            ]),
        ]);

        // Bulk athletes — U12 (6 kids)
        $u12Positions = ['obránce', 'záložník', 'útočník', 'obránce', 'záložník', 'útočník'];
        $u12Jerseys   = [2, 5, 11, 3, 8, 10];
        $bulkU12 = [];
        foreach (SeederIds::$extraU12 as $i => $userId) {
            $tmId = Str::uuid()->toString();
            SeederIds::$tmExtraU12[] = $tmId;
            $bulkU12[] = $tmTemplate($tmId, SeederIds::$teamU12, $userId, 'athlete', [
                'position' => $u12Positions[$i], 'jersey_number' => $u12Jerseys[$i],
            ]);
        }
        DB::table('team_memberships')->insert($bulkU12);

        // =================================================================
        // Club 2: BK Olomouc — Season + Juniors team
        // =================================================================
        SeederIds::$season2 = Str::uuid()->toString();

        DB::table('seasons')->insert([
            'id'         => SeederIds::$season2,
            'club_id'    => SeederIds::$club2,
            'name'       => '2025/2026',
            'start_date' => '2025-10-01',
            'end_date'   => '2026-05-31',
        ]);

        SeederIds::$teamJuniors = Str::uuid()->toString();

        DB::table('teams')->insert([
            'id'           => SeederIds::$teamJuniors,
            'club_id'      => SeederIds::$club2,
            'season_id'    => SeederIds::$season2,
            'name'         => 'Juniors',
            'sport'        => 'basketball',
            'age_category' => 'U15',
            'color'        => null,
            'is_active'    => true,
            'is_archived'  => false,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        // Team memberships — Juniors
        SeederIds::$tmPavelJuniors = Str::uuid()->toString();
        SeederIds::$tmTomasJuniors = Str::uuid()->toString();
        SeederIds::$tmMatejJuniors = Str::uuid()->toString();
        SeederIds::$tmDavidJuniors = Str::uuid()->toString();
        SeederIds::$tmPetraJuniors = Str::uuid()->toString();

        DB::table('team_memberships')->insert([
            // Pavel = head_coach in Club 2 (assistant in Club 1)
            $tmTemplate(SeederIds::$tmPavelJuniors, SeederIds::$teamJuniors, SeederIds::$pavel, 'head_coach'),
            // Tomáš = athlete in BOTH clubs (cross-club child)
            $tmTemplate(SeederIds::$tmTomasJuniors, SeederIds::$teamJuniors, SeederIds::$tomas, 'athlete', [
                'position' => 'rozehrávač', 'jersey_number' => 5,
            ]),
            // Matěj = athlete in BOTH clubs (cross-club child)
            $tmTemplate(SeederIds::$tmMatejJuniors, SeederIds::$teamJuniors, SeederIds::$matej, 'athlete', [
                'position' => 'křídelní hráč', 'jersey_number' => 7,
            ]),
            // David = adult athlete
            $tmTemplate(SeederIds::$tmDavidJuniors, SeederIds::$teamJuniors, SeederIds::$david, 'athlete', [
                'position' => 'pivot', 'jersey_number' => 12,
            ]),
            // Petra = placeholder child (no guardian linked)
            $tmTemplate(SeederIds::$tmPetraJuniors, SeederIds::$teamJuniors, SeederIds::$petra, 'athlete', [
                'position' => 'křídlo', 'jersey_number' => 8,
            ]),
        ]);

        // Bulk athletes — Juniors (5 kids)
        $juniorsPositions = ['rozehrávač', 'křídlo', 'pivot', 'rozehrávač', 'křídlo'];
        $juniorsJerseys   = [3, 4, 11, 9, 6];
        $bulkJuniors = [];
        foreach (SeederIds::$extraJuniors as $i => $userId) {
            $tmId = Str::uuid()->toString();
            SeederIds::$tmExtraJuniors[] = $tmId;
            $bulkJuniors[] = $tmTemplate($tmId, SeederIds::$teamJuniors, $userId, 'athlete', [
                'position' => $juniorsPositions[$i], 'jersey_number' => $juniorsJerseys[$i],
            ]);
        }
        DB::table('team_memberships')->insert($bulkJuniors);

        $totalTm = 5 + count(SeederIds::$extraU9) + 4 + count(SeederIds::$extraU12) + 5 + count(SeederIds::$extraJuniors);
        $this->command->info("TeamSeeder: 2 sezóny, 3 týmy a {$totalTm} členství vytvořeno.");
    }
}
