<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ConsentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $gdprId = Str::uuid()->toString();
        $photoId = Str::uuid()->toString();
        $medicalId = Str::uuid()->toString();

        // Consent types
        DB::table('consent_types')->insert([
            [
                'id' => $gdprId,
                'club_id' => SeederIds::$club,
                'name' => 'Souhlas se zpracováním osobních údajů (GDPR)',
                'description' => 'Souhlas se zpracováním osobních údajů dle GDPR pro účely správy členství v klubu.',
                'content' => '<h2>Souhlas se zpracováním osobních údajů</h2><p>V souladu s nařízením Evropského parlamentu a Rady (EU) 2016/679 (GDPR) uděluji tímto souhlas se zpracováním svých osobních údajů, a to v rozsahu:</p><ul><li>Jméno, příjmení, datum narození</li><li>E-mailová adresa a telefonní číslo</li><li>Adresa bydliště</li><li>Údaje o docházce a sportovní výkonnosti</li></ul><p>Osobní údaje budou zpracovávány za účelem:</p><ol><li>Správy členství v klubu</li><li>Organizace tréninků a zápasů</li><li>Komunikace s členy a rodiči</li><li>Vedení statistik a docházky</li></ol><p>Souhlas je udělen na dobu trvání členství v klubu a 3 roky po jeho ukončení. Máte právo souhlas kdykoli odvolat.</p>',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'id' => $photoId,
                'club_id' => SeederIds::$club,
                'name' => 'Souhlas s fotografováním',
                'description' => 'Souhlas s pořizováním a zveřejňováním fotografií a videí z akcí klubu.',
                'content' => '<h2>Souhlas s pořizováním a zveřejňováním fotografií</h2><p>Uděluji souhlas s pořizováním fotografií a videozáznamů mé osoby (resp. mého dítěte) během klubových akcí a s jejich zveřejňováním na:</p><ul><li>Webových stránkách klubu</li><li>Sociálních sítích klubu (Facebook, Instagram)</li><li>Tištěných materiálech klubu</li></ul><p>Tento souhlas je udělen na dobu neurčitou a je možné jej kdykoli odvolat.</p>',
                'is_required' => false,
                'sort_order' => 2,
            ],
            [
                'id' => $medicalId,
                'club_id' => SeederIds::$club,
                'name' => 'Potvrzení zdravotní způsobilosti',
                'description' => 'Potvrzení, že člen je zdravotně způsobilý k účasti na sportovních aktivitách.',
                'content' => '<h2>Potvrzení zdravotní způsobilosti</h2><p>Prohlašuji, že jsem (resp. mé dítě je) zdravotně způsobilý/á k účasti na sportovních aktivitách organizovaných klubem.</p><p><strong>Důležité:</strong> V případě jakýchkoli zdravotních omezení nebo změn zdravotního stavu je nutné neprodleně informovat trenéra.</p>',
                'is_required' => true,
                'sort_order' => 3,
            ],
        ]);

        // Grant consents for adults and children
        // Eva (parent) grants all consents for herself
        DB::table('consents')->insert([
            [
                'id' => Str::uuid()->toString(),
                'consent_type_id' => $gdprId,
                'user_id' => SeederIds::$eva,
                'child_id' => null,
                'granted' => true,
                'granted_by' => SeederIds::$eva,
                'granted_at' => $now->copy()->subDays(30),
                'revoked_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'consent_type_id' => $photoId,
                'user_id' => SeederIds::$eva,
                'child_id' => null,
                'granted' => true,
                'granted_by' => SeederIds::$eva,
                'granted_at' => $now->copy()->subDays(30),
                'revoked_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'consent_type_id' => $medicalId,
                'user_id' => SeederIds::$eva,
                'child_id' => null,
                'granted' => true,
                'granted_by' => SeederIds::$eva,
                'granted_at' => $now->copy()->subDays(30),
                'revoked_at' => null,
            ],
        ]);

        // Eva grants GDPR and medical for her child Tomáš, but NOT photo
        DB::table('consents')->insert([
            [
                'id' => Str::uuid()->toString(),
                'consent_type_id' => $gdprId,
                'user_id' => SeederIds::$eva,
                'child_id' => SeederIds::$tomas,
                'granted' => true,
                'granted_by' => SeederIds::$eva,
                'granted_at' => $now->copy()->subDays(28),
                'revoked_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'consent_type_id' => $medicalId,
                'user_id' => SeederIds::$eva,
                'child_id' => SeederIds::$tomas,
                'granted' => true,
                'granted_by' => SeederIds::$eva,
                'granted_at' => $now->copy()->subDays(28),
                'revoked_at' => null,
            ],
        ]);

        // Martin grants all for Ema and Jakub
        foreach ([SeederIds::$ema, SeederIds::$jakub] as $childId) {
            foreach ([$gdprId, $photoId, $medicalId] as $typeId) {
                DB::table('consents')->insert([
                    'id' => Str::uuid()->toString(),
                    'consent_type_id' => $typeId,
                    'user_id' => SeederIds::$martin,
                    'child_id' => $childId,
                    'granted' => true,
                    'granted_by' => SeederIds::$martin,
                    'granted_at' => $now->copy()->subDays(25),
                    'revoked_at' => null,
                ]);
            }
        }

        // Martin's own consents (GDPR granted, photo revoked)
        DB::table('consents')->insert([
            [
                'id' => Str::uuid()->toString(),
                'consent_type_id' => $gdprId,
                'user_id' => SeederIds::$martin,
                'child_id' => null,
                'granted' => true,
                'granted_by' => SeederIds::$martin,
                'granted_at' => $now->copy()->subDays(25),
                'revoked_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'consent_type_id' => $photoId,
                'user_id' => SeederIds::$martin,
                'child_id' => null,
                'granted' => false,
                'granted_by' => SeederIds::$martin,
                'granted_at' => $now->copy()->subDays(25),
                'revoked_at' => $now->copy()->subDays(10),
            ],
        ]);

        $this->command->info('ConsentSeeder: 3 typy souhlasů a 14 záznamů souhlasů vytvořeno.');
    }
}
