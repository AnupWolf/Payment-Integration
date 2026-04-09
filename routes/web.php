<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::post('/payment/{payment_method}',function($payment_method,Request $request){

    if($payment_method =='esewa'){
        $curl = curl_init("https://rc-epay.esewa.com.np/api/epay/main/v2/form");
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($request->except('_token')));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);
            return response()->json([
                'data' => $response,  
                'method' => $payment_method
            ]);
    }else if($payment_method == 'khalti'){

        $curl = curl_init("https://a.khalti.com/api/v2/epayment/initiate/");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request->all()));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER,array(
            'Authorization: key b8bd3b4911d04288bb05b475ae1a7b21',
            'Content-Type: application/json',
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response, true);
        if (isset($response['payment_url'])) {
            return response()->json([
                'payment_url' => $response['payment_url'],
                'method' => $payment_method
            ]);
        } else {
            \Log::error('Invalid payment URL:', $response);
            return redirect()->back()->with('error', 'Invalid payment URL.');
        }
        
    }

    // return $response;

})->name('paymentMethod');

Route::post('/payment-success', function(Request $request){
    dd('inside success url');
    return $request->all();
})->name('paymentSuccess');
Route::post('/payment-failure',function(Request $request){
    dd('inside the failure url');
    return $request->all();
})->name('paymentFailure');

