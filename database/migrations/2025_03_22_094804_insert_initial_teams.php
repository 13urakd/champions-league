<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = [
            ['strength_percentage_home' => 87, 'strength_percentage_away' => 83, 'name' => 'Liverpool'],
            ['strength_percentage_home' => 72, 'strength_percentage_away' => 69, 'name' => 'Newcastle United'],
            ['strength_percentage_home' => 68, 'strength_percentage_away' => 64, 'name' => 'Aston Villa'],
            ['strength_percentage_home' => 54, 'strength_percentage_away' => 49, 'name' => 'Leeds United'],
            ['strength_percentage_home' => 92, 'strength_percentage_away' => 88, 'name' => 'Manchester City'],
            ['strength_percentage_home' => 82, 'strength_percentage_away' => 77, 'name' => 'Arsenal'],
            ['strength_percentage_home' => 78, 'strength_percentage_away' => 74, 'name' => 'Chelsea'],
            ['strength_percentage_home' => 74, 'strength_percentage_away' => 70, 'name' => 'Manchester United'],
            ['strength_percentage_home' => 74, 'strength_percentage_away' => 70, 'name' => 'Tottenham Hotspur'],
            ['strength_percentage_home' => 68, 'strength_percentage_away' => 69, 'name' => 'Brighton & Hove'],
            ['strength_percentage_home' => 65, 'strength_percentage_away' => 60, 'name' => 'Brentford'],
            ['strength_percentage_home' => 62, 'strength_percentage_away' => 58, 'name' => 'Crystal Palace'],
            ['strength_percentage_home' => 60, 'strength_percentage_away' => 56, 'name' => 'West Ham United'],
            ['strength_percentage_home' => 54, 'strength_percentage_away' => 49, 'name' => 'Everton'],
            ['strength_percentage_home' => 51, 'strength_percentage_away' => 47, 'name' => 'Leicester City'],
            ['strength_percentage_home' => 46, 'strength_percentage_away' => 42, 'name' => 'Southampton'],
        ];

        Team::insert($teams);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Team::truncate();
    }
};
