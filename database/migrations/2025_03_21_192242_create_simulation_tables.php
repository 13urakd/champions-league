<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->mediumInteger('strength_percentage_home');
            $table->mediumInteger('strength_percentage_away');
        });

        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_finalized');
            $table->timestamps();
        });

        Schema::create('simulation_teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_id');
            $table->unsignedBigInteger('team_id');

            $table->unique(['simulation_id', 'team_id']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_id');
            $table->mediumInteger('week_no');
            $table->unsignedBigInteger('team_1_id');
            $table->unsignedBigInteger('team_2_id');
            $table->boolean('is_neutral_venue');
            $table->mediumInteger('goal_team_1')->nullable();
            $table->mediumInteger('goal_team_2')->nullable();
            $table->mediumInteger('goal_original_team_1')->nullable();
            $table->mediumInteger('goal_original_team_2')->nullable();
            $table->timestamps();

            $table->index(['simulation_id', 'week_no']);
        });

        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_id')->index();
            $table->mediumInteger('order');
            $table->unsignedBigInteger('team_id');

            $table->mediumInteger('points');
            $table->mediumInteger('goal_difference');
            $table->mediumInteger('goal_for');
            $table->mediumInteger('goal_against');
            $table->mediumInteger('won');
            $table->mediumInteger('drawn');
            $table->mediumInteger('lost');
            $table->mediumInteger('played');

            $table->timestamps();

            $table->index(['simulation_id', 'order', 'points', 'goal_difference', 'goal_for'], 'standing_simulation_general_index');
        });

        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulation_id')->index();
            $table->unsignedBigInteger('team_id');
            $table->integer('championship_per_thousandth');
            $table->timestamps();

            $table->unique(['simulation_id', 'team_id']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('standings');
        Schema::dropIfExists('events');
        Schema::dropIfExists('simulation_teams');
        Schema::dropIfExists('simulations');
        Schema::dropIfExists('teams');
    }
};
