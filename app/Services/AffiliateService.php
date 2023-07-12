<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        $affiliate = Affiliate::create([
            'merchant_id' => $merchant->id,
            'email' => $email,
            'name' => $name,
            'commission_rate' => $commissionRate,
            'discount_code' => $this->generateDiscountCode(),
        ]);

        try {
            Mail::to($email)->send(new AffiliateCreated($affiliate));
        } catch (\Exception $e) {
            // Log the exception or perform any necessary error handling
            // For example, you can throw a custom exception to indicate the failure
            throw new AffiliateCreateException('Failed to send affiliate creation email.');
        }

        return $affiliate;
    }

    /**
     * Generate a unique discount code.
     *
     * @return string
     */
    protected function generateDiscountCode(): string
    {
        // Generate a random discount code or use any desired logic
        // This is just an example implementation
        return 'DISCOUNT' . rand(1000, 9999);
    }
}