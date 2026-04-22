<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class FrodlyHelper
{
    // ------------------- REDX -------------------
    public static function redxLogin(): ?string
    {
        $config = [
            'phone'      => '01972431245',
            'password'   => 'Frodly2025_$',
            'token_file' => public_path('frodly/redx_token.json'),
            'api_base'   => 'https://api.redx.com.bd/v4',
        ];

        @mkdir(dirname($config['token_file']), 0777, true);

        if (file_exists($config['token_file']) && time() - filemtime($config['token_file']) < 50 * 60) {
            return trim(file_get_contents($config['token_file']));
        }

        $ch = curl_init("{$config['api_base']}/auth/login");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode([
                'phone'    => '88' . $config['phone'],
                'password' => $config['password'],
            ]),
        ]);

        $response = curl_exec($ch);
        $res = json_decode($response, true);
        curl_close($ch);

        if (!isset($res['data']['accessToken'])) return null;

        file_put_contents($config['token_file'], $res['data']['accessToken']);
        return $res['data']['accessToken'];
    }

    public static function getRedx(string $phone): array
    {
        // $token = config('frodly.steadfast.token_data');
        $token = self::redxLogin();
        if (!$token) return ['success'=>0,'cancel'=>0,'total'=>0];

        $ch = curl_init("https://redx.com.bd/api/redx_se/admin/parcel/customer-success-return-rate?phoneNumber=88$phone");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", "Accept: application/json"]
        ]);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $success = $res['data']['deliveredParcels'] ?? 0;
        $total = $res['data']['totalParcels'] ?? 0;

        return [
            'success' => $success,
            'cancel'  => $total - $success,
            'total'   => $total
        ];
    }

    // ------------------- STEADFAST -------------------
    public static function steadFastLogin()
    {
        $config = [
            'email'       => 'dailyneedbd0@gmail.com', //'Blossomfieldbd@gmail.com', //'bornoshop24@gmail.com', //'frodlybd@gmail.com',
            'password'    => 'DnB2025$',//'Limon123@', //'Aq1w2e3r4t5',  // 'Frodly2025_$',
            'cookie_file' => public_path('frodly/steadfast_cookie.txt'),
            // 'base_url'    => 'https://steadfast.com.bd/login',
            'base_url'    => 'https://packzy.com/login',
        ];

        @mkdir(dirname($config['cookie_file']), 0777, true);

        $ch = curl_init($config['base_url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_COOKIEJAR      => $config['cookie_file'],
            CURLOPT_COOKIEFILE     => $config['cookie_file'],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $headerSize);

        if (!preg_match('/name="_token" value="([^"]+)"/', $body, $m) &&
            !preg_match('/<meta name="csrf-token" content="([^"]+)"/i', $body, $m)) {
            curl_close($ch);
            return false;
        }

        $token = $m[1];

        curl_setopt_array($ch, [
            CURLOPT_URL        => $config['base_url'],
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => http_build_query([
                '_token'   => $token,
                'email'    => $config['email'],
                'password' => $config['password']
            ]),
            CURLOPT_HEADER     => false
        ]);

        curl_exec($ch);
        return $ch;
    }

    public static function getSteadFast($phone)
    {
        $ch = self::steadFastLogin();
        // $ch = null;
        if (!$ch) return ['success'=>0,'cancel'=>0,'total'=>0];

        curl_setopt_array($ch, [
            CURLOPT_URL            => "https://packzy.com/user/frauds/check/$phone", // https://packzy.com/user/consignment/getbyphone/$phone
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => false,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        // if (!empty($data['error'])) {
        //     Log::info('API Response', $data);
        // }
        Log::info('API Response', $data);

        return [
            'success' => $data['total_delivered'] ?? 0,
            'cancel'  => $data['total_cancelled'] ?? 0,
            'total'   => ($data['total_delivered'] ?? 0) + ($data['total_cancelled'] ?? 0)
        ];
    }

    // ------------------- PATHAO -------------------
    public static function pathaoLogin()
    {
        $config = [
            'email'         => 'activerana1@gmail.com',
            'password'      => 'Rana@123@',
            'client_id'     => 'JxbojDzagw',
            'client_secret' => 'zFd506q6ihrAiL2ibnlyAUuqEyNRZ4nIY69UslwB',
            'token_cache'   => public_path('frodly/pathao_token.json'),
            'token_url'     => 'https://api-hermes.pathao.com/aladdin/api/v1/issue-token',
        ];

        @mkdir(dirname($config['token_cache']), 0777, true);

        if (file_exists($config['token_cache'])) {
            $cache = json_decode(file_get_contents($config['token_cache']), true);
            if (!empty($cache['access_token']) && !empty($cache['expires_at']) && $cache['expires_at'] > time()) {
                return $cache['access_token'];
            }
        }

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(15)
            ->post($config['token_url'], [
                'client_id'     => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'grant_type'    => 'password',
                'username'      => $config['email'],
                'password'      => $config['password'],
            ]);

        if (!$response->successful()) {
            Log::error('Pathao token request failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return null;
        }

        $res = $response->json();
        if (empty($res['access_token'])) {
            return null;
        }

        file_put_contents($config['token_cache'], json_encode([
            'access_token' => $res['access_token'],
            'expires_at'   => time() + ($res['expires_in'] ?? 3600),
        ]));

        return $res['access_token'];
    }

    public static function getPathao(string $phoneNumber): array
    {
        $timeout  = 15;
        $maxTries = 2;
        $baseUrl  = 'https://merchant.pathao.com/api/v1';

        if (!preg_match('/^01[3-9]\d{8}$/', $phoneNumber)) {
            return ['status'=>'error','success'=>0,'cancel'=>0,'total'=>0,'message'=>'Invalid phone number format'];
        }

        for ($i=0;$i<$maxTries;$i++) {
            $token = self::pathaoLogin();
            // $token = config('frodly.pathao.token_data');
            // $token = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIzNjgxMDYiLCJhdWQiOlsiMyJdLCJleHAiOjE3Njg3MzU4MDYsIm5iZiI6MTc2MDk1OTgwNiwiaWF0IjoxNzYwOTU5ODA2LCJqdGkiOiIyNDg0ZDI1YWYwMGQ1YzdjZjUxODYyMDFlZTU3NGRmMjBkOGIxYjAwY2E2ODhhODgyYjk4YzI4ZjFhMmM3YWM4IiwibWVyY2hhbnRfaWQiOiJZcWFRMFkzR2VuIiwic2NvcGVzIjpbXX0.i0X8uPlSxlBoinFuHPjYwSnkY8QARCefPLyvVexH8SeRO5fLjaHCh1gkh33fXdJGn5qTnIKomaDT91vgpKjsQbfMMHwJXCN2cQ1Ix-DLcMbSvfOhJST2UT1LTjHDvYW_2ne4DHGGdeYQpdJgtfoTmRm6Doz4gPS9kJ0O4Cl_CxFKQ2pMQP4niDTe64kR_Mh88SQWiojKDuFdcYjoYyiwrRj8CL-Wrgf660192I9o3BPpp2shNyzWCMIlKep0Y_mRiCXqHXKCY2uFvtWNwqAeyfLUBMFVbT2lraVJ-JmJtjKrey2O_BCkneX4iBnQ2y1rW8JJ3qbz4-KXB4heai196hiGyxvblrSvMm_BdLWH50bPsQpMQBBbdL8R3kxgGZjErPc6Z2mM35X4aRI8Nm-vz-7SpOi96bUyUezrNgnWVJ0AoFzEFyXlUR3r0xWPYM7oh4394JOTTT9CvpJFryW5hpluG79czUzL0ru9IRjDfgi9FzTzYq0ugwFzeMnuH_494Xk6ahcVyV81ZYeuYwacW1LYY9q62pk7yZYsV2rGd7TjttlEeV_JxbyomR-Cn6oJGCRXBvNdw__YalkvyYg5IbjSlRFB9KgcG0EBEEBzWiA9WjWqlE0w9usj3UJMqSuLm0rSOfaKdw0cQMqCUH37Wn59uTKrTyB-wR4WZlVbrzE';

            if (!$token) continue;

            $url = "{$baseUrl}/user/success";
            $data = json_encode(['phone' => $phoneNumber]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_HTTPHEADER     => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token"
                ],
                CURLOPT_TIMEOUT        => $timeout,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300 && $res = json_decode($response, true)) {
                if (isset($res['data']['customer'])) {
                    $success = $res['data']['customer']['successful_delivery'] ?? 0;
                    $total = $res['data']['customer']['total_delivery'] ?? 0;

                    return ['success' => $success, 'cancel' => $total - $success, 'total' => $total];
                }
            }
        }

        return ['success'=>0,'cancel'=>0,'total'=>0,'status'=>'success'];
    }

    // ------------------- CARRYBEE -------------------
    public static function carrybeeLogin(): ?string
    {
        $courier = config('courier.carrybee', []);
        $baseUrl = rtrim($courier['base_url'] ?? 'https://api-merchant.carrybee.com', '/');

        $config = [
            'phone'          => $courier['phone'] ?? null,
            'password'       => $courier['password'] ?? null,
            'client_id'      => $courier['client_id'] ?? null,
            'client_secret'  => $courier['client_secret'] ?? null,
            'client_context' => $courier['client_context'] ?? null,
            'token_cache'    => $courier['token_file'] ?? public_path('cache/carrybee_token.json'),
            'token_url'      => $courier['token_url'] ?? null,
            'base_url'       => $baseUrl,
        ];

        if (!$config['phone'] || !$config['password'] || !$config['client_id'] || !$config['client_secret'] || !$config['client_context']) {
            Log::error('Carrybee config missing required fields');
            return null;
        }

        $tokenDir = dirname($config['token_cache']);
        if (!is_dir($tokenDir) && !@mkdir($tokenDir, 0777, true) && !is_dir($tokenDir)) {
            Log::error('Carrybee token directory create failed', ['dir' => $tokenDir]);
            return null;
        }

        if (file_exists($config['token_cache'])) {
            $cache = json_decode(file_get_contents($config['token_cache']), true);
            if (!empty($cache['access_token']) && !empty($cache['expires_at']) && $cache['expires_at'] > time()) {
                return $cache['access_token'];
            }
        }

        $endpoints = array_values(array_unique(array_filter([
            $config['token_url'],
            $config['base_url'] . '/api/v2/login',
            $config['base_url'] . '/api/login',
            $config['base_url'] . '/login',
        ])));

        $payload = [
            'phone' => $config['phone'],
            'password' => $config['password'],
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'client_context' => $config['client_context'],
        ];

        foreach ($endpoints as $endpoint) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 20,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $body = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body === false || $httpCode < 200 || $httpCode >= 300) {
                Log::warning('Carrybee login endpoint failed', [
                    'endpoint' => $endpoint,
                    'http_code' => $httpCode,
                    'curl_error' => $curlError,
                    'body' => $body,
                ]);
                continue;
            }

            $res = json_decode((string) $body, true);
            if (!is_array($res)) {
                Log::warning('Carrybee login response is not JSON', [
                    'endpoint' => $endpoint,
                    'body' => $body,
                ]);
                continue;
            }

            $token = $res['accessToken']
                ?? $res['access_token']
                ?? $res['token']
                ?? ($res['data']['accessToken'] ?? null)
                ?? ($res['data']['access_token'] ?? null)
                ?? ($res['data']['token'] ?? null)
                ?? ($res['data']['jwt'] ?? null);

            if (!$token) {
                Log::warning('Carrybee token missing in response', [
                    'endpoint' => $endpoint,
                    'response' => $res,
                ]);
                continue;
            }

            $expiresIn = (int) (
                $res['expires_in']
                ?? ($res['data']['expires_in'] ?? 3600)
            );

            $saved = file_put_contents($config['token_cache'], json_encode([
                'access_token' => $token,
                'expires_at'   => time() + max(300, $expiresIn - 60),
            ]));

            if ($saved === false) {
                Log::error('Carrybee token file write failed', [
                    'path' => $config['token_cache'],
                ]);
                return null;
            }

            return $token;
        }

        return null;
    }

    public static function carrybeeLoginInfo(): array
    {
        $token = self::carrybeeLogin();
        $tokenFile = config('courier.carrybee.token_file', public_path('cache/carrybee_token.json'));
        $tokenFileExists = file_exists($tokenFile);
        $tokenFileData = null;

        if ($tokenFileExists) {
            $tokenFileData = json_decode((string) file_get_contents($tokenFile), true);
        }

        return [
            'token_generated' => !empty($token),
            'token_preview' => $token ? substr($token, 0, 20) . '...' : null,
            'token_file' => $tokenFile,
            'token_file_exists' => $tokenFileExists,
            'token_file_data' => $tokenFileData,
        ];
    }

    public static function getCarrybee(string $phoneNumber): array
    {
        $token = self::carrybeeLogin();
        if (!$token) {
            return ['success'=>0,'cancel'=>0,'total'=>0,'status'=>'error'];
        }

        $baseUrl = rtrim(config('courier.carrybee.base_url', 'https://api-merchant.carrybee.com'), '/');
        $businessId = (int) config('courier.carrybee.business_id', 10214);

        $cleanPhone = preg_replace('/\D+/', '', $phoneNumber);
        if (str_starts_with($cleanPhone, '880')) {
            $targetPhone = '+' . $cleanPhone;
        } elseif (str_starts_with($cleanPhone, '0')) {
            $targetPhone = '+88' . $cleanPhone;
        } else {
            $targetPhone = '+880' . $cleanPhone;
        }

        $url = $baseUrl . '/api/v2/businesses/' . $businessId . '/customers/' . rawurlencode($targetPhone);
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json',
            'Content-Type: application/json',
            "Authorization: Bearer $token"
        ),
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!$response || $httpCode < 200 || $httpCode >= 300) {
            Log::info('Carrybee API Response', [
                'phone' => $phoneNumber,
                'url' => $url,
                'status' => $httpCode ?: 'no_response',
                'curl_error' => $curlError,
                'body' => $response ?: null,
            ]);
            return ['success'=>0,'cancel'=>0,'total'=>0,'status'=>'error'];
        }else {
            $data = json_decode($response, true);

            $success = $data['data']['total_order'] -  $data['data']['cancelled_order'] ?? 0;
            $cancel = $data['data']['cancelled_order'] ?? 0;
            $total = $data['data']['total_order'] ?? 0;
        }


        return ['success' => $success, 'cancel' => $cancel, 'total' => $total, 'status' => 'success'];
    }

    // ------------------- PAPERFLY -------------------
    public static function getPaperfly(string $phoneNumber): array
    {
        return ['success'=>0,'cancel'=>0,'total'=>0,'status'=>'success'];
    }
}
