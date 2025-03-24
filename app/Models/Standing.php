<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $simulation_id
 * @property int $order
 * @property int $team_id
 * @property int $points
 * @property int $goal_difference
 * @property int $goal_for
 * @property int $goal_against
 * @property int $won
 * @property int $drawn
 * @property int $lost
 * @property int $played
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereDrawn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereGoalAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereGoalDifference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereGoalFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereLost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing wherePlayed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereSimulationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Standing whereWon($value)
 * @mixin \Eloquent
 */
class Standing extends Model
{
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}
