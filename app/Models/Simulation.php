<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int $is_finalized
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Event> $events
 * @property-read int|null $events_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Prediction> $predictions
 * @property-read int|null $predictions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimulationTeam> $simulationTeams
 * @property-read int|null $simulation_teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Standing> $standings
 * @property-read int|null $standings_count
 * @method static Builder<static>|Simulation baseDetails()
 * @method static Builder<static>|Simulation newModelQuery()
 * @method static Builder<static>|Simulation newQuery()
 * @method static Builder<static>|Simulation query()
 * @method static Builder<static>|Simulation whereCreatedAt($value)
 * @method static Builder<static>|Simulation whereId($value)
 * @method static Builder<static>|Simulation whereIsFinalized($value)
 * @method static Builder<static>|Simulation whereName($value)
 * @method static Builder<static>|Simulation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Simulation extends Model
{
    public function simulationTeams(): HasMany
    {
        return $this->hasMany(SimulationTeam::class, 'simulation_id', 'id');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class, 'simulation_id', 'id');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'simulation_id', 'id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'simulation_id', 'id');
    }

    public function scopeBaseDetails(Builder $query): void
    {
        $query->select(['id', 'name', 'is_finalized'])
            ->with([
                'simulationTeams',
                'simulationTeams.team',
                'standings' => function ($q) {
                    $q->select(['id', 'simulation_id', 'order', 'team_id', 'points', 'goal_difference', 'goal_for', 'goal_against', 'won', 'drawn', 'lost', 'played'])
                        ->orderBy('order');
                },
                'predictions' => function ($q) {
                    $q->select(['id', 'simulation_id', 'championship_per_thousandth', 'team_id'])
                        ->orderByDesc('championship_per_thousandth');
                },
                'events' => function ($q) {
                    $q->select(['id', 'simulation_id', 'week_no', 'team_1_id', 'team_2_id', 'is_neutral_venue', 'goal_team_1', 'goal_team_2'])
                        ->orderBy('week_no');
                },
            ]);
    }
}
