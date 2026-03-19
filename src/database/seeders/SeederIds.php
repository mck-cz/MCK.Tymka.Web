<?php

namespace Database\Seeders;

/**
 * Shared UUID registry for cross-seeder references.
 * All IDs are populated by the respective seeders.
 */
class SeederIds
{
    // Users
    public static string $michal;   // Club 1 owner
    public static string $jan;      // Coach (head_coach U9 + U12 in club 1)
    public static string $pavel;    // Coach (assistant_coach U9 in club 1, head_coach in club 2)
    public static string $eva;      // Parent of Tomáš — member of BOTH clubs
    public static string $martin;   // Parent of Ema + Jakub
    public static string $tomas;    // Child (Eva's) — athlete in both clubs
    public static string $sofie;    // Child (Eva's) — athlete in club 1 only
    public static string $matej;    // Child (Eva's) — athlete in both clubs
    public static string $ema;      // Child (Martin's) — athlete
    public static string $jakub;    // Child (Martin's) — athlete
    public static string $adam;     // Teenager, self-managing
    public static string $lucie;    // Club 2 owner
    public static string $petra;    // Placeholder child (no guardian linked)
    public static string $david;    // Athlete in club 2 (adult)
    public static string $hana;     // Parent of Filip + Tereza (club 1)
    public static string $jiri;     // Parent of Daniel (club 1) + Samuel (club 2, cross-club)

    // Bulk-generated athletes (populated by UserSeeder)
    public static array $extraU9 = [];       // 6 athlete user IDs
    public static array $extraU12 = [];      // 6 athlete user IDs
    public static array $extraJuniors = [];  // 5 athlete user IDs
    public static array $tmExtraU9 = [];     // team membership IDs
    public static array $tmExtraU12 = [];
    public static array $tmExtraJuniors = [];

    // Club 1
    public static string $club;

    // Club 2
    public static string $club2;

    // Seasons
    public static string $season;
    public static string $season2;

    // Teams — Club 1
    public static string $teamU9;
    public static string $teamU12;

    // Teams — Club 2
    public static string $teamJuniors;

    // Team memberships — Club 1
    public static string $tmJanU9;
    public static string $tmPavelU9;
    public static string $tmTomasU9;
    public static string $tmEmaU9;
    public static string $tmSofieU9;
    public static string $tmMatejU12;
    public static string $tmJanU12;
    public static string $tmJakubU12;
    public static string $tmAdamU12;

    // Team memberships — Club 2
    public static string $tmPavelJuniors;
    public static string $tmTomasJuniors;
    public static string $tmDavidJuniors;
    public static string $tmPetraJuniors;
    public static string $tmMatejJuniors;

    // Venues — Club 1
    public static string $venueHlavni;
    public static string $venueHala;
    public static string $venueUmelka;

    // Venues — Club 2
    public static string $venueStadion2;
}
