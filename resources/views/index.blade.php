<!DOCTYPE html>
<html ng-app="clApp">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Champions League</title>

    @vite(['resources/css/app.css'])

</head>
<body>

@verbatim

    <div class="container my-4"
         ng-controller="MainCtrl"
    >
        <div role="alert" class="alert alert-danger"
             ng-show="errMsg!==null"
        >
            {{ errMsg }}
        </div>

        <div class="row my-3 mx-1 justify-content-center booorder booorder-primary">
            <!-- xs ; sm ; md ; lg ; xl ; xxl -->
            <div class="p-1 col col-12 col-md-7">
                <div class="booorder booorder-success">
                    <table class="table caption-top" >
                        <caption class="font-bold"> &nbsp; </caption>
                        <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Club</th>
                            <th scope="col">Pl</th>
                            <th scope="col">W</th>
                            <th scope="col">D</th>
                            <th scope="col">L</th>
                            <th scope="col">Gf</th>
                            <th scope="col">Ga</th>
                            <th scope="col">GD</th>
                            <th scope="col">Pts</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr ng-repeat="standing in data.standings">
                            <th class="text-end font-monospace"> {{ standing.order }} </th>
                            <td scope="row"> {{ standing.teamName }} </td>
                            <td class="text-end font-monospace"> {{ standing.played }} </td>
                            <td class="text-end font-monospace"> {{ standing.won }} </td>
                            <td class="text-end font-monospace"> {{ standing.drawn }} </td>
                            <td class="text-end font-monospace"> {{ standing.lost }} </td>
                            <td class="text-end font-monospace"> {{ standing.goalFor }} </td>
                            <td class="text-end font-monospace"> {{ standing.goalAgainst }} </td>
                            <td class="text-end font-monospace"> {{ standing.goalDifference }} </td>
                            <td class="text-end font-monospace"> {{ standing.points }} </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="p-1 p-1 col col-12 col-sm-8 col-md-5">
                <div class="booorder booorder-success">
                    <table class="table caption-top">
                        <caption ng-show="data.predictions.length > 0" class="ps-3 text-gray-500 font-bold"> Predictions of Championship </caption>
                        <caption ng-hide="data.predictions.length > 0" class="ps-3 text-gray-300 font-semibold"> Predictions of Championship </caption>
                        <thead class="table-light" ng-show="data.predictions.length > 0">
                        <tr>
                            <th scope="col">Club</th>
                            <th scope="col" class="text-end pe-4">%</th>
                        </tr>
                        </thead>
                        <tbody ng-show="data.predictions.length > 0">
                        <tr ng-repeat="prediction in data.predictions">
                            <td> {{ prediction.teamName }} </td>
                            <th scope="row" class="font-monospace text-end pe-3"> {{ prediction.championshipPercentage }} </th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row my-3 mx-1 justify-content-center booorder booorder-secondary">
            <div class="p-1 pe-sm-3 col col-12 col-sm-6 col-md-5">
                <div class="booorder booorder-info">
                    <table class="table caption-top mb-4">
                        <thead class="table-light" ng-show="data.weekIndexes.nextOne !== null">
                        <tr>
                            <th scope="col" class="text-nowrap" colspan="2">Next Week</th>
                            <th scope="col" colspan="2" class="text-end">
                                <button role="button" class="btn btn-sm btn-outline-warning" ng-disabled="data.isFinalized" ng-click="patchPlayNextWeek(data.id);">
                                    Play Next Week
                                </button>
                            </th>
                        </tr>
                        </thead>
                        <tbody ng-show="data.weekIndexes.nextOne !== null">
                        <tr ng-repeat="event in data.weeks[data.weekIndexes.nextOne].events">
                            <td class="small fst-italic text-secondary"> {{ data.weeks[weekIndex].weekNo }} </td>

                            <td class="text-nowrap"> {{ event.teamName1 }} </td>
                            <td> - </td>
                            <td class="text-nowrap"> {{ event.teamName2 }} </td>
                        </tr>
                        </tbody>
                    </table>
                    <table class="table caption-top">
                        <caption class="ps-3 text-gray-500 font-bold"> Next Weeks Matches </caption>
                        <thead class="table-light" ng-show="data.weekIndexes.nextOtherOnes.length > 0">
                        <tr>
                            <th scope="col" class="text-nowrap" colspan="2"># <small>week</small></th>
                            <th scope="col" colspan="2" class="text-end">
                                <button role="button" class="btn btn-sm btn-warning" ng-disabled="data.isFinalized" ng-click="patchPlayAllWeeks(data.id);">
                                    Play All Weeks
                                </button>
                            </th>
                        </tr>
                        </thead>
                        <tbody ng-show="data.weekIndexes.nextOtherOnes.length > 0" ng-repeat="weekIndex in data.weekIndexes.nextOtherOnes">
                        <tr ng-repeat="event in data.weeks[weekIndex].events">
                            <td class="small fst-italic text-secondary"> {{ data.weeks[weekIndex].weekNo }} </td>

                            <td class="text-nowrap"> {{ event.teamName1 }} </td>
                            <td> - </td>
                            <td class="text-nowrap"> {{ event.teamName2 }} </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="p-1 ps-sm-3 col col-12 col-sm-6 col-md-5">
                <div class="booorder booorder-info">
                    <table class="table caption-top">
                        <caption class="ps-3 text-gray-500 font-bold"> Past Weeks Match Results </caption>
                        <thead class="table-light" ng-show="data.weekIndexes.previousOnes.length > 0">
                        <tr>
                            <th scope="col" class="text-nowrap" colspan="2"># <small>week</small></th>
                            <th scope="col" colspan="5" class="text-end">
                                <button role="button" class="btn btn-sm btn-outline-danger" ng-click="getFixture(null, true);">
                                    Reset Simulation
                                </button>
                            </th>

                        </tr>
                        </thead>
                        <tbody ng-show="data.weekIndexes.previousOnes.length > 0" ng-repeat="weekIndex in data.weekIndexes.previousOnes">
                        <tr ng-repeat="event in data.weeks[weekIndex].events">
                            <td class="small fst-italic text-secondary"> {{ data.weeks[weekIndex].weekNo }} </td>

                            <td class="text-nowrap"> {{ event.teamName1 }} </td>
                            <td class="font-monospace">
                                <span ng-show="event.isEditing == undefined || !event.isEditing">
                                    {{ event.goalTeam1 }}
                                </span>
                                <input class="input-group-text input-group-sm" ng-show="event.isEditing != undefined && event.isEditing" ng-model="event.goalEdited1" style="width: 3rem;"/>
                            </td>
                            <td> - </td>
                            <td class="font-monospace">
                                <span ng-show="event.isEditing == undefined || !event.isEditing">
                                    {{ event.goalTeam2 }}
                                </span>
                                <input class="input-group-text input-group-sm" ng-show="event.isEditing != undefined && event.isEditing" ng-model="event.goalEdited2" style="width: 3rem;"/>
                            </td>
                            <td class="text-nowrap"> {{ event.teamName2 }} </td>
                            <td class="text-nowrap">
                                <button role="button" ng-click="event.isEditing = true; event.goalEdited1 = event.goalTeam1; event.goalEdited2 = event.goalTeam2; " ng-show="event.isEditing == undefined || !event.isEditing" class="btn btn-sm btn-light">Edit</button>
                                <button role="button" ng-click="event.isEditing = false;" ng-show="event.isEditing != undefined && event.isEditing" class="btn btn-sm btn-secondary">Cl.</button>
                                <button role="button" ng-click="patchMatch(event.id, data.id, event.goalEdited1, event.goalEdited2);" ng-show="event.isEditing != undefined && event.isEditing" class="btn btn-sm btn-success">Conf.</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endverbatim

@vite(['resources/js/app.js'])

</body>
</html>
