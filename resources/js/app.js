import 'bootstrap';

import angular from 'angular';

var clApp = angular.module('clApp', []).factory('clSrv', function () {
    return {};
});

clApp.controller('MainCtrl', ['$scope', '$location', '$http', '$q', 'clSrv', function ($scope, $location, $http, $q, clSrv) {

    // initials
    var protocol = $location.protocol();
    var host = $location.host();
    var port = $location.port();
    $scope.baseUrl = protocol + '://' + host;
    if (port) {
        $scope.baseUrl += ':' + port;
    }
    $scope.baseUrl += '/api/';

    $scope.hasPageLoader = true;

    $scope.errMsg = null;

    // base for api connections
    $scope.ajax = function (urlPath, method, inputs) {

        var postReq = {
            method: method,
            url: $scope.baseUrl + urlPath,
        };

        if (method === 'GET') {
            postReq.params = inputs;
        } else {
            postReq.data = inputs;
            postReq.headers = {
                "Content-Type": "application/json"
            }
        }

        var deferred = $q.defer();

        if ($scope.hasPageLoader) {
            $scope.errMsg = null;
        }

        $http(postReq).then(
            function (response) {
                deferred.resolve({
                    httpStatus: true,
                    content: response.data
                });

            }, function (response) {
                console.warn(response);

                if ($scope.hasPageLoader) {
                    $scope.errMsg = 'Http Error - ' + response.status;
                    if (response?.data?.info?.responseMessage !== undefined) {
                        $scope.errMsg = response.data.info.responseMessage;
                    }
                }

                deferred.resolve({
                    httpStatus: false,
                    content: response.data
                });

            }
        );

        return deferred.promise;
    }

    $scope.getFixture = function (simulationId = null, doReset = null) {
        var payload = {};
        if (simulationId !== null) {
            payload.simulationId = simulationId;
        }
        if (doReset !== null) {
            payload.doReset = doReset ? 1 : 0;
        }

        $scope.data = [];
        $scope.ajax('fixture', 'GET', payload).then(function (ajaxResponse) {
            if (ajaxResponse.httpStatus) {
                $scope.data = angular.copy(ajaxResponse.content.data);
            }
        });
    }

    $scope.patchPlayNextWeek = function (simulationId) {
        $scope.data = [];
        $scope.ajax('play/next-week', 'PATCH', {simulationId: simulationId}).then(function (ajaxResponse) {
            if (ajaxResponse.httpStatus) {
                $scope.data = angular.copy(ajaxResponse.content.data);
            }
        });
    }

    $scope.patchPlayAllWeeks = function (simulationId) {
        $scope.data = [];
        $scope.ajax('play/all-weeks', 'PATCH', {simulationId: simulationId}).then(function (ajaxResponse) {
            if (ajaxResponse.httpStatus) {
                $scope.data = angular.copy(ajaxResponse.content.data);
            }
        });
    }

    $scope.patchMatch = function (eventId, simulationId, goalTeam1, goalTeam2) {
        $scope.data = [];
        $scope.ajax('match/' + eventId, 'PATCH', {
            simulationId: simulationId,
            goalTeam1: goalTeam1,
            goalTeam2: goalTeam2
        }).then(function (ajaxResponse) {
            if (ajaxResponse.httpStatus) {
                $scope.data = angular.copy(ajaxResponse.content.data);
            }
        });
    }

    $scope.getFixture();

}
]);

