<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FixtureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $simulation = $this->resource;

        $simulationTeamsKeyByTeamId = $simulation->simulationTeams->keyBy('team_id');
        $eventsGroupByWeekNo = $simulation->events->groupBy('week_no');

        $standings = [];
        $predictions = [];
        $weeks = [];

        foreach ($simulation->standings as $standing) {
            $standings[] = [
                'order' => $standing->order,
                'teamName' => $simulationTeamsKeyByTeamId[$standing->team_id]->team->name,
                'points' => $standing->points,
                'goalDifference' => $standing->goal_difference,
                'goalFor' => $standing->goal_for,
                'goalAgainst' => $standing->goal_against,
                'won' => $standing->won,
                'drawn' => $standing->drawn,
                'lost' => $standing->lost,
                'played' => $standing->played,
            ];
        }

        foreach ($simulation->predictions as $prediction) {
            $predictions[] = [
                'teamName' => $simulationTeamsKeyByTeamId[$prediction->team_id]->team->name,
                'championshipPercentage' => number_format($prediction->championship_per_thousandth / 10, 1, '.', ''),
            ];
        }

        foreach ($eventsGroupByWeekNo as $weekNo => $eventsOfWeek) {
            $events = [];
            foreach ($eventsOfWeek as $event) {
                $events[] = [
                    'id' => $event->id,
                    'teamName1' => $simulationTeamsKeyByTeamId[$event->team_1_id]->team->name,
                    'teamName2' => $simulationTeamsKeyByTeamId[$event->team_2_id]->team->name,
                    'isNeutralVenue' => boolval($event->isNeutralVenue),
                    'goalTeam1' => $event->goal_team_1,
                    'goalTeam2' => $event->goal_team_2,
                ];

            }
            $weeks[] = [
                'weekNo' => $weekNo,
                'events' => $events,
            ];
        }

        $weekIndexes = [
            'previousOnes' => [],
            'nextOtherOnes' => [],
            'nextOne' => null,
        ];
        foreach ($weeks as $i => $week) {
            if (isset($week['events'][0]['goalTeam1'])) {
                array_unshift($weekIndexes['previousOnes'], $i);
            } else if (!isset($weekIndexes['nextOne'])) {
                $weekIndexes['nextOne'] = $i;
            } else {
                $weekIndexes['nextOtherOnes'][] = $i;
            }
        }
//        $weekIndexes['nextOne'] = array_shift($weekIndexes['nextOtherOnes']);

        return [
            'id' => $simulation->id,
            'isFinalized' => boolval($simulation->is_finalized),
            'standings' => &$standings,
            'predictions' => &$predictions,
            'weeks' => &$weeks,
            'weekIndexes' => &$weekIndexes,
        ];
    }
}
