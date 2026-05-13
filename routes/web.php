<?php
Route::get('/test-kavenegar', function () {
    try {
        $api = new Kavenegar\KavenegarApi(env('KAVENEGAR_API_KEY'));
        $info = $api->AccountInfo();
        return response()->json([
            'success' => true, 
            'credit' => $info->remaincredit,
            'expire' => $info->expiredate
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'error' => $e->getMessage()
        ], 500);
    }
});