<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $simulation_id
 * @property int $team_id
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SimulationTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SimulationTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SimulationTeam query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SimulationTeam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SimulationTeam whereSimulationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SimulationTeam whereTeamId($value)
 * @mixin \Eloquent
 */
class SimulationTeam extends Model
{
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}
