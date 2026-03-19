<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomFieldSeeder extends Seeder
{
    public function run(): void
    {
        $clubId = SeederIds::$club;

        // Custom field definitions
        $heightId = Str::uuid()->toString();
        $weightId = Str::uuid()->toString();
        $shoeId = Str::uuid()->toString();
        $allergiesId = Str::uuid()->toString();

        $createdBy = SeederIds::$michal;

        DB::table('custom_field_definitions')->insert([
            [
                'id' => $heightId,
                'club_id' => $clubId,
                'entity_type' => 'member',
                'name' => 'height',
                'display_name' => 'Výška',
                'field_type' => 'number_int',
                'suffix' => 'cm',
                'options' => null,
                'default_value' => null,
                'placeholder' => '165',
                'help_text' => null,
                'validation_min' => 50,
                'validation_max' => 220,
                'is_required' => false,
                'is_active' => true,
                'show_in_roster' => true,
                'show_in_registration' => false,
                'visibility_read' => 'coaches',
                'visibility_write' => 'member',
                'sort_order' => 1,
                'created_by' => $createdBy,
            ],
            [
                'id' => $weightId,
                'club_id' => $clubId,
                'entity_type' => 'member',
                'name' => 'weight',
                'display_name' => 'Váha',
                'field_type' => 'number_decimal',
                'suffix' => 'kg',
                'options' => null,
                'default_value' => null,
                'placeholder' => '55.5',
                'help_text' => null,
                'validation_min' => 15,
                'validation_max' => 150,
                'is_required' => false,
                'is_active' => true,
                'show_in_roster' => true,
                'show_in_registration' => false,
                'visibility_read' => 'coaches',
                'visibility_write' => 'member',
                'sort_order' => 2,
                'created_by' => $createdBy,
            ],
            [
                'id' => $shoeId,
                'club_id' => $clubId,
                'entity_type' => 'member',
                'name' => 'shoe_size',
                'display_name' => 'Velikost bot',
                'field_type' => 'number_int',
                'suffix' => null,
                'options' => null,
                'default_value' => null,
                'placeholder' => '38',
                'help_text' => 'EU velikost',
                'validation_min' => 20,
                'validation_max' => 50,
                'is_required' => false,
                'is_active' => true,
                'show_in_roster' => false,
                'show_in_registration' => true,
                'visibility_read' => 'coaches',
                'visibility_write' => 'member',
                'sort_order' => 3,
                'created_by' => $createdBy,
            ],
            [
                'id' => $allergiesId,
                'club_id' => $clubId,
                'entity_type' => 'member',
                'name' => 'allergies',
                'display_name' => 'Alergie',
                'field_type' => 'text',
                'suffix' => null,
                'options' => null,
                'default_value' => null,
                'placeholder' => 'Žádné',
                'help_text' => 'Uveďte případné alergie důležité pro trenéra.',
                'validation_min' => null,
                'validation_max' => null,
                'is_required' => false,
                'is_active' => true,
                'show_in_roster' => false,
                'show_in_registration' => true,
                'visibility_read' => 'coaches',
                'visibility_write' => 'member',
                'sort_order' => 4,
                'created_by' => $createdBy,
            ],
        ]);

        // Custom field values for some members
        $updatedBy = SeederIds::$jan; // Coach fills in

        DB::table('custom_field_values')->insert([
            [
                'id' => Str::uuid()->toString(),
                'definition_id' => $heightId,
                'entity_id' => SeederIds::$tomas,
                'value' => '132',
                'updated_by' => $updatedBy,
            ],
            [
                'id' => Str::uuid()->toString(),
                'definition_id' => $weightId,
                'entity_id' => SeederIds::$tomas,
                'value' => '28.5',
                'updated_by' => $updatedBy,
            ],
            [
                'id' => Str::uuid()->toString(),
                'definition_id' => $shoeId,
                'entity_id' => SeederIds::$tomas,
                'value' => '33',
                'updated_by' => $updatedBy,
            ],
            [
                'id' => Str::uuid()->toString(),
                'definition_id' => $heightId,
                'entity_id' => SeederIds::$ema,
                'value' => '128',
                'updated_by' => $updatedBy,
            ],
            [
                'id' => Str::uuid()->toString(),
                'definition_id' => $weightId,
                'entity_id' => SeederIds::$ema,
                'value' => '25.0',
                'updated_by' => $updatedBy,
            ],
            [
                'id' => Str::uuid()->toString(),
                'definition_id' => $heightId,
                'entity_id' => SeederIds::$jakub,
                'value' => '155',
                'updated_by' => $updatedBy,
            ],
            [
                'id' => Str::uuid()->toString(),
                'definition_id' => $weightId,
                'entity_id' => SeederIds::$jakub,
                'value' => '42.3',
                'updated_by' => $updatedBy,
            ],
            [
                'id' => Str::uuid()->toString(),
                'definition_id' => $allergiesId,
                'entity_id' => SeederIds::$jakub,
                'value' => 'pyly, prach',
                'updated_by' => $updatedBy,
            ],
        ]);

        $this->command->info('CustomFieldSeeder: 4 definice vlastních polí a 8 hodnot vytvořeno.');
    }
}
