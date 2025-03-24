<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FixtureRequest;
use App\Http\Resources\FixtureResource;
use App\Libraries\Response;
use App\Libraries\SimulationOperations;
use App\Models\Simulation;
use Illuminate\Http\JsonResponse;

class FixtureController extends Controller
{
    public function __invoke(FixtureRequest $request): JsonResponse
    {
        $doReset = $request->input('doReset', false);
        $simulationId = $request->input('simulationId', null);

        if (isset($simulationId) && $doReset) {
            return Response::error('BOTH_SIMULATION_AND_RESET');
        }

        if ($doReset) {
            $simulationId = SimulationOperations::create();
        }


        $simulationQuery = Simulation::baseDetails();

        if (isset($simulationId)) {
            $simulation = $simulationQuery->find($simulationId);
            if (!isset($simulation)) {
                return Response::error('NOT_FOUND_SIMULATION');
            }
        } else {
            $simulation = $simulationQuery->orderByDesc('id')->first();
            if (!isset($simulation)) {
                $simulationId = SimulationOperations::create();
                $simulation = $simulationQuery->find($simulationId);
            }
        }

        return Response::ok(
            (new FixtureResource($simulation))->toArray($request)
        );
    }
}
