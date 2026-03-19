<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        // Club 1 venues
        SeederIds::$venueHlavni = Str::uuid()->toString();
        SeederIds::$venueHala   = Str::uuid()->toString();
        SeederIds::$venueUmelka = Str::uuid()->toString();

        DB::table('venues')->insert([
            [
                'id'               => SeederIds::$venueHlavni,
                'club_id'          => SeederIds::$club,
                'name'             => 'Hlavní hřiště Letná',
                'address'          => 'Stadion Letná, Březnická 4068, Zlín',
                'latitude'         => 49.2208000,
                'longitude'        => 17.6697000,
                'geocoding_source' => null,
                'sport_type'       => 'football',
                'notes'            => null,
                'is_favorite'      => true,
                'sort_order'       => 1,
            ],
            [
                'id'               => SeederIds::$venueHala,
                'club_id'          => SeederIds::$club,
                'name'             => 'Sportovní hala ZŠ Zlín',
                'address'          => 'ZŠ Komenského, Zlín',
                'latitude'         => 49.2265000,
                'longitude'        => 17.6620000,
                'geocoding_source' => null,
                'sport_type'       => null,
                'notes'            => null,
                'is_favorite'      => true,
                'sort_order'       => 2,
            ],
            [
                'id'               => SeederIds::$venueUmelka,
                'club_id'          => SeederIds::$club,
                'name'             => 'Umělka Malenovice',
                'address'          => 'FK Malenovice, Zlín',
                'latitude'         => 49.2050000,
                'longitude'        => 17.6300000,
                'geocoding_source' => null,
                'sport_type'       => null,
                'notes'            => null,
                'is_favorite'      => false,
                'sort_order'       => 3,
            ],
        ]);

        // Club 2 venue
        SeederIds::$venueStadion2 = Str::uuid()->toString();

        DB::table('venues')->insert([
            [
                'id'               => SeederIds::$venueStadion2,
                'club_id'          => SeederIds::$club2,
                'name'             => 'Hala UP Olomouc',
                'address'          => 'Univerzitní sportovní hala, 17. listopadu 6, Olomouc',
                'latitude'         => 49.5955000,
                'longitude'        => 17.2518000,
                'geocoding_source' => null,
                'sport_type'       => 'basketball',
                'notes'            => null,
                'is_favorite'      => true,
                'sort_order'       => 1,
            ],
        ]);

        $this->command->info('VenueSeeder: 4 sportoviště vytvořena.');
    }
}
