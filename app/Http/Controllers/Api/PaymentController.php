<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Payment;

class PaymentController extends Controller
{
    //

    public function decryptData($encryptedData)
    {
        // dd($encryptedData);
        $key = "your_secret_key"; // Use the same secret key
        $encryptedData = base64_decode($encryptedData);
        $iv = substr($encryptedData, 0, 16);
        $ciphertext = substr($encryptedData, 16);
        return json_decode(openssl_decrypt($ciphertext, "AES-256-CBC", $key, 0, $iv), true);
    }

    public function encryptData($data)
    {
        $key = "your_secret_key"; // Use a strong secret key
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt(json_encode($data), "AES-256-CBC", $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }



    public function submitPaymentForm(Request $request) {
            $decrypted = $this->decryptData($request['data']);
            // dd($decrypted);
            $name = $decrypted['username'];
            $amount = $decrypted['amount']; 
            $get_order_id = $decrypted['order_id'];


            if($name !='' && $amount !=''){            
                $merchantId = config('phonepe.merchantId');
                $apiKey12 = config('phonepe.apiKey');
                print_r($apiKey12);
                $redirectUrl = route('confirm');
                $order_id = uniqid(); // Generate unique order id
    
                $transaction_data = array(
                    'merchantId' => $merchantId,
                    'merchantTransactionId' => $order_id,
                    "merchantUserId"=>'',
                    'amount' => 1000,
                    'redirectUrl'=>$redirectUrl,
                    'redirectMode'=>"POST",
                    'callbackUrl'=>$redirectUrl,
                    "paymentInstrument"=> array(    
                        "type"=> "PAY_PAGE",
                    )
                );


                    $encode = json_encode($transaction_data);
                    $payloadMain = base64_encode($encode);
                    print_r('$apiKey12',$apiKey12);
                    print_r($payloadMain);
                    $salt_index = 1;
                    $payload = $payloadMain . "/pg/v1/pay" . $apiKey12;
                    $sha256 = hash("sha256",$payload);
                    $final_x_header = $sha256 . '###' . $salt_index;
                    print_r($final_x_header);

                   
                    $request = json_encode(array('request'=>$payloadMain));
                    
                    $curl = curl_init();

                    curl_setopt_array($curl, [
                    CURLOPT_URL => "https://api.phonepe.com/apis/hermes/pg/v1/pay",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $request,
                        CURLOPT_HTTPHEADER => [
                            "Content-Type: application/json",
                            "X-VERIFY: " . $final_x_header,
                            "accept: application/json"
                        ],
                    ]);

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                echo "cURL Error #:" . $err;
                } else {
                $res = json_decode($response);

                // Store information into database

                $data = [
                    'merchantId' => $merchantId,
                    'merchantTransactionId' => $order_id,
                    "merchantUserId"=>$order_id,
                    'amount' => $amount,
                    'redirectUrl'=>$redirectUrl,
                    'redirectMode'=>"POST",
                    'callbackUrl'=>$redirectUrl,
                    "paymentInstrument"=> json_encode([    
                        "type"=> "PAY_PAGE",
                ]),
                    'payment_status' => 'PAYMENT_PENDING',
                    'order_id' => $get_order_id
                ];
                dd($res);
                    Payment::create($data);

                    if(isset($res->code) && ($res->code=='PAYMENT_INITIATED')){
                        $payUrl=$res->data->instrumentResponse->redirectInfo->url;
                        return redirect()->away($payUrl);
                    }else{
                                dd('ERROR : ' . $res);
                    }
                }
            } 
            
    }



}
