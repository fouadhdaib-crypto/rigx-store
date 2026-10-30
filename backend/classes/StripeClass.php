<?php
class StripeClass
{
    public function createCheckoutSession($items, $orderId)
    {
        $data = array(
            'mode' => 'payment',
            'success_url' => SITE_URL . '/frontend/User/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => SITE_URL . '/frontend/User/payment_cancel.php',
            'client_reference_id' => $orderId
        );

        $i = 0;
        foreach ($items as $item) {
            $data['line_items'][$i]['price_data']['currency'] = 'usd';
            $data['line_items'][$i]['price_data']['product_data']['name'] = $item['name'];
            $data['line_items'][$i]['price_data']['unit_amount'] = (int) round($item['price'] * 100);
            $data['line_items'][$i]['quantity'] = $item['quantity'];
            $i++;
        }

        return $this->sendRequest('POST', '/v1/checkout/sessions', $data);
    }

    public function getSession($sessionId)
    {
        return $this->sendRequest('GET', '/v1/checkout/sessions/' . $sessionId);
    }

    private function sendRequest($method, $path, $data = array())
    {
        $ch = curl_init('https://api.stripe.com' . $path);
        curl_setopt($ch, CURLOPT_USERPWD, STRIPE_SECRET_KEY . ':');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}