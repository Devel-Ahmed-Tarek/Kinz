<?php

namespace App\Http\Controllers\Api;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Point;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\MyFatoorah;

class PointsWalletController extends Controller
{

    public function index()
    {
        // تنفيذ عملية الترقيم لعرض 10 سجلات في كل صفحة
        $points = Point::where("status", "1")->paginate(10);

        $data = $points->map(function ($point) {
            return [
                'id' => $point->id,
                'points' => $point->points,
                'amount' => $point->amount,
            ];
        });

        // إرجاع الاستجابة مع البيانات المرقمة
        return resourceApi::pagination($points, $data);
    }
    public function index12()
    {
        /* For simplicity check our PHP SDK library here https://myfatoorah.readme.io/php-library */

        //PHP Notice:  To enable MyFatoorah auto-update, kindly give the write/read permissions to the library folder
        //use zip file
        include 'myfatoorah-library-2.2/MyfatoorahLoader.php';
        include 'myfatoorah-library-2.2/MyfatoorahLibrary.php';

        //use composer
        //require 'vendor/autoload.php';
        //use MyFatoorah\Library\MyFatoorah;
        //use MyFatoorah\Library\API\Payment\MyFatoorahPayment;

        /* --------------------------- Configurations ------------------------------- */
        //Test
        $mfConfig = [
            /**
             * API Token Key (string)
             * Accepted value:
             * Live Token: https://myfatoorah.readme.io/docs/live-token
             * Test Token: https://myfatoorah.readme.io/docs/test-token
             */
            'apiKey' => 'rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL',
            /*
             * Vendor Country ISO Code (string)
             * Accepted value: KWT, SAU, ARE, QAT, BHR, OMN, JOD, or EGY. Check https://docs.myfatoorah.com/docs/iso-lookups
             */
            'vcCode' => 'KWT',
            /**
             * Test Mode (boolean)
             * Accepted value: true for the test mode or false for the live mode
             */
            'isTest' => true,
        ];

        /* --------------------------- InitiatePayment Endpoint --------------------- */
        $invoiceValue = 50;
        $displayCurrencyIso = 'KWD';

        //------------- Post Fields -------------------------
        //Check https://docs.myfatoorah.com/docs/initiate-payment#request-model
        //------------- Call the Endpoint -------------------------
        try {
            $mfObj = new MyFatoorahPayment($mfConfig);
            $paymentMethods = $mfObj->initiatePayment($invoiceValue, $displayCurrencyIso);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            die;
        }

        //You can save $paymentMethods information in database to be used later
        $paymentMethodId = 2;
        //foreach ($paymentMethods as $pm) {
        //    if ($pm->PaymentMethodEn == 'VISA/MASTER') {
        //        $paymentMethodId = $pm->PaymentMethodId;
        //        break;
        //    }
        //}

        /* --------------------------- ExecutePayment Endpoint ---------------------- */

        //Fill customer address array
        /* $customerAddress = array(
        'Block'               => 'Blk #', //optional
        'Street'              => 'Str', //optional
        'HouseBuildingNo'     => 'Bldng #', //optional
        'Address'             => 'Addr', //optional
        'AddressInstructions' => 'More Address Instructions', //optional
        ); */

        //Fill invoice item array
        /* $invoiceItems[] = [
        'ItemName'  => 'Item Name', //ISBAN, or SKU
        'Quantity'  => '2', //Item's quantity
        'UnitPrice' => '25', //Price per item
        ]; */

        //Fill suppliers array
        /* $suppliers = [
        [
        'SupplierCode'  => 1,
        'InvoiceShare'  => '2',
        'ProposedShare' => null,
        ]
        ]; */

        //Parse the phone string
        $phone = MyFatoorah::getPhone('+965 123456789');

        //------------- Post Fields -------------------------
        //Check https://docs.myfatoorah.com/docs/execute-payment#request-model
        $postFields = [
            //Fill required data
            'InvoiceValue' => $invoiceValue,
            'PaymentMethodId' => $paymentMethodId,
            //Fill optional data
            //'CustomerName'       => 'fname lname',
            //'DisplayCurrencyIso' => $displayCurrencyIso,
            //'MobileCountryCode'  => $phone[0],
            //'CustomerMobile'     => $phone[1],
            //'CustomerEmail'      => 'email@example.com',
            //'CallBackUrl'        => 'https://example.com/callback.php',
            //'ErrorUrl'           => 'https://example.com/callback.php', //or 'https://example.com/error.php'
            //'Language'           => 'en', //or 'ar'
            //'CustomerReference'  => 'orderId',
            //'CustomerCivilId'    => 'CivilId',
            //'UserDefinedField'   => 'This could be string, number, or array',
            //'ExpiryDate'         => '', //The Invoice expires after 3 days by default. Use 'Y-m-d\TH:i:s' format in the 'Asia/Kuwait' time zone.
            //'CustomerAddress'    => $customerAddress,
            //'InvoiceItems'       => $invoiceItems,
            //'Suppliers'          => $suppliers,
        ];

        //------------- Call the Endpoint -------------------------
        try {
            $mfObj = new MyFatoorahPayment($mfConfig);
            $data = $mfObj->executePayment($postFields);

            //You can save payment data in database as per your needs
            $invoiceId = $data->InvoiceId;
            $paymentLink = $data->PaymentURL;

            //Display the result to your customer
            //Redirect your customer to complete the payment process
            echo '<h3><u>Summary:</u></h3>';
            echo "To pay the invoice ID <b>$invoiceId</b>, click on:<br>";
            echo "<a href='$paymentLink' target='_blank'>$paymentLink</a><br><br>";

            echo '<h3><u>ExecutePayment Response Data:</u></h3><pre>';
            print_r($data);
            echo '</pre>';

            echo '<h3><u>InitiatePayment Response Data:</u></h3><pre>';
            print_r($paymentMethods);
            echo '</pre>';
        } catch (Exception $ex) {
            echo $ex->getMessage();
            die;
        }

    }

