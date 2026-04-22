<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Helpers\FrodlyHelper;

class FrodlyController extends Controller
{
    public function check(Request $request)
    {
        // sleep(10);
        $request->validate([
            'phone' => 'required|regex:/^\d{11}$/'
        ]);
        $phone = $request->input('phone');

        // Call courier methods
        $results = [
            'Redx'       => FrodlyHelper::getRedx($phone),
            'SteadFast'  => FrodlyHelper::getSteadFast($phone),
            'Pathao'     => FrodlyHelper::getPathao($phone),
            'Carrybee'   => FrodlyHelper::getCarrybee($phone),
            'Paperfly'   => FrodlyHelper::getPaperfly($phone),
        ];

        // Build summary
        $summaries = array_map(function($data) {
            return [
                'total'   => $data['total'],
                'success' => $data['success'],
                'cancel'  => $data['cancel'],
            ];
        }, $results);

        $total_all   = array_sum(array_column($results, 'total'));
        $success_all = array_sum(array_column($results, 'success'));
        $cancel_all  = array_sum(array_column($results, 'cancel'));

        $totalSummary = [
            'total'       => $total_all,
            'success'     => $success_all,
            'cancel'      => $cancel_all,
            'successRate' => $total_all ? round(($success_all / $total_all) * 100) : 0,
            'cancelRate'  => $total_all ? round(($cancel_all / $total_all) * 100) : 0,
        ];

        return response()->json([
            'status'  => 200,
            'message' => 'Courier info retrieved successfully.',
            'data'    => [
                'Summaries'   => $summaries,
                'totalSummary'=> $totalSummary,
            ]
        ]);
    }


    public function checkManualy(Request $request)
    {
        // Get token from header or query
        $token = $request->header('X-API-TOKEN') ?? $request->query('token');

        // Predefined token or phone
        $validToken = '1234567890abcdef';

        if (!$token || $token !== $validToken) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or missing token'
            ], 401);
        }

        // Validate phone
        $request->validate([
            'phone' => 'required|regex:/^\d{11}$/'
        ]);

        $phone = $request->input('phone');

        // Call courier methods
        $results = [
            'Redx'       => FrodlyHelper::getRedx($phone),
            'SteadFast'  => FrodlyHelper::getSteadFast($phone),
            'Pathao'     => FrodlyHelper::getPathao($phone),
            'Carrybee'   => FrodlyHelper::getCarrybee($phone),
            'Paperfly'   => FrodlyHelper::getPaperfly($phone),
        ];

        // Build summary
        $summaries = [];
        $total_all = $success_all = $cancel_all = 0;

        foreach ($results as $courier => $data) {
            $summaries[$courier] = [
                'logo'    => config("courier.logos." . strtolower($courier), ''),
                'total'   => $data['total'],
                'success' => $data['success'],
                'cancel'  => $data['cancel']
            ];

            $total_all   += $data['total'];
            $success_all += $data['success'];
            $cancel_all  += $data['cancel'];
        }

        return response()->json([
            'status' => true,
            'message' => 'Courier info retrieved successfully.',
            'data' => [
                'Summaries'   => $summaries,
                'totalSummary'=> [
                    'total'       => $total_all,
                    'success'     => $success_all,
                    'cancel'      => $cancel_all,
                    'successRate' => $total_all ? round(($success_all / $total_all) * 100) : 0,
                    'cancelRate'  => $total_all ? round(($cancel_all / $total_all) * 100) : 0
                ]
            ]
        ]);

    }
}

