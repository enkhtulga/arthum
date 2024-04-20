<?php

namespace Artum\Qpay\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use Session;


class QpayController extends Controller
{
    private $qpayUrl;
    private $clientId;
    private $clientSecret;
    private $invoiceCode;
    private $amount;
    private $view;

    public function __construct()
    {
        $this->clientId = env('QPAY_USERNAME');
        $this->clientSecret = env('QPAY_PASSWORD');
        $this->invoiceCode = env('QPAY_INVOICE_CODE');
        $this->qpayUrl = env('QPAY_URL');
    }
    public function pay()
    {
        $amount = 0;
        $orderId = 'orlogo';
        $this->view = 'card_payment';

        if (Session::has('payment_type')) {
            if (Session::get('payment_type') == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = round($combined_order->grand_total);
                $orderId = 'combined-' . Session::get('combined_order_id');
            } elseif (Session::get('payment_type') == 'wallet_payment') {
                $orderId = 'wallet-' . rand(1000, 10000);
                $amount = round(Session::get('payment_data')['amount']);
            } elseif (Session::get('payment_type') == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);
                $amount = round($customer_package->amount);
                $orderId = 'cpp-' . Session::get('payment_data')['customer_package_id'];
            } elseif (Session::get('payment_type') == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail(Session::get('payment_data')['seller_package_id']);
                $amount = round($seller_package->amount);
                $orderId = 'spp-' . Session::get('payment_data')['seller_package_id'];
                $this->view = 'seller_package_payment';
            }
        }

        $this->amount = $amount;

        $token = $this->getToken();
        $responseData = [];

        if ($token) {
            $tokenUrl = $this->qpayUrl . 'invoice';
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            );
            $body = array(
                'invoice_code' => $this->invoiceCode,
                'sender_invoice_no' => $orderId,
                "invoice_receiver_code" => "terminal",
                "invoice_description" => $orderId,
                'amount' => $amount,
                'callback_url' => route('qpay.callback', ['order_id', $orderId])
            );
            $requestbodyJson = json_encode($body);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $tokenUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_POSTFIELDS => $requestbodyJson,
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $responseData = json_decode($response, true);
            if (isset($responseData['error'])) {
                return false;
            }

            Session::put('qpay_response', $responseData);

            if ($this->view == 'seller_package_payment') {
                return redirect()->route('qpay.paymentSpp');
            } else {
                return redirect()->route('qpay.payment');
            }
        } else {
            flash(translate("Something went wrong."))->error();
            return redirect()->route('checkout.shipping_info');
        }
    }

    public function payment()
    {
        $responseData = Session::get('qpay_response');
        return view('qpay::payment', ['response' => $responseData]);
    }

    public function paymentSpp()
    {
        $responseData = Session::get('qpay_response');
        return view('qpay::seller_packages/qpay_payment', ['response' => $responseData]);
    }

    public function callback()
    {
        $token = $this->getToken();
        if ($token) {
            $response = $this->checkPayment($token);
            $responseData = json_decode($response['body'], true);
            if (intval($responseData['paid_amount']) >= intval($this->amount) && intval($responseData['count']) > 0 && count($responseData['rows']) > 0) {
                return redirect()->route('qpay.success');
            }
        }
    }

    public function responseQpay()
    {
        $token = $this->getToken();
        $response = $this->checkPayment($token);
        $responseData = json_decode($response, true);

        if ($this->isPaymentSuccessful($responseData)) {
            return response()->json([
                'redirect' => route('qpay.success')
            ]);
        } elseif (isset($responseData['error']) && $responseData['error'] === 'NO_CREDENTIALS') {
            $token = $this->getToken();
            if ($token) {
                $responseCheck = $this->checkPayment($token);
                $responseDataCheck = json_decode($responseCheck['body'], true);
                if ($this->isPaymentSuccessful($responseDataCheck)) {
                    return response()->json([
                        'redirect' => route('qpay.success')
                    ]);
                }
            }
        }
    }

    public function success(Request $request)
    {
        $this->clearProperties();
        $payment_type = Session::get('payment_type');

        if ($payment_type == 'cart_payment') {
            return (new CheckoutController)->checkout_done(Session::get('combined_order_id'), $request->payment_details);
        }
        if ($payment_type == 'wallet_payment') {
            return (new WalletController)->wallet_payment_done(Session::get('payment_data'), $request->payment_details);
        }
        if ($payment_type == 'customer_package_payment') {
            return (new CustomerPackageController)->purchase_payment_done(Session::get('payment_data'), $request->payment_details);
        }
        if ($payment_type == 'seller_package_payment') {
            return (new SellerPackageController)->purchase_payment_done(Session::get('payment_data'), $request->payment_details);
        }
    }

    private function clearProperties()
    {
        $this->amount = null;
    }

    private function getToken()
    {
        $tokenUrl = $this->qpayUrl . 'auth/token';
        $credentials = $this->clientId . ':' . $this->clientSecret;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_USERPWD => $credentials, // Basic Authentication
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseData = json_decode($response, true);
        if (isset($responseData['error'])) {
            return false;
        }

        return isset($responseData['access_token']) ? $responseData['access_token'] : false;
    }

    private function isPaymentSuccessful($responseData)
    {
        return isset($responseData['paid_amount']) && intval($responseData['paid_amount']) >= intval($this->amount) &&
            intval($responseData['count']) > 0 && count($responseData['rows']) > 0;
    }

    private function checkPayment($token)
    {
        $sessionResponseData = Session::get('qpay_response');
        $invoiceId = $sessionResponseData['invoice_id'];
        $checkUrl = $this->qpayUrl . 'payment/check';
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        );
        $body = array(
            "object_type" => "INVOICE",
            "object_id" => $invoiceId,
            "offset" => array(
                "page_number" => 1,
                "page_limit" => 100
            )
        );
        $requestbodyJson = json_encode($body);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $checkUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $requestbodyJson,
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
