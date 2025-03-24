<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventModifyRequest;
use App\Http\Resources\FixtureResource;
use App\Libraries\Response;
use App\Libraries\SimulationOperations;
use App\Models\Event;
use App\Models\Simulation;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function modify(EventModifyRequest $request, int $id): JsonResponse
    {
        $simulationId = $request->input('simulationId');

        $simulation = Simulation::where('is_finalized', '=', 0)
            ->find($simulationId);

        if (!isset($simulation)) {
            return Response::error('NOT_FOUND_NON_FINALIZED_SIMULATION');
        }

        $event = Event::where('simulation_id', '=', $simulationId)
            ->whereNotNull('goal_original_team_1')
            ->whereNotNull('goal_original_team_2')
            ->find($id);

        if (!isset($event)) {
            return Response::error('NOT_FOUND_EDITABLE_EVENT');
        }

        $event->goal_team_1 = $request->input('goalTeam1');
        $event->goal_team_2 = $request->input('goalTeam2');
        $event->save();

        //    TODO: test, delete these lines
        //    $simulationId = 1;
        //    $e = Event::testQuery1(1, 2, $simulationId, null)->first(); $e->update(['goal_team_1' => 1, 'goal_team_2' => 2]); // dump($e->toArray());
        //    $e = Event::testQuery1(1, 3, $simulationId, null)->first(); $e->update(['goal_team_1' => 1, 'goal_team_2' => 2]); // dump($e->toArray());
        //    $e = Event::testQuery1(1, 4, $simulationId, null)->first(); $e->update(['goal_team_1' => 1, 'goal_team_2' => 2]); // dump($e->toArray());
        //    $e = Event::testQuery1(2, 1, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 0]); // dump($e->toArray());
        //    $e = Event::testQuery1(3, 1, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 0]); // dump($e->toArray());
        //    $e = Event::testQuery1(4, 1, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 0]); // dump($e->toArray());
        //    $e = Event::testQuery1(2, 3, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 2]); // dump($e->toArray());
        //    $e = Event::testQuery1(2, 4, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 2]); // dump($e->toArray());
        //    $e = Event::testQuery1(3, 2, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 2]); // dump($e->toArray());
        //    $e = Event::testQuery1(4, 2, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 2, 'is_neutral_venue' => 0]); // dump($e->toArray());
        //    $e = Event::testQuery1(3, 4, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 2]); // dump($e->toArray());
        //    $e = Event::testQuery1(4, 3, $simulationId, null)->first(); $e->update(['goal_team_1' => 2, 'goal_team_2' => 2]); // dump($e->toArray());

        SimulationOperations::setStandings($simulationId);
        SimulationOperations::checkIfFinalized($simulationId);
        SimulationOperations::predictChampion($simulationId);

        return Response::ok(
            (new FixtureResource(
                Simulation::baseDetails()->find($simulationId)
            ))->toArray($request)
        );
    }

}
