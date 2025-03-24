<?php

namespace App\Libraries;


use App\Models\Event;
use App\Models\Prediction;
use App\Models\Simulation;
use App\Models\SimulationTeam;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;

class SimulationOperations
{
    private static array $factorialValues = [1, 1, 2, 6, 24, 120, 720];

    //    public static function test($teamIds = [1, 2, 3, 4]): int
    //    {
    //        $teamIds[] = 11;
    //        $teamIds[] = 12;
    //        $teamIds[] = 13;
    //        $teamIds[] = 14;assignWeeks
    //        $fx = self::($teamIds);
    //
    //        dd($fx);
    //    }

    public static function create(array $teamIds = [1, 2, 3, 4]): int
    {
        $teamIds = array_values($teamIds);
        $teamsKeyById = Team::whereIn('id', $teamIds)
            ->get()
            ->keyBy('id');

        if (count($teamIds) !== $teamsKeyById->count()) {
            throw new HttpResponseException(
                Response::error('TEAMS_MATCH_ERROR')
            );
        }

        $newSimulation = new Simulation();
        $newSimulation->name = '_temp';
        $newSimulation->is_finalized = 0;
        $newSimulation->save();  // TODO: uncomment line
//        $newSimulation = Simulation::find(1);  // TODO: delete line

        $newSimulation->name = 'Simulation - ' . $newSimulation->id;
        $newSimulation->save();

        $simulationId = $newSimulation->id;

        $simulationTeams = array_map(function ($teamId) use ($simulationId) {
            return [
                'simulation_id' => $simulationId,
                'team_id' => $teamId,
            ];
        }, $teamIds);

        SimulationTeam::insert($simulationTeams);  // TODO: uncomment line

        $now = Carbon::now();
        $standings = array_map(function ($teamId) use ($simulationId, $now) {
            return [
                'simulation_id' => $simulationId,
                'order' => 1,
                'team_id' => $teamId,
                'points' => 0,
                'goal_difference' => 0,
                'goal_for' => 0,
                'goal_against' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'played' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $teamIds);
        Standing::insert($standings);  // TODO: uncomment line

        $eventsKeyByWeek = self::assignWeeks($teamIds);

        $teamCounts = count($teamIds);
        $newEvents = [];
        foreach ($eventsKeyByWeek as $weekNo => $weekEvents) {
            foreach ($weekEvents as $event) {
                $event['simulation_id'] = $simulationId;
                $event['is_neutral_venue'] = 0;
                $event['created_at'] = $now;
                $event['updated_at'] = $now;

                $event['week_no'] = $weekNo;
                $newEvents[] = $event;

                $event['week_no'] = $teamCounts - 1 + $weekNo;
                $tempTeamId = $event['team_1_id'];
                $event['team_1_id'] = $event['team_2_id'];
                $event['team_2_id'] = $tempTeamId;
                $newEvents[] = $event;
            }
        }
        Event::insert($newEvents);  // TODO: uncomment line

        return $newSimulation->id;
    }

    public static function setStandings(int $simulationId): void
    {
        $playedEvents = Event::select(['team_1_id', 'team_2_id', 'is_neutral_venue', 'goal_team_1', 'goal_team_2'])
            ->where('simulation_id', '=', $simulationId)
            ->whereNotNull('goal_team_1')
            ->whereNotNull('goal_team_2')
            ->get();

        $standingsKeyByTeamId = Standing::where('simulation_id', '=', $simulationId)
            ->get()
            ->keyBy('team_id');

        foreach ($standingsKeyByTeamId as $standing) {
            $standing->points = 0;
            $standing->goal_difference = 0;
            $standing->goal_for = 0;
            $standing->goal_against = 0;
            $standing->won = 0;
            $standing->drawn = 0;
            $standing->lost = 0;
            $standing->played = 0;
        }

        foreach ($playedEvents as $playedEvent) {
            $goalDifference1 = $playedEvent->goal_team_1 - $playedEvent->goal_team_2;
            $point1 = $goalDifference1 > 0 ? 3 : ($goalDifference1 === 0 ? 1 : 0);
            $goalDifference2 = -$goalDifference1;
            $point2 = $goalDifference2 > 0 ? 3 : ($goalDifference2 === 0 ? 1 : 0);

            $standingsKeyByTeamId[$playedEvent->team_1_id]->points += $point1;
            $standingsKeyByTeamId[$playedEvent->team_1_id]->goal_difference += $goalDifference1;
            $standingsKeyByTeamId[$playedEvent->team_1_id]->goal_for += $playedEvent->goal_team_1;
            $standingsKeyByTeamId[$playedEvent->team_1_id]->goal_against += $playedEvent->goal_team_2;
            $standingsKeyByTeamId[$playedEvent->team_1_id]->won += intval($goalDifference1 > 0);
            $standingsKeyByTeamId[$playedEvent->team_1_id]->drawn += intval($goalDifference1 === 0);
            $standingsKeyByTeamId[$playedEvent->team_1_id]->lost += intval($goalDifference1 < 0);
            $standingsKeyByTeamId[$playedEvent->team_1_id]->played++;

            $standingsKeyByTeamId[$playedEvent->team_2_id]->points += $point2;
            $standingsKeyByTeamId[$playedEvent->team_2_id]->goal_difference += $goalDifference2;
            $standingsKeyByTeamId[$playedEvent->team_2_id]->goal_for += $playedEvent->goal_team_2;
            $standingsKeyByTeamId[$playedEvent->team_2_id]->goal_against += $playedEvent->goal_team_1;
            $standingsKeyByTeamId[$playedEvent->team_2_id]->won += intval($goalDifference2 > 0);
            $standingsKeyByTeamId[$playedEvent->team_2_id]->drawn += intval($goalDifference2 === 0);
            $standingsKeyByTeamId[$playedEvent->team_2_id]->lost += intval($goalDifference2 < 0);
            $standingsKeyByTeamId[$playedEvent->team_2_id]->played++;
        }

        $standingsGrouped = [];
        foreach ($standingsKeyByTeamId as $standing) {
            $standingsGrouped[$standing->points][$standing->goal_difference][$standing->goal_for][] = $standing;
        }

        krsort($standingsGrouped);
        foreach ($standingsGrouped as $point => $standingsPointGrouped) {
            krsort($standingsPointGrouped);
            $standingsGrouped[$point] = $standingsPointGrouped;
            foreach ($standingsPointGrouped as $goalDiff => $standingsPointGoalDiffGrouped) {
                krsort($standingsPointGoalDiffGrouped);
                $standingsGrouped[$point][$goalDiff] = $standingsPointGoalDiffGrouped;
                foreach ($standingsPointGoalDiffGrouped as $goalFor => $standingsPointGoalDiffGoalForGrouped) {
                    $standingsGrouped[$point][$goalDiff][$goalFor] = $standingsPointGoalDiffGoalForGrouped;
                }
            }
        }

        $order = 1;
        $ordered = [];
        foreach ($standingsGrouped as $point => $standingsPointGrouped) {
            foreach ($standingsPointGrouped as $goalDiff => $standingsPointGoalDiffGrouped) {
                foreach ($standingsPointGoalDiffGrouped as $goalFor => $standingsPointGoalDiffGoalForGrouped) {
                    foreach ($standingsPointGoalDiffGoalForGrouped as $i => $standing) {
                        $standing->order = $order;
                        $ordered[$order][$standing->team_id] = $standing;
                    }
                    $order += count($standingsPointGoalDiffGoalForGrouped);
                }
            }
        }
        // dump($ordered);

        if (count($ordered[1]) > 1) {
            $orderedFirstTeamIds = [];
            $firstStandingsKeyByTeamId = [];
            foreach ($ordered[1] as $standing) {
                $orderedFirstTeamIds[] = $standing->team_id;
                $firstStandingsKeyByTeamId[$standing->team_id] = [
                    'points' => 0,
                    'awayGoal' => 0,
                ];
            }

            foreach ($playedEvents as $event) {
                if (in_array($event->team_1_id, $orderedFirstTeamIds) && in_array($event->team_2_id, $orderedFirstTeamIds)) {

                    $goalDifference1 = $event->goal_team_1 - $event->goal_team_2;
                    $point1 = $goalDifference1 > 0 ? 3 : ($goalDifference1 === 0 ? 1 : 0);
                    $goalDifference2 = -$goalDifference1;
                    $point2 = $goalDifference2 > 0 ? 3 : ($goalDifference2 === 0 ? 1 : 0);

                    $firstStandingsKeyByTeamId[$event->team_1_id]['points'] += $point1;
                    $firstStandingsKeyByTeamId[$event->team_1_id]['awayGoal'] += $event->is_neutral_venue ? $event->goal_team_2 : 0;
                    $firstStandingsKeyByTeamId[$event->team_2_id]['points'] += $point2;
                    $firstStandingsKeyByTeamId[$event->team_2_id]['awayGoal'] += $event->goal_team_2;
                }
            }

            $firstStandingsGroupedTeamIds = [];
            foreach ($firstStandingsKeyByTeamId as $firstStandingTeamId => $firstStanding) {
                $firstStandingsGroupedTeamIds[$firstStanding['points']][$firstStanding['awayGoal']][] = $firstStandingTeamId;
            }

            krsort($firstStandingsGroupedTeamIds);
            foreach ($firstStandingsGroupedTeamIds as $point => $firstStandingsPointGroupedTeamIds) {
                krsort($firstStandingsPointGroupedTeamIds);
                $firstStandingsGroupedTeamIds[$point] = $firstStandingsPointGroupedTeamIds;
                foreach ($firstStandingsPointGroupedTeamIds as $goalAway => $firstStandingsPointGoalGroupedTeamIds) {
                    $firstStandingsGroupedTeamIds[$point][$goalAway] = $firstStandingsPointGoalGroupedTeamIds;
                }
            }

            $order = 1;
            foreach ($firstStandingsGroupedTeamIds as $point => $firstStandingsPointGroupedTeamIds) {
                foreach ($firstStandingsPointGroupedTeamIds as $goalAway => $firstStandingsPointGoalGroupedTeamIds) {
                    foreach ($firstStandingsPointGoalGroupedTeamIds as $i => $teamId) {
                        $standing = $ordered[1][$teamId];
                        unset($ordered[1][$teamId]);
                        $standing->order = $order;
                        $ordered[$order][$teamId] = $standing;
                    }
                    $order += count($firstStandingsPointGoalGroupedTeamIds);
                }
            }

            ksort($ordered);
        }


        foreach ($ordered as $order => $standings) {
            foreach ($standings as $standing) {
                $standing->save();
            }
        }

        // dump($ordered);
    }

    public static function checkIfFinalized(int $simulationId): bool
    {
        $simulation = Simulation::select(['id', 'is_finalized'])
            ->find($simulationId);

        if ($simulation->is_finalized) {
            return true;
        }

        $unplayedMatchCount = Event::where('simulation_id', '=', $simulationId)
            ->whereNull('goal_original_team_1')
            ->count();

        if ($unplayedMatchCount > 0) {
            return false;
        }

        $firstStandings = Standing::where('simulation_id', '=', $simulationId)
            ->where('order', '=', 1)
            ->get();

        if ($firstStandings->count() === 1) {
            $simulation->is_finalized = 1;
            $simulation->save();

            return true;
        }

        $teams = $firstStandings->pluck('team_id')->toArray();

        $additionalWeeks = self::assignWeeks($teams);

        $playedWeekCount = Event::where('simulation_id', '=', $simulationId)->max('week_no');
        $now = Carbon::now();
        $additionalEvents = [];
        foreach ($additionalWeeks as $weekNo => $weeks) {
            $weekNo += $playedWeekCount;
            foreach ($weeks as $event) {
                $event['simulation_id'] = $simulationId;
                $event['week_no'] = $weekNo;
                $event['is_neutral_venue'] = 1;
                $event['created_at'] = $now;
                $event['updated_at'] = $now;
                $additionalEvents[] = $event;
            }
        }

        Event::insert($additionalEvents);

        return false;
    }

    public static function playNextWeek(int $simulationId, ?int $limit = 1): void
    {
        $weekMin = Event::where('simulation_id', '=', $simulationId)->whereNull('goal_team_1')->min('week_no');

        // obtains related unPlayed Events
        $eventsToPlayQuery = Event::with(['team1', 'team2'])
            ->where('simulation_id', '=', $simulationId)
            ->whereNull('goal_team_1')
            ->where('week_no', '>=', $weekMin);

        if (isset($limit)) {
            $weekMax = $weekMin + $limit - 1;
            $eventsToPlayQuery->where('week_no', '<=', $weekMax);
        }

        $eventsToPlay = $eventsToPlayQuery->get();

        foreach ($eventsToPlay as $eventToPlay) {

            // profit by teams assigned strengths and poisson distrubution to assign a match's score
            $team1 = [
                'homeStrength' => $eventToPlay->team1->strength_percentage_home,
                'awayStrength' => $eventToPlay->team1->strength_percentage_away,
            ];
            $team2 = [
                'homeStrength' => $eventToPlay->team2->strength_percentage_home,
                'awayStrength' => $eventToPlay->team2->strength_percentage_away,
            ];
            $poissons = self::predictScores($team1, $team2, $eventToPlay->is_neutral_venue);

            $poissonIntSum = end($poissons)['intSum'];

            $randomIS = rand(0, $poissonIntSum);
            foreach ($poissons as $poisson) {
                if ($randomIS <= $poisson['intSum']) {
                    break;
                }
            }

            $eventToPlay->goal_original_team_1 = $eventToPlay->goal_team_1 = $poisson['goal1'];
            $eventToPlay->goal_original_team_2 = $eventToPlay->goal_team_2 = $poisson['goal2'];
            $eventToPlay->save();
        }

        self::setStandings($simulationId);

        // If all events are scored and the champion is certain, finalize the simulation
        // Else, adds events to be played in neutral venues
        self::checkIfFinalized($simulationId);


        self::predictChampion($simulationId);

    }

    public static function predictScores(array $team1, array $team2, bool $isNeutralVenue, int $maxGoal = 5): array
    {
        $lambda1 = ($team1['homeStrength'] + ($isNeutralVenue ? $team1['awayStrength'] : $team1['homeStrength'])) / 60;
        $lambda2 = ($team2['awayStrength'] + ($isNeutralVenue ? $team2['homeStrength'] : $team2['awayStrength'])) / 60;

        $poissons = [];
        $poissonIntSum = 0;
        foreach (range(0, $maxGoal) as $goal1) {
            foreach (range(0, $maxGoal) as $goal2) {
                $poissonValue = round(
                    (exp(-$lambda1) * pow($lambda1, $goal1) / self::factorial($goal1)) * (exp(-$lambda2) * pow($lambda2, $goal2) / self::factorial($goal2)),
                    5
                );
                $poissonIntSum += intval($poissonValue * 100000);
                $poissons[] = [
                    'goal1' => $goal1,
                    'goal2' => $goal2,
                    'value' => $poissonValue,
                    'intSum' => $poissonIntSum
                ];
            }
        }

        return $poissons;

    }

    public static function predictChampion(int $simulationId)
    {
        $teamPoints = Standing::select(['team_id', 'points', 'order'])
            ->where('simulation_id', '=', $simulationId)
            ->get()
            ->keyBy('team_id')
            ->toArray();

        foreach ($teamPoints as &$teamPoint) {
            $teamPoint['posListKeyByPoint'][$teamPoint['points']] = 1;
        }

        $eventsToPlay = Event::with(['team1', 'team2'])
            ->where('simulation_id', '=', $simulationId)
            ->whereNull('goal_team_1')
            ->get();

        if (count($eventsToPlay) === 0) {

            $predictionsByTeamId = Prediction::where('simulation_id', '=', $simulationId)
                ->get()
                ->keyBy('team_id');

            if ($predictionsByTeamId->count() > 0) {
                foreach ($teamPoints as $standing) {
                    $predictionsByTeamId[$standing['team_id']]->championship_per_thousandth = $standing['order'] === 1 ? 1000 : 0;
                    $predictionsByTeamId[$standing['team_id']]->save();
                }
            } else {
                $now = Carbon::now();
                $newPredictions = [];
                foreach ($teamPoints as $standing) {
                    $newPredictions[] = [
                        'simulation_id' => $simulationId,
                        'team_id' => $standing['team_id'],
                        'championship_per_thousandth' => $standing['order'] === 1 ? 1000 : 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                Prediction::insert($newPredictions);
            }

            return;
        }

        // show each probabilities per each point via the key "posListKeyByPoint";
        foreach ($eventsToPlay as $eventToPlay) {
            $team1 = [
                'homeStrength' => $eventToPlay->team1->strength_percentage_home,
                'awayStrength' => $eventToPlay->team1->strength_percentage_away,
            ];
            $team2 = [
                'homeStrength' => $eventToPlay->team2->strength_percentage_home,
                'awayStrength' => $eventToPlay->team2->strength_percentage_away,
            ];
            $poissons = self::predictScores($team1, $team2, $eventToPlay->is_neutral_venue);

            $poissonIntSum = end($poissons)['intSum'];

            $possibilities = [
                't1' => 0,
                't2' => 0,
                'tX' => 0,
            ];

            foreach ($poissons as $poisson) {
                $tKey = $poisson['goal1'] > $poisson['goal2'] ? 't1' : ($poisson['goal1'] < $poisson['goal2'] ? 't2' : 'tX');
                $possibilities[$tKey] += ($poisson['value'] * 100000) / $poissonIntSum;
            }

            $newPosListKeyByPoint = [];
            foreach ($teamPoints[$eventToPlay->team_1_id]['posListKeyByPoint'] as $p0 => $pos) {
                $p3 = $p0 + 3;
                $p1 = $p0 + 1;
                $newPosListKeyByPoint[$p3] = $newPosListKeyByPoint[$p3] ?? 0;
                $newPosListKeyByPoint[$p1] = $newPosListKeyByPoint[$p1] ?? 0;
                $newPosListKeyByPoint[$p0] = $newPosListKeyByPoint[$p0] ?? 0;
                $newPosListKeyByPoint[$p3] += $pos * $possibilities['t1'];
                $newPosListKeyByPoint[$p1] += $pos * $possibilities['tX'];
                $newPosListKeyByPoint[$p0] += $pos * $possibilities['t2'];
            }
            $teamPoints[$eventToPlay->team_1_id]['posListKeyByPoint'] = $newPosListKeyByPoint;

            $newPosListKeyByPoint = [];
            foreach ($teamPoints[$eventToPlay->team_2_id]['posListKeyByPoint'] as $p0 => $pos) {
                $p3 = $p0 + 3;
                $p1 = $p0 + 1;
                $newPosListKeyByPoint[$p3] = $newPosListKeyByPoint[$p3] ?? 0;
                $newPosListKeyByPoint[$p1] = $newPosListKeyByPoint[$p1] ?? 0;
                $newPosListKeyByPoint[$p0] = $newPosListKeyByPoint[$p0] ?? 0;
                $newPosListKeyByPoint[$p3] += $pos * $possibilities['t2'];
                $newPosListKeyByPoint[$p1] += $pos * $possibilities['tX'];
                $newPosListKeyByPoint[$p0] += $pos * $possibilities['t1'];
            }
            $teamPoints[$eventToPlay->team_2_id]['posListKeyByPoint'] = $newPosListKeyByPoint;
        }
        // dd($teamPoints);

        /**
         * Multiple Cartesian Product of teams points to show each possibility
         */
        $crossStack = self::crossPoints($teamPoints);
        // dd($crossStack);

        $finalPredictByTeamId = array_fill_keys(array_keys($teamPoints), 0);

        foreach ($crossStack as $s) {
            $pointMax = 0;
            $championIds = [];
            foreach ($s['pointsByTeamId'] as $teamId => $point) {
                if($point > $pointMax) {
                    $pointMax = $point;
                    $championIds = [$teamId];
                } else if($point == $pointMax) {
                    $championIds[] = $teamId;
                }
            }

            $posPlus = $s['pos'] / count($championIds);
            foreach ($championIds as $teamId) {
                $finalPredictByTeamId[$teamId] += $posPlus;
            }
        }

        $predictionsByTeamId = Prediction::where('simulation_id', '=', $simulationId)
            ->get()
            ->keyBy('team_id');

//        dd($finalPredictByTeamId);

        if ($predictionsByTeamId->count() > 0) {
            foreach ($finalPredictByTeamId as $teamId => $probability) {
                $predictionsByTeamId[$teamId]->championship_per_thousandth = intval(round($probability * 1000));
                $predictionsByTeamId[$teamId]->save();
            }
        } else {
            $now = Carbon::now();
            $newPredictions = [];
            foreach ($finalPredictByTeamId as $teamId => $probability) {
                $newPredictions[] = [
                    'simulation_id' => $simulationId,
                    'team_id' => $teamId,
                    'championship_per_thousandth' => intval(round($probability * 1000)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            Prediction::insert($newPredictions);
        }

//        dd($finalPredictByTeamId, $predictionsByTeamId);
    }


    private static function factorial(int $n): int
    {
        if (isset(self::$factorialValues[$n])) {
            return self::$factorialValues[$n];
        }

        $product = end(self::$factorialValues);
        for ($i = key(self::$factorialValues) + 1; $i <= $n; $i++) {
            $product *= $i;
            self::$factorialValues[$i] = $product;
        }

        return $product;
    }

    private static function crossPoints(array $teamPoints, $stack = null): array
    {
        $stack = $stack ?? [[
            'pos' => 1,
            'pointsByTeamId' => [],
        ]];

        if (empty($teamPoints)) {
            return $stack;
        }

//        reset($teamPoints);
        $temp = array_shift($teamPoints);['posListKeyByPoint'];
        $teamId = $temp['team_id'];
//        key($teamPoints);
        $posList = $temp['posListKeyByPoint'];
//        dump($teamId, $posList);

        $stackNew = [];
        foreach ($stack as $s) {
            foreach ($posList as $point => $pos) {
                $s['pointsByTeamId'][$teamId] = $point;
                $stackNew[] = [
                    'pos' => $s['pos'] * $pos,
                    'pointsByTeamId' => $s['pointsByTeamId'], // array_merge($s['pointsByTeamId'], [$teamId => $point]),
                ];
            }
        }
//        dd($teamId, $posList, $stackNew);

        return self::crossPoints($teamPoints, $stackNew);
    }

    private static function assignWeeks(array $teamIds): array
    {
        if (empty($teamIds)) {
            return [];
        }


        foreach ($teamIds as $teamId) {
            if ((!is_integer($teamId) && !is_string($teamId)) || in_array($teamId, ['SKIP'], true)) {
                throw new HttpResponseException(
                    Response::error('INVALID_TEAM_ID')
                );
            }
        }

        if (count($teamIds) % 2 === 1) {
            $teamIds[] = 'SKIP';
        }

        shuffle($teamIds);

        $weeks = [];

        $teamC = array_shift($teamIds);
        $countOthers = count($teamIds);  // 9
        $weekNos = range(1, $countOthers);

        foreach ($weekNos as $weekNo) {

            $matchTeams = [$teamC, $teamIds[0]];
            shuffle($matchTeams);
            if (!in_array('SKIP', $matchTeams, true)) {
                $weeks[$weekNo][] = [
                    'team_1_id' => $matchTeams[0],
                    'team_2_id' => $matchTeams[1],
                ];
            }

            $i = 1;
            for ($j = $countOthers - $i; $i < $j; $j = $countOthers - $i) {

                $matchTeams = [$teamIds[$i], $teamIds[$j]];
                shuffle($matchTeams);
                if (!in_array('SKIP', $matchTeams, true)) {
                    $weeks[$weekNo][] = [
                        'team_1_id' => $matchTeams[0],
                        'team_2_id' => $matchTeams[1],
                    ];
                }

                $i++;
            }

            shuffle($weeks[$weekNo]);

            $ti = array_shift($teamIds);
            array_push($teamIds, $ti);
        }

        return $weeks;
    }

}