    public function purchasePoints(Request $request)
    {
        $user = Auth::user(); // الحصول على المستخدم المتصل

        // تحقق من البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1', // المبلغ الذي سيتم دفعه
            'type' => 'required|in:points,votes', // نوع الشراء: نقاط أو تصويطات
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        // إعداد بيانات الطلب إلى MyFatoorah
        $paymentData = [
            'NotificationOption' => 'ALL',
            'InvoiceValue' => $request->amount,
            'CustomerName' => $user->name,
            'CustomerEmail' => $user->email,
            'CallBackUrl' => route('myfatoorah.callback'),
            'ErrorUrl' => route('myfatoorah.error'),
            'Language' => 'en',
            'DisplayCurrencyIso' => 'KWD',
        ];

        try {
            $client = new Client();
            $response = $client->post('https://api.myfatoorah.com/v2/ExecutePayment', [
                'headers' => [
                    'Authorization' => 'Bearer ' . "rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL", // مفتاح الـ API الخاص بك
                    'Content-Type' => 'application/json',
                ],
                'json' => $paymentData,
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['IsSuccess']) && $result['IsSuccess'] == true) {
                // استجابة ناجحة من MyFatoorah
                return response()->json([
                    'message' => 'Payment initialized successfully.',
                    'payment_url' => $result['Data']['PaymentURL'],
                ], 200);
            } else {
                return response()->json(['message' => 'Payment initialization failed.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment initialization error: ' . $e->getMessage()], 500);
        }
    }

    // دالة استلام الإشعار عند نجاح الدفع
    public function paymentCallback(Request $request)
    {
        // استخراج البيانات من MyFatoorah بعد الدفع الناجح
        $paymentId = $request->paymentId;

        $client = new Client();
        $response = $client->post('https://api.myfatoorah.com/v2/GetPaymentStatus', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'KeyType' => 'PaymentId',
                'Key' => $paymentId,
            ],
        ]);

        $result = json_decode($response->getBody(), true);

        if ($result['IsSuccess']) {
            // التحقق من نجاح الدفع
            $invoiceValue = $result['Data']['InvoiceValue'];
            $userEmail = $result['Data']['CustomerEmail'];

            // البحث عن المستخدم بناءً على البريد الإلكتروني
            $user = User::where('email', $userEmail)->first();

            if ($user) {
                // تحديد نوع الشراء (نقاط أو تصويطات)
                $type = $request->type;

                if ($type == 'points') {
                    // زيادة النقاط في المحفظة
                    $user->points_wallet += $invoiceValue; // نفترض أن قيمة المبلغ تعادل النقاط
                } elseif ($type == 'votes') {
                    // زيادة التصويطات في المحفظة
                    $user->votes_wallet += $invoiceValue; // نفترض أن قيمة المبلغ تعادل التصويطات
                }

                $user->save();

                return response()->json(['message' => 'Payment processed successfully and wallet updated.'], 200);
            }

            return response()->json(['message' => 'User not found.'], 404);
        } else {
            return response()->json(['message' => 'Payment failed.'], 400);
        }
    }

    // عرض رصيد محفظة النقاط
    public function showPointsWallet()
    {
        $user = Auth::user();
        $wallet = $user->pointsWallet;

        if (!$wallet) {
            return response()->json(['message' => 'Points wallet not found'], 404);
        }

        return response()->json(['balance' => $wallet->balance], 200);
    }

    // إضافة رصيد إلى محفظة النقاط
    public function addPoints(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->pointsWallet;

        if (!$wallet) {
            return response()->json(['message' => 'Points wallet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $wallet->balance += $request->amount;
        $wallet->save();

        return response()->json(['message' => 'Points added successfully', 'balance' => $wallet->balance], 200);
    }

    public function deductPoints(Request $request)
    {
        $user = Auth::user(); // الحصول على المستخدم المتصل

        // تحقق من البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1', // عدد النقاط التي سيتم خصمها
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        // التحقق من أن المستخدم لديه رصيد كافٍ من النقاط
        if ($user->points_wallet < $request->points) {
            return response()->json(['message' => 'Not enough points in the wallet.'], 400);
        }

        // خصم النقاط من محفظة النقاط
        $user->points_wallet -= $request->points;
        $user->save();

        return response()->json(['message' => 'Points deducted successfully.', 'remaining_points' => $user->points_wallet], 200);
    }

}
