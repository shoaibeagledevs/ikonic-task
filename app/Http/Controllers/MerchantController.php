<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Order;


class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {
    }

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');

        $count = Order::whereBetween('created_at', [$fromDate, $toDate])->count();

        $commissionOwed = Order::where('payout_status', Order::STATUS_UNPAID)
            ->whereHas('affiliate')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('commission_owed');

        $revenue = Order::whereBetween('created_at', [$fromDate, $toDate])->sum('subtotal');

        return response()->json([
            'count' => $count,
            'commission_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    }
}
