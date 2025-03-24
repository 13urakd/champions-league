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
 * @property int $championship_per_thousandth
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction whereChampionshipPerThousandth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction whereSimulationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Prediction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Prediction extends Model
{
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
}
