<?php

namespace AvoRed\Ecommerce\Payment\Stripe;

use Stripe\Charge;
use Stripe\Stripe;
use AvoRed\Framework\Models\Database\Configuration;
use AvoRed\Framework\Payment\Payment as PaymentEcommerce;
use AvoRed\Framework\Payment\Contracts\Payment as PaymentContracts;
use AvoRed\Ecommerce\Models\Database\User;

class Payment extends PaymentEcommerce implements PaymentContracts
{
    const CONFIG_KEY = 'payment_stripe_enabled';

    const CONFIG_PUBLISHABLE_KEY = 'payment_stripe_publishable_key';

    const CONFIG_SECRET_KEY = 'avored_stripe_secret_key';
    /**
     * Payment Option Identifier.
     *
     * @var string
     */
    protected $identifier = 'stripe';

    /**
     * Payment Option Name.
     *
     * @var string
     */
    protected $name = 'Stripe';

    /**
     * Payment Option View.
     *
     * @var string
     */
    protected $view = 'avored-ecommerce::payment.stripe.index';

    public function enable()
    {
        $model = new Configuration();
        $isEnabled = $model->getConfiguration(self::CONFIG_KEY);
        if (null === $isEnabled || false == $isEnabled) {
            return false;
        }

        return true;
    }

    public function identifier()
    {
        return $this->identifier;
    }

    public function name()
    {
        return $this->name;
    }

    public function view()
    {
        return $this->view;
    }

    public function with()
    {
        $token = Configuration::getConfiguration(self::CONFIG_PUBLISHABLE_KEY);
        $data = ['token' => $token];

        return $data;
    }

    /*
     * Nothing to do
     *
     */
    public function process($orderData, $cartProducts, $request)
    {
        $subTotal = 0;
        $taxTotal = 0;

        foreach ($cartProducts as $product) {
            $subTotal += $product->price() * $product->qty();
            $taxTotal += $product->tax() * $product->qty();
        }

        $total = (round($subTotal, 2) + round($taxTotal, 2)) * 100;

        $totalCents = (int) $total;
        $apiKey = Configuration::getConfiguration(self::CONFIG_SECRET_KEY);

        Stripe::setApiKey($apiKey);
        //dd($orderData);
        //$user = User::find($orderData['user_id']);
        //$customer = \Stripe\Customer::create([
        //    'email' => $user->email,
        //    'description' => 'Customer for One of Charge ' . $user->id,
        //    'source' => $request->get('stripeToken')
        //  ]);

        //dd($request->all());
        //dd($request->get('stripeToken'));

        $response = Charge::create([
            'amount' => $totalCents,
            'currency' => 'nzd',
            'source' => $request->get('stripeToken'), // obtained with Stripe.js
            'description' => 'AvoRed E commerce Payment',
        ]);

        return $response;
    }
}
