<?php

namespace App\Libraries;

use Illuminate\Http\JsonResponse;

class Response
{
    private static bool $success = false;

    private static array $data = [];

    private static int $httpStatus = 400;

    private static ?string $responseCode = 'Success';

    private static ?string $responseMessage = null;

    private static array $messages = [];

    public static function ok($data = [], $responseMessage = null, $messages = []): JsonResponse
    {
        self::$success = true;
        self::$data = $data;
        self::$httpStatus = 200;
        self::$responseMessage = $responseMessage;
        self::$messages = $messages;

        foreach ($messages as &$msg) {
            if (!isset($msg['message']) && isset($msg['code'])) {
                $msg['message'] = self::getMessage($msg['code']);
            }
        }

        return self::jsonResponse();
    }

    public static function error($responseCode = null, $responseMessage = null, $messages = [], $httpStatus = 400, $data = []): JsonResponse
    {
        self::$data = $data;
        self::$httpStatus = $httpStatus;

        if (!isset($responseMessage) && isset($responseCode)) {
            $responseMessage = self::getMessage($responseCode);
        }

        foreach ($messages as &$msg) {
            if (!isset($msg['message']) && isset($msg['code'])) {
                $msg['message'] = self::getMessage($msg['code']);
            }
        }

        self::$responseCode = $responseCode;
        self::$responseMessage = $responseMessage;
        self::$messages = $messages;

        return self::jsonResponse();
    }

    private static function getMessage(string $responseCode): ?string
    {
        $messagesMap = [
            'BOTH_SIMULATION_AND_RESET' => 'If you specify simulationId, you can not reset simulation.',
            'VALIDATION_FAILS' => 'Check you inputs.',
            'TEAMS_MATCH_ERROR' => 'Specified teams does not matches.',
            'INVALID_TEAM_ID' => 'Invalid teamId for assignWeeks.',
            'NOT_FOUND_SIMULATION' => 'Simulation Not Found.',
            'NOT_FOUND_NON_FINALIZED_SIMULATION' => 'Non-Finalized Simulation Not Found.',
            'NOT_FOUND_EDITABLE_EVENT' => 'Editable Event Not Found.',
        ];

        return ($messagesMap[$responseCode] ?? null);
    }

    private static function jsonResponse(): JsonResponse
    {
        return response()->json(
            [
                'info' => [
                    'success' => self::$success,
                    'responseCode' => self::$responseCode,
                    'responseMessage' => self::$responseMessage,
                    'messages' => self::$messages,
                ],
                'data' => self::$data,
            ],
            self::$httpStatus
        );
    }
}
