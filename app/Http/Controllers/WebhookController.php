<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * Pass the necessary data to the process order method
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->all();

        // Validate the required data fields from the webhook payload
        $validatedData = $request->validate([
            'order_id' => 'required|string',
            'subtotal_price' => 'required|numeric',
            'merchant_domain' => 'required|string',
            'discount_code' => 'nullable|string',
            'customer_email' => 'required|email',
            'customer_name' => 'required|string',
        ]);

        // Process the order using the OrderService
        $this->orderService->processOrder($validatedData);

        return response()->json([
            'success' => true,
        ]);
    }
}
