<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // ---------------------------------------------------------------
        // Helper closures
        // ---------------------------------------------------------------
        $eventTemplate = fn ($overrides) => array_merge([
            'id'                   => Str::uuid()->toString(),
            'club_id'              => null,
            'team_id'              => null,
            'venue_id'             => null,
            'location'             => null,
            'created_by'           => null,
            'event_type'           => 'training',
            'title'                => '',
            'surface_type'         => null,
            'starts_at'            => null,
            'ends_at'              => null,
            'recurrence_rule_id'   => null,
            'rsvp_deadline'        => null,
            'nomination_deadline'  => null,
            'min_capacity'         => null,
            'max_capacity'         => null,
            'instructions'         => null,
            'notes'                => null,
            'status'               => 'scheduled',
            'cancel_reason'        => null,
            'rescheduled_to'       => null,
            'cancelled_by'         => null,
            'cancelled_at'         => null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $overrides);

        $attendanceRecord = fn ($eventId, $tmId, $status = 'pending', $respondedBy = null) => [
            'id'                 => Str::uuid()->toString(),
            'event_id'           => $eventId,
            'team_membership_id' => $tmId,
            'rsvp_status'        => $status,
            'rsvp_note'          => null,
            'responded_by'       => $respondedBy,
            'responded_at'       => $respondedBy ? $now : null,
            'actual_status'      => null,
            'checked_by'         => null,
            'checked_at'         => null,
        ];

        // Collect all team membership IDs per team
        $u9Athletes = array_merge(
            [SeederIds::$tmTomasU9, SeederIds::$tmEmaU9, SeederIds::$tmSofieU9],
            SeederIds::$tmExtraU9
        );
        $u9Coaches = [SeederIds::$tmJanU9, SeederIds::$tmPavelU9];
        $u9All = array_merge($u9Coaches, $u9Athletes);

        $u12Athletes = array_merge(
            [SeederIds::$tmJakubU12, SeederIds::$tmAdamU12, SeederIds::$tmMatejU12],
            SeederIds::$tmExtraU12
        );
        $u12Coaches = [SeederIds::$tmJanU12];
        $u12All = array_merge($u12Coaches, $u12Athletes);

        $juniorsAthletes = array_merge(
            [SeederIds::$tmTomasJuniors, SeederIds::$tmMatejJuniors, SeederIds::$tmDavidJuniors, SeederIds::$tmPetraJuniors],
            SeederIds::$tmExtraJuniors
        );
        $juniorsCoaches = [SeederIds::$tmPavelJuniors];
        $juniorsAll = array_merge($juniorsCoaches, $juniorsAthletes);

        // ---------------------------------------------------------------
        // Recurrence rules
        // ---------------------------------------------------------------
        $rrU9Utery   = Str::uuid()->toString();
        $rrU9Ctvrtek = Str::uuid()->toString();
        $rrU12Streda = Str::uuid()->toString();
        $rrJuniorsPondeli = Str::uuid()->toString();

        DB::table('recurrence_rules')->insert([
            [
                'id'                       => $rrU9Utery,
                'club_id'                  => SeederIds::$club,
                'team_id'                  => SeederIds::$teamU9,
                'name'                     => 'U9 Úterní trénink — hala',
                'event_type'               => 'training',
                'frequency'                => 'weekly',
                'interval'                 => 1,
                'day_of_week'              => 1,
                'week_parity'              => null,
                'nth_weekday'              => null,
                'time_start'               => '17:00:00',
                'time_end'                 => '18:30:00',
                'venue_id'                 => SeederIds::$venueHala,
                'surface_type'             => null,
                'instructions_template_id' => null,
                'equipment_template_id'    => null,
                'auto_create_days_ahead'   => 14,
                'auto_rsvp'                => true,
                'valid_from'               => '2025-09-01',
                'valid_until'              => null,
                'is_active'                => true,
                'created_by'               => SeederIds::$jan,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => $rrU9Ctvrtek,
                'club_id'                  => SeederIds::$club,
                'team_id'                  => SeederIds::$teamU9,
                'name'                     => 'U9 Čtvrteční trénink — venku',
                'event_type'               => 'training',
                'frequency'                => 'weekly',
                'interval'                 => 1,
                'day_of_week'              => 3,
                'week_parity'              => null,
                'nth_weekday'              => null,
                'time_start'               => '16:30:00',
                'time_end'                 => '18:00:00',
                'venue_id'                 => SeederIds::$venueHlavni,
                'surface_type'             => null,
                'instructions_template_id' => null,
                'equipment_template_id'    => null,
                'auto_create_days_ahead'   => 14,
                'auto_rsvp'                => true,
                'valid_from'               => '2025-09-01',
                'valid_until'              => null,
                'is_active'                => true,
                'created_by'               => SeederIds::$jan,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => $rrU12Streda,
                'club_id'                  => SeederIds::$club,
                'team_id'                  => SeederIds::$teamU12,
                'name'                     => 'U12 Středeční trénink — hala',
                'event_type'               => 'training',
                'frequency'                => 'weekly',
                'interval'                 => 1,
                'day_of_week'              => 2,
                'week_parity'              => null,
                'nth_weekday'              => null,
                'time_start'               => '16:00:00',
                'time_end'                 => '17:30:00',
                'venue_id'                 => SeederIds::$venueHala,
                'surface_type'             => null,
                'instructions_template_id' => null,
                'equipment_template_id'    => null,
                'auto_create_days_ahead'   => 14,
                'auto_rsvp'                => true,
                'valid_from'               => '2025-09-01',
                'valid_until'              => null,
                'is_active'                => true,
                'created_by'               => SeederIds::$jan,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
            [
                'id'                       => $rrJuniorsPondeli,
                'club_id'                  => SeederIds::$club2,
                'team_id'                  => SeederIds::$teamJuniors,
                'name'                     => 'Juniors Pondělní trénink',
                'event_type'               => 'training',
                'frequency'                => 'weekly',
                'interval'                 => 1,
                'day_of_week'              => 0,
                'week_parity'              => null,
                'nth_weekday'              => null,
                'time_start'               => '18:00:00',
                'time_end'                 => '19:30:00',
                'venue_id'                 => SeederIds::$venueStadion2,
                'surface_type'             => null,
                'instructions_template_id' => null,
                'equipment_template_id'    => null,
                'auto_create_days_ahead'   => 14,
                'auto_rsvp'                => true,
                'valid_from'               => '2025-10-01',
                'valid_until'              => null,
                'is_active'                => true,
                'created_by'               => SeederIds::$pavel,
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Helper: create event + attendance for all team members
        // ---------------------------------------------------------------
        $createEventWithAttendance = function (array $eventData, array $allTmIds, array $rsvpOverrides = []) use ($eventTemplate, $attendanceRecord) {
            $event = $eventTemplate($eventData);
            DB::table('events')->insert($event);

            $attendances = [];
            foreach ($allTmIds as $tmId) {
                $status = $rsvpOverrides[$tmId]['status'] ?? 'pending';
                $respondedBy = $rsvpOverrides[$tmId]['responded_by'] ?? null;
                $att = $attendanceRecord($event['id'], $tmId, $status, $respondedBy);
                if (isset($rsvpOverrides[$tmId]['rsvp_note'])) {
                    $att['rsvp_note'] = $rsvpOverrides[$tmId]['rsvp_note'];
                }
                $attendances[] = $att;
            }
            DB::table('attendances')->insert($attendances);

            return $event['id'];
        };

        // =================================================================
        // CLUB 1: FK Zlín Mládež — U9 events
        // =================================================================

        // --- Past training (already happened, completed) ---
        $pastU9 = $today->copy()->subDays(3);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU9,
                'venue_id' => SeederIds::$venueHala, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U9',
                'starts_at' => $pastU9->copy()->setTime(17, 0),
                'ends_at' => $pastU9->copy()->setTime(18, 30),
                'recurrence_rule_id' => $rrU9Utery,
                'status' => 'completed',
            ],
            $u9All,
            [
                SeederIds::$tmJanU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$jan],
                SeederIds::$tmPavelU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$pavel],
                SeederIds::$tmTomasU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmEmaU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$martin],
                SeederIds::$tmSofieU9 => ['status' => 'declined', 'responded_by' => SeederIds::$eva, 'rsvp_note' => 'Nemoc'],
            ]
        );

        // --- Tomorrow: U9 training ---
        $tomorrow = $today->copy()->addDay();
        $eventTrU9Tomorrow = $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU9,
                'venue_id' => SeederIds::$venueHlavni, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U9',
                'starts_at' => $tomorrow->copy()->setTime(17, 0),
                'ends_at' => $tomorrow->copy()->setTime(18, 30),
                'recurrence_rule_id' => $rrU9Ctvrtek,
            ],
            $u9All,
            [
                SeederIds::$tmJanU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$jan],
                SeederIds::$tmTomasU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmSofieU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmEmaU9 => ['status' => 'pending'],
            ]
        );

        // --- In 4 days: U9 training ---
        $inFourDays = $today->copy()->addDays(4);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU9,
                'venue_id' => SeederIds::$venueHala, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U9',
                'starts_at' => $inFourDays->copy()->setTime(17, 0),
                'ends_at' => $inFourDays->copy()->setTime(18, 30),
                'recurrence_rule_id' => $rrU9Utery,
            ],
            $u9All
        );

        // --- In 6 days: U9 match ---
        $inSixDays = $today->copy()->addDays(6);
        $eventMatchU9 = $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU9,
                'venue_id' => SeederIds::$venueHlavni, 'created_by' => SeederIds::$jan,
                'event_type' => 'match', 'title' => 'U9 vs SK Kroměříž',
                'starts_at' => $inSixDays->copy()->setTime(10, 0),
                'ends_at' => $inSixDays->copy()->setTime(12, 0),
                'nomination_deadline' => $inSixDays->copy()->subDays(2)->setTime(18, 0),
                'instructions' => 'Sraz 9:30 u šaten. Bílé dresy.',
            ],
            $u9All,
            [
                SeederIds::$tmTomasU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmEmaU9 => ['status' => 'declined', 'responded_by' => SeederIds::$martin, 'rsvp_note' => 'Rodinná oslava'],
                SeederIds::$tmSofieU9 => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
            ]
        );

        // --- In 8 days: U9 training ---
        $inEightDays = $today->copy()->addDays(8);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU9,
                'venue_id' => SeederIds::$venueHlavni, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U9',
                'starts_at' => $inEightDays->copy()->setTime(16, 30),
                'ends_at' => $inEightDays->copy()->setTime(18, 0),
                'recurrence_rule_id' => $rrU9Ctvrtek,
            ],
            $u9All
        );

        // --- In 11 days: U9 training ---
        $inElevenDays = $today->copy()->addDays(11);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU9,
                'venue_id' => SeederIds::$venueHala, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U9',
                'starts_at' => $inElevenDays->copy()->setTime(17, 0),
                'ends_at' => $inElevenDays->copy()->setTime(18, 30),
                'recurrence_rule_id' => $rrU9Utery,
            ],
            $u9All
        );

        // =================================================================
        // CLUB 1: FK Zlín Mládež — U12 events
        // =================================================================

        // --- Past training (completed) ---
        $pastU12 = $today->copy()->subDays(4);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU12,
                'venue_id' => SeederIds::$venueHala, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U12',
                'starts_at' => $pastU12->copy()->setTime(16, 0),
                'ends_at' => $pastU12->copy()->setTime(17, 30),
                'recurrence_rule_id' => $rrU12Streda,
                'status' => 'completed',
            ],
            $u12All,
            [
                SeederIds::$tmJanU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$jan],
                SeederIds::$tmJakubU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$martin],
                SeederIds::$tmAdamU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$adam],
                SeederIds::$tmMatejU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
            ]
        );

        // --- Day after tomorrow: U12 training ---
        $dayAfter = $today->copy()->addDays(2);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU12,
                'venue_id' => SeederIds::$venueHala, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U12',
                'starts_at' => $dayAfter->copy()->setTime(16, 0),
                'ends_at' => $dayAfter->copy()->setTime(17, 30),
                'recurrence_rule_id' => $rrU12Streda,
            ],
            $u12All,
            [
                SeederIds::$tmJanU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$jan],
                SeederIds::$tmJakubU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$martin],
                SeederIds::$tmAdamU12 => ['status' => 'pending'],
                SeederIds::$tmMatejU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
            ]
        );

        // --- In 5 days: U12 training ---
        $inFiveDays = $today->copy()->addDays(5);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU12,
                'venue_id' => SeederIds::$venueHala, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U12',
                'starts_at' => $inFiveDays->copy()->setTime(16, 0),
                'ends_at' => $inFiveDays->copy()->setTime(17, 30),
                'recurrence_rule_id' => $rrU12Streda,
            ],
            $u12All
        );

        // --- In 9 days: U12 match ---
        $inNineDays = $today->copy()->addDays(9);
        $eventMatchU12 = $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU12,
                'venue_id' => SeederIds::$venueUmelka, 'created_by' => SeederIds::$jan,
                'event_type' => 'match', 'title' => 'U12 vs FC Vsetín',
                'starts_at' => $inNineDays->copy()->setTime(14, 0),
                'ends_at' => $inNineDays->copy()->setTime(16, 0),
                'nomination_deadline' => $inNineDays->copy()->subDays(2)->setTime(20, 0),
                'location' => 'Hřiště Na Lapači, Vsetín',
                'instructions' => 'Odjezd autobusem ve 12:00 od haly. Červené dresy.',
            ],
            $u12All,
            [
                SeederIds::$tmJakubU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$martin],
                SeederIds::$tmMatejU12 => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
            ]
        );

        // --- In 12 days: U12 training ---
        $inTwelveDays = $today->copy()->addDays(12);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU12,
                'venue_id' => SeederIds::$venueHala, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U12',
                'starts_at' => $inTwelveDays->copy()->setTime(16, 0),
                'ends_at' => $inTwelveDays->copy()->setTime(17, 30),
                'recurrence_rule_id' => $rrU12Streda,
            ],
            $u12All
        );

        // =================================================================
        // CLUB 1: Cancelled event (U9)
        // =================================================================
        $inThirteenDays = $today->copy()->addDays(13);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club, 'team_id' => SeederIds::$teamU9,
                'venue_id' => SeederIds::$venueHlavni, 'created_by' => SeederIds::$jan,
                'event_type' => 'training', 'title' => 'Trénink U9 (zrušeno)',
                'starts_at' => $inThirteenDays->copy()->setTime(16, 30),
                'ends_at' => $inThirteenDays->copy()->setTime(18, 0),
                'status' => 'cancelled',
                'cancel_reason' => 'Nepříznivé počasí',
                'cancelled_by' => SeederIds::$jan,
                'cancelled_at' => $now,
            ],
            $u9All
        );

        // =================================================================
        // CLUB 2: BK Olomouc — Juniors events
        // =================================================================

        // --- Past training (completed) ---
        $pastJuniors = $today->copy()->subDays(5);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club2, 'team_id' => SeederIds::$teamJuniors,
                'venue_id' => SeederIds::$venueStadion2, 'created_by' => SeederIds::$pavel,
                'event_type' => 'training', 'title' => 'Trénink Juniors',
                'starts_at' => $pastJuniors->copy()->setTime(18, 0),
                'ends_at' => $pastJuniors->copy()->setTime(19, 30),
                'recurrence_rule_id' => $rrJuniorsPondeli,
                'status' => 'completed',
            ],
            $juniorsAll,
            [
                SeederIds::$tmPavelJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$pavel],
                SeederIds::$tmTomasJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmMatejJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmDavidJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$david],
                SeederIds::$tmPetraJuniors => ['status' => 'declined', 'responded_by' => null],
            ]
        );

        // --- In 3 days: Juniors training ---
        $inThreeDays = $today->copy()->addDays(3);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club2, 'team_id' => SeederIds::$teamJuniors,
                'venue_id' => SeederIds::$venueStadion2, 'created_by' => SeederIds::$pavel,
                'event_type' => 'training', 'title' => 'Trénink Juniors',
                'starts_at' => $inThreeDays->copy()->setTime(18, 0),
                'ends_at' => $inThreeDays->copy()->setTime(19, 30),
                'recurrence_rule_id' => $rrJuniorsPondeli,
            ],
            $juniorsAll,
            [
                SeederIds::$tmPavelJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$pavel],
                SeederIds::$tmTomasJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmDavidJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$david],
                SeederIds::$tmMatejJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
            ]
        );

        // --- In 7 days: Juniors match ---
        $inSevenDays = $today->copy()->addDays(7);
        $eventMatchJuniors = $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club2, 'team_id' => SeederIds::$teamJuniors,
                'venue_id' => SeederIds::$venueStadion2, 'created_by' => SeederIds::$pavel,
                'event_type' => 'match', 'title' => 'Juniors vs TJ Prostějov',
                'starts_at' => $inSevenDays->copy()->setTime(15, 0),
                'ends_at' => $inSevenDays->copy()->setTime(17, 0),
                'nomination_deadline' => $inSevenDays->copy()->subDays(2)->setTime(20, 0),
                'instructions' => 'Sraz 14:15. Modré dresy.',
            ],
            $juniorsAll,
            [
                SeederIds::$tmTomasJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmMatejJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$eva],
                SeederIds::$tmDavidJuniors => ['status' => 'confirmed', 'responded_by' => SeederIds::$david],
            ]
        );

        // --- In 10 days: Juniors training ---
        $inTenDays = $today->copy()->addDays(10);
        $createEventWithAttendance(
            [
                'club_id' => SeederIds::$club2, 'team_id' => SeederIds::$teamJuniors,
                'venue_id' => SeederIds::$venueStadion2, 'created_by' => SeederIds::$pavel,
                'event_type' => 'training', 'title' => 'Trénink Juniors',
                'starts_at' => $inTenDays->copy()->setTime(18, 0),
                'ends_at' => $inTenDays->copy()->setTime(19, 30),
                'recurrence_rule_id' => $rrJuniorsPondeli,
            ],
            $juniorsAll
        );

        // ---------------------------------------------------------------
        // Nominations — Zápas U9 + Zápas U12 + Zápas Juniors
        // ---------------------------------------------------------------
        $nominationRecord = fn ($eventId, $tmId, $sourceTeamId, $priority = 1) => [
            'id'                 => Str::uuid()->toString(),
            'event_id'           => $eventId,
            'team_membership_id' => $tmId,
            'source_team_id'     => $sourceTeamId,
            'status'             => 'nominated',
            'priority'           => $priority,
            'nominated_by'       => SeederIds::$jan,
            'responded_by'       => null,
            'responded_at'       => null,
        ];

        DB::table('nominations')->insert([
            // U9 match nominations
            $nominationRecord($eventMatchU9, SeederIds::$tmTomasU9, SeederIds::$teamU9, 1),
            $nominationRecord($eventMatchU9, SeederIds::$tmEmaU9, SeederIds::$teamU9, 1),
            $nominationRecord($eventMatchU9, SeederIds::$tmSofieU9, SeederIds::$teamU9, 1),
        ]);

        // U12 match nominations
        $u12NominationRecord = fn ($tmId, $priority = 1) => [
            'id'                 => Str::uuid()->toString(),
            'event_id'           => $eventMatchU12,
            'team_membership_id' => $tmId,
            'source_team_id'     => SeederIds::$teamU12,
            'status'             => 'nominated',
            'priority'           => $priority,
            'nominated_by'       => SeederIds::$jan,
            'responded_by'       => null,
            'responded_at'       => null,
        ];

        DB::table('nominations')->insert([
            $u12NominationRecord(SeederIds::$tmJakubU12, 1),
            $u12NominationRecord(SeederIds::$tmAdamU12, 1),
            $u12NominationRecord(SeederIds::$tmMatejU12, 1),
        ]);

        // Juniors match nominations (Pavel nominates)
        $juniorsNomRecord = fn ($tmId, $priority = 1) => [
            'id'                 => Str::uuid()->toString(),
            'event_id'           => $eventMatchJuniors,
            'team_membership_id' => $tmId,
            'source_team_id'     => SeederIds::$teamJuniors,
            'status'             => 'nominated',
            'priority'           => $priority,
            'nominated_by'       => SeederIds::$pavel,
            'responded_by'       => null,
            'responded_at'       => null,
        ];

        DB::table('nominations')->insert([
            $juniorsNomRecord(SeederIds::$tmTomasJuniors, 1),
            $juniorsNomRecord(SeederIds::$tmMatejJuniors, 1),
            $juniorsNomRecord(SeederIds::$tmDavidJuniors, 1),
        ]);

        // Count totals
        $totalEvents = 13; // 6 U9 + 4 U12 + 1 cancelled + 3 Juniors... let me just count
        $eventCount = DB::table('events')->count();
        $attendanceCount = DB::table('attendances')->count();
        $nominationCount = DB::table('nominations')->count();
        $rrCount = DB::table('recurrence_rules')->count();

        $this->command->info("EventSeeder: {$rrCount} pravidel opakování, {$eventCount} událostí, {$attendanceCount} docházek a {$nominationCount} nominací vytvořeno.");
    }
}
