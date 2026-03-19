<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $clubId = SeederIds::$club;
        $adminId = SeederIds::$michal;
        $coachId = SeederIds::$jan;

        // ---------------------------------------------------------------
        // Consent Types
        // ---------------------------------------------------------------
        DB::table('consent_types')->insert([
            [
                'id'          => Str::uuid()->toString(),
                'club_id'     => $clubId,
                'name'        => 'Souhlas s focením a publikací',
                'description' => 'Souhlasím s pořizováním fotografií a videí mého dítěte během tréninků a zápasů a jejich publikací na webu a sociálních sítích klubu.',
                'is_required' => false,
                'sort_order'  => 1,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'club_id'     => $clubId,
                'name'        => 'GDPR – zpracování osobních údajů',
                'description' => 'Souhlasím se zpracováním osobních údajů v rozsahu: jméno, příjmení, datum narození, kontaktní údaje rodiče, zdravotní omezení. Údaje budou zpracovávány za účelem organizace sportovní činnosti.',
                'is_required' => true,
                'sort_order'  => 2,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'club_id'     => $clubId,
                'name'        => 'Zdravotní způsobilost',
                'description' => 'Potvrzuji, že mé dítě je zdravotně způsobilé k provozování sportovní činnosti a nemá žádná omezení, o kterých by měl být trenér informován.',
                'is_required' => true,
                'sort_order'  => 3,
            ],
        ]);

        // ---------------------------------------------------------------
        // Equipment Templates + Items
        // ---------------------------------------------------------------
        $eqTraining = Str::uuid()->toString();
        $eqMatch    = Str::uuid()->toString();

        DB::table('equipment_templates')->insert([
            [
                'id'         => $eqTraining,
                'club_id'    => $clubId,
                'event_type' => 'training',
                'name'       => 'Standardní trénink',
                'sort_order' => 1,
            ],
            [
                'id'         => $eqMatch,
                'club_id'    => $clubId,
                'event_type' => 'match',
                'name'       => 'Zápas – povinná výbava',
                'sort_order' => 1,
            ],
        ]);

        DB::table('equipment_template_items')->insert([
            // Training items
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqTraining,
                'label'       => 'Kopačky',
                'is_default'  => true,
                'sort_order'  => 1,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqTraining,
                'label'       => 'Chrániče holení',
                'is_default'  => true,
                'sort_order'  => 2,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqTraining,
                'label'       => 'Láhev s pitím',
                'is_default'  => true,
                'sort_order'  => 3,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqTraining,
                'label'       => 'Tréninkové tričko',
                'is_default'  => false,
                'sort_order'  => 4,
            ],
            // Match items
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqMatch,
                'label'       => 'Dres (kompletní sada)',
                'is_default'  => true,
                'sort_order'  => 1,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqMatch,
                'label'       => 'Kopačky',
                'is_default'  => true,
                'sort_order'  => 2,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqMatch,
                'label'       => 'Chrániče holení',
                'is_default'  => true,
                'sort_order'  => 3,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqMatch,
                'label'       => 'Láhev s pitím',
                'is_default'  => true,
                'sort_order'  => 4,
            ],
            [
                'id'          => Str::uuid()->toString(),
                'template_id' => $eqMatch,
                'label'       => 'Kartička hráče',
                'is_default'  => true,
                'sort_order'  => 5,
            ],
        ]);

        // ---------------------------------------------------------------
        // Instruction Templates
        // ---------------------------------------------------------------
        DB::table('instruction_templates')->insert([
            [
                'id'         => Str::uuid()->toString(),
                'club_id'    => $clubId,
                'event_type' => 'training',
                'name'       => 'Běžný trénink',
                'body'       => "Sraz 15 minut před začátkem tréninku.\nNezapomeňte si vzít dostatek pití.\nV případě nepřítomnosti se omluvte předem přes aplikaci.",
                'sort_order' => 1,
            ],
            [
                'id'         => Str::uuid()->toString(),
                'club_id'    => $clubId,
                'event_type' => 'match',
                'name'       => 'Domácí zápas',
                'body'       => "Sraz 45 minut před výkopem v šatně.\nMějte kompletní dresy a kartičky hráčů.\nRodiče prosíme o fandění z tribuny.",
                'sort_order' => 1,
            ],
            [
                'id'         => Str::uuid()->toString(),
                'club_id'    => $clubId,
                'event_type' => 'match',
                'name'       => 'Venkovní zápas',
                'body'       => "Odjezd autobusem z parkoviště u hřiště.\nSraz 60 minut před odjezdem.\nS sebou: dres, kopačky, chrániče, pití, svačina.\nNávrat odhadem 2 hodiny po konci zápasu.",
                'sort_order' => 2,
            ],
            [
                'id'         => Str::uuid()->toString(),
                'club_id'    => $clubId,
                'event_type' => 'tournament',
                'name'       => 'Celodenní turnaj',
                'body'       => "Turnaj trvá celý den, počítejte s obědem na místě.\nS sebou: 2 sady dresů, kopačky, chrániče, dostatek pití a jídla.\nPodrobný rozpis zápasů bude zveřejněn den předem.",
                'sort_order' => 1,
            ],
        ]);

        // ---------------------------------------------------------------
        // Penalty Rules
        // ---------------------------------------------------------------
        DB::table('penalty_rules')->insert([
            [
                'id'                => Str::uuid()->toString(),
                'club_id'           => $clubId,
                'team_id'           => null,
                'name'              => 'Neomluvenka z tréninku',
                'trigger_type'      => 'no_show',
                'penalty_type'      => 'fixed_amount',
                'amount'            => 200.00,
                'late_cancel_hours' => null,
                'grace_count'       => 1,
                'is_active'         => true,
                'created_by'        => $adminId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id'                => Str::uuid()->toString(),
                'club_id'           => $clubId,
                'team_id'           => null,
                'name'              => 'Pozdní omluva (méně než 12h)',
                'trigger_type'      => 'late_cancel',
                'penalty_type'      => 'fixed_amount',
                'amount'            => 100.00,
                'late_cancel_hours' => 12,
                'grace_count'       => 2,
                'is_active'         => true,
                'created_by'        => $adminId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'id'                => Str::uuid()->toString(),
                'club_id'           => $clubId,
                'team_id'           => SeederIds::$teamU9,
                'name'              => 'Absence na zápase bez omluvy',
                'trigger_type'      => 'no_show',
                'penalty_type'      => 'count_as_attended',
                'amount'            => null,
                'late_cancel_hours' => null,
                'grace_count'       => 0,
                'is_active'         => true,
                'created_by'        => $coachId,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Payment Requests + Member Payments
        // ---------------------------------------------------------------
        $paymentMembership = Str::uuid()->toString();
        $paymentCamp       = Str::uuid()->toString();

        DB::table('payment_requests')->insert([
            [
                'id'                     => $paymentMembership,
                'club_id'               => $clubId,
                'team_id'               => null,
                'created_by'            => $adminId,
                'name'                  => 'Členské příspěvky – jaro 2026',
                'description'           => 'Členské příspěvky za jarní část sezóny 2025/2026. Platba zahrnuje pronájem haly a venkovního hřiště.',
                'amount'                => 3500.00,
                'currency'              => 'CZK',
                'payment_type'          => 'membership_fee',
                'due_date'              => '2026-04-15',
                'variable_symbol_prefix' => '2026',
                'bank_account'          => '123456789/0100',
                'status'                => 'active',
                'created_at'            => $now,
            ],
            [
                'id'                     => $paymentCamp,
                'club_id'               => $clubId,
                'team_id'               => SeederIds::$teamU9,
                'created_by'            => $adminId,
                'name'                  => 'Letní kemp U9 – červenec 2026',
                'description'           => 'Týdenní fotbalový kemp v Luhačovicích, 6.–10. 7. 2026. Cena zahrnuje ubytování, stravování a tréninkový program.',
                'amount'                => 5000.00,
                'currency'              => 'CZK',
                'payment_type'          => 'event_fee',
                'due_date'              => '2026-06-15',
                'variable_symbol_prefix' => '2026',
                'bank_account'          => '123456789/0100',
                'status'                => 'active',
                'created_at'            => $now,
            ],
        ]);

        // Member payments for membership fee
        DB::table('member_payments')->insert([
            [
                'id'                 => Str::uuid()->toString(),
                'payment_request_id' => $paymentMembership,
                'user_id'            => SeederIds::$eva,
                'child_id'           => SeederIds::$tomas,
                'variable_symbol'    => '20260001',
                'amount'             => 3500.00,
                'status'             => 'paid',
                'paid_at'            => $now->copy()->subDays(3),
                'confirmed_by'       => $adminId,
                'thanked_at'         => $now->copy()->subDays(2),
                'qr_payload'         => null,
                'notes'              => null,
                'created_at'         => $now,
            ],
            [
                'id'                 => Str::uuid()->toString(),
                'payment_request_id' => $paymentMembership,
                'user_id'            => SeederIds::$martin,
                'child_id'           => SeederIds::$ema,
                'variable_symbol'    => '20260002',
                'amount'             => 3500.00,
                'status'             => 'pending',
                'paid_at'            => null,
                'confirmed_by'       => null,
                'thanked_at'         => null,
                'qr_payload'         => null,
                'notes'              => null,
                'created_at'         => $now,
            ],
            [
                'id'                 => Str::uuid()->toString(),
                'payment_request_id' => $paymentMembership,
                'user_id'            => SeederIds::$martin,
                'child_id'           => SeederIds::$jakub,
                'variable_symbol'    => '20260003',
                'amount'             => 3500.00,
                'status'             => 'pending',
                'paid_at'            => null,
                'confirmed_by'       => null,
                'thanked_at'         => null,
                'qr_payload'         => null,
                'notes'              => null,
                'created_at'         => $now,
            ],
        ]);

        // Member payments for camp
        DB::table('member_payments')->insert([
            [
                'id'                 => Str::uuid()->toString(),
                'payment_request_id' => $paymentCamp,
                'user_id'            => SeederIds::$eva,
                'child_id'           => SeederIds::$tomas,
                'variable_symbol'    => '20260101',
                'amount'             => 5000.00,
                'status'             => 'pending',
                'paid_at'            => null,
                'confirmed_by'       => null,
                'thanked_at'         => null,
                'qr_payload'         => null,
                'notes'              => null,
                'created_at'         => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Venue Costs
        // ---------------------------------------------------------------
        DB::table('venue_costs')->insert([
            [
                'id'                  => Str::uuid()->toString(),
                'club_id'             => $clubId,
                'team_id'             => null,
                'name'                => 'Pronájem sportovní haly',
                'cost_per_event'      => 1200.00,
                'currency'            => 'CZK',
                'split_method'        => 'per_attendance',
                'billing_period'      => 'monthly',
                'include_event_types' => json_encode(['training']),
                'bank_account'        => '987654321/0300',
                'is_active'           => true,
                'created_by'          => $adminId,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            [
                'id'                  => Str::uuid()->toString(),
                'club_id'             => $clubId,
                'team_id'             => SeederIds::$teamU9,
                'name'                => 'Umělá tráva – U9',
                'cost_per_event'      => 800.00,
                'currency'            => 'CZK',
                'split_method'        => 'equal_monthly',
                'billing_period'      => 'seasonal',
                'include_event_types' => json_encode(['training', 'match']),
                'bank_account'        => null,
                'is_active'           => true,
                'created_by'          => $adminId,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
        ]);

        // ---------------------------------------------------------------
        // Albums
        // ---------------------------------------------------------------
        DB::table('albums')->insert([
            [
                'id'         => Str::uuid()->toString(),
                'club_id'    => $clubId,
                'team_id'    => SeederIds::$teamU9,
                'event_id'   => null,
                'title'      => 'Tréninkové fotky – únor 2026',
                'created_by' => $coachId,
                'created_at' => $now,
            ],
            [
                'id'         => Str::uuid()->toString(),
                'club_id'    => $clubId,
                'team_id'    => null,
                'event_id'   => null,
                'title'      => 'Klubový den 2026',
                'created_by' => $adminId,
                'created_at' => $now,
            ],
        ]);

        $this->command->info('LookupSeeder: souhlasy, šablony, penalizace, platby, náklady a alba vytvořeny.');
    }
}
