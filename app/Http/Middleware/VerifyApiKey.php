<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Grab header
        $token = $request->header('X-API-KEY');
        if (! $token) {
            return $this->respond(400, 'Missing API key.');
        }

        // 2) Decode & parse
        $info = $this->validateApiKey($token);
        if (! $info) {
            return $this->respond(401, 'Invalid or expired API key.');
        }
        [$sig, $ts, $pkg] = [
            $info['signatureHash'],
            (int)$info['timestamp'],
            $info['package'],
        ];

        // 3) Timestamp check
        if (abs(time() - $ts) > 120) {
            return $this->respond(408, 'Token expired.');
        }

        // 4) Step 1: fetch the application's CSV of keys
        $app = DB::table('applications')
            ->select('api_keys')
            ->where('package_name', $pkg)
            ->first();

        if ($app) {
            $keys = explode(',', $app->api_keys);
            if (in_array($sig, $keys, true)) {
                // valid & not expired
                $request->attributes->set('validated_signature_hash', $sig);
                $request->attributes->set('validated_package_name', $pkg);
                return $next($request);
            }
        }

        // 5) Step 2: fallback to api_keys table
        $logged = DB::table('api_keys')
            ->where('package_name', $pkg)
            ->where('api_key', $sig)
            ->first();

        if (! $logged) {
            // Step 3: insert & reject
            DB::table('api_keys')->insert([
                'package_name' => $pkg,
                'api_key'      => $sig,
                'status_flag'  => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            return $this->respond(401, 'Unauthorized: API key logged for review.');
        }

        // Step 4: logged but disabled
        if ($logged->status_flag == 0) {
            return $this->respond(403, 'Unauthorized: API key is disabled.');
        }

        // final fallback
        return $this->respond(403, 'Unauthorized: Access denied.');
    }

    private function respond(int $code, string $message)
    {
        return response()->json([
            'status'  => 'error',
            'code'    => $code,
            'message' => $message,
            'data'    => null,
        ], 200);
    }

    private function reverseCharMap(string $input): string
    {
        // mapOut → mapIn translation via strtr is ~5× faster than a PHP loop
        $mapIn  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.-';
        $mapOut = 'QWERTYUIOPASDFGHJKLZXCVBNMlkjhgfdsamnbvcxzpoiuytrewq9876543210_~';
        return strtr($input, $mapOut, $mapIn);
    }

    private function validateApiKey(string $token): ?array
    {
        $decoded = $this->reverseCharMap($token);
        $parts   = explode('|', $decoded, 3);

        if (count($parts) !== 3) {
            return null;
        }

        return [
            'signatureHash' => $parts[0],
            'timestamp'     => $parts[1],
            'package'       => $parts[2],
        ];
    }
}
