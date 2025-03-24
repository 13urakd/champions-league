<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayRunAllWeeksRequest;
use App\Http\Requests\PlayRunNextWeekRequest;
use App\Http\Resources\FixtureResource;
use App\Libraries\Response;
use App\Libraries\SimulationOperations;
use App\Models\Simulation;
use Illuminate\Http\JsonResponse;

class PlayController extends Controller
{
    public function runNextWeek(PlayRunNextWeekRequest $request): JsonResponse
    {
        $simulationId = $request->input('simulationId');

        $simulation = Simulation::where('is_finalized', '=', 0)
            ->find($simulationId);

        if (!isset($simulation)) {
            return Response::error('NOT_FOUND_NON_FINALIZED_SIMULATION');
        }

        SimulationOperations::playNextWeek($simulationId);

        return Response::ok(
            (new FixtureResource(
                Simulation::baseDetails()->find($simulationId)
            ))->toArray($request)
        );
    }

    public function runAllWeeks(PlayRunAllWeeksRequest $request): JsonResponse
    {
        $simulationId = $request->input('simulationId');

        $simulation = Simulation::where('is_finalized', '=', 0)
            ->find($simulationId);

        if (!isset($simulation)) {
            return Response::error('NOT_FOUND_NON_FINALIZED_SIMULATION');
        }

        SimulationOperations::playNextWeek($simulationId, null);

        return Response::ok(
            (new FixtureResource(
                Simulation::baseDetails()->find($simulationId)
            ))->toArray($request)
        );
    }

}
