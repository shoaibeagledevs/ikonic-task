<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // Find the merchant based on the domain
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();

        if (!$merchant) {
            // If the merchant is not found, you can throw an exception or handle the case accordingly
            // For example, you can log an error and return
            \Log::error("Merchant not found for domain: {$data['merchant_domain']}");
            return;
        }

        // Check if an affiliate already exists for the customer email
        $affiliate = $this->affiliateService->findAffiliateByEmail($data['customer_email']);

        if (!$affiliate) {
            // If no affiliate exists, register a new affiliate
            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name']);
        }

        // Check if the order already exists based on the order_id
        $existingOrder = Order::where('order_id', $data['order_id'])->first();

        if ($existingOrder) {
            // If the order already exists, you can ignore it or perform any necessary actions
            // For example, you can log a warning and return
            \Log::warning("Duplicate order found with ID: {$data['order_id']}");
            return;
        }

        // Create a new order record
        $order = new Order();
        $order->order_id = $data['order_id'];
        $order->merchant_id = $merchant->id;
        $order->affiliate_id = $affiliate->id;
        $order->subtotal = $data['subtotal_price'];
        $order->discount_code = $data['discount_code'];
        $order->save();

        // Calculate the commission owed based on the default commission rate
        $commissionOwed = $data['subtotal_price'] * $merchant->default_commission_rate;
        $order->commission_owed = $commissionOwed;
        $order->save();

        // You can perform any other necessary actions related to order processing here
    }
}