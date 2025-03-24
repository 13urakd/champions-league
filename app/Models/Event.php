<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $simulation_id
 * @property int $week_no
 * @property int $team_1_id
 * @property int $team_2_id
 * @property int $is_neutral_venue
 * @property int|null $goal_team_1
 * @property int|null $goal_team_2
 * @property int|null $goal_original_team_1
 * @property int|null $goal_original_team_2
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team|null $team1
 * @property-read \App\Models\Team|null $team2
 * @method static Builder<static>|Event newModelQuery()
 * @method static Builder<static>|Event newQuery()
 * @method static Builder<static>|Event query()
 * @method static Builder<static>|Event testQuery1(int $teamId1, int $teamId2, int $simulationId = 1, ?bool $isNeutralVenue = false)
 * @method static Builder<static>|Event whereCreatedAt($value)
 * @method static Builder<static>|Event whereGoalOriginalTeam1($value)
 * @method static Builder<static>|Event whereGoalOriginalTeam2($value)
 * @method static Builder<static>|Event whereGoalTeam1($value)
 * @method static Builder<static>|Event whereGoalTeam2($value)
 * @method static Builder<static>|Event whereId($value)
 * @method static Builder<static>|Event whereIsNeutralVenue($value)
 * @method static Builder<static>|Event whereSimulationId($value)
 * @method static Builder<static>|Event whereTeam1Id($value)
 * @method static Builder<static>|Event whereTeam2Id($value)
 * @method static Builder<static>|Event whereUpdatedAt($value)
 * @method static Builder<static>|Event whereWeekNo($value)
 * @mixin \Eloquent
 */
class Event extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'goal_team_1',
        'goal_team_2',
        'is_neutral_venue',
    ];

    public function team1(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_1_id', 'id');
    }

    public function team2(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_2_id', 'id');
    }

    public function scopeTestQuery1(Builder $query, int $teamId1, int $teamId2, int $simulationId = 1, null|bool $isNeutralVenue = false): void
    {
        $query->where('simulation_id', '=', $simulationId)
            ->where('team_1_id', '=', $teamId1)
            ->where('team_2_id', '=', $teamId2);

        if (isset($isNeutralVenue)) {
            $query->where('is_neutral_venue', '=', intval($isNeutralVenue));
        }

        $query->orderBy('id');
    }

}
