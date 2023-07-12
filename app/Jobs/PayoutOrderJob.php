<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {
    }

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        try {
            // Perform the payout using the API service
            $apiService->sendPayout($this->order->affiliate_id, $this->order->commission_owed);

            // Update the order status to paid
            $this->order->payout_status = Order::STATUS_PAID;
            $this->order->save();
        } catch (\Exception $e) {
            // Log the exception or perform any necessary error handling
            // If an exception occurs, the order status will remain unpaid
            // and the job will be retried according to the queue configuration
            \Log::error("Payout failed for order ID: {$this->order->id}. Error: {$e->getMessage()}");
        }
    }
}
