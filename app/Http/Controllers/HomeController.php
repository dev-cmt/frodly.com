<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\SeoTrait;
use App\Helpers\FrodlyHelper;
use App\Models\Page;
use App\Models\Sale;
use App\Models\PricingPlan;
use App\Models\User;
use Carbon\Carbon;


class HomeController extends Controller
{
    use SeoTrait;

    public function welcome()
    {
        $pricingPlans = PricingPlan::where('status', 1)->get();

        // SEO
        $page = Page::with('seo')->where('slug','home')->firstOrFail();
        $this->setSeo([
            'title'       => $page->seo->meta_title ?? $page->title,
            'description' => $page->seo->meta_description ?? '',
            'keywords'    => $this->formatKeywords($page->seo->meta_keywords ?? ''),
            'image'       => $page->seo->og_image ?? '',
            'canonical'   => url()->current(),
        ]);
        $seo_tags = $this->generateTags();

        $breadcrumbs = $this->generateBreadcrumbJsonLd([
            ['name' => 'Home', 'url' => url('/')],
        ]);

        return view('frontend.welcome', compact('pricingPlans', 'seo_tags','breadcrumbs'));
    }

    public function checkout($planId = null)
    {
        if ($planId) {
            $plan = PricingPlan::findOrFail($planId);
        }

        return view('frontend.checkout', compact('plan'));
    }

    public function placeOrder(Request $request)
    {

        // Get authenticated user or create/update
        $user = Auth::user();
        if (!$user) {
            // Create user
            $user = User::updateOrCreate(
                ['email' => $request->email],
                [
                    'name'  => $request->name,
                    'phone' => $request->phone,
                    'password' => $request->password ? Hash::make($request->password) : null,
                    'status'=> 1,
                ]
            );
            Auth::login($user);
        } else {
            // Update user info
            $user->update([
                'name' => $request->name,
                'phone' => $request->phosne,
            ]);
        }
        // Fetch the plan
        $plan = PricingPlan::findOrFail($request->plan_id);

        // Recalculate end date if package changed
        $start = Carbon::parse($request->start_date ?? now());
        $periods = [
            'monthly' => 1,
            'quarterly' => 3,
            'half-yearly' => 6,
            'yearly' => 12,
            'lifetime' => null,
        ];
        $months = $periods[$plan->billing_cycle] ?? 0;
        $end = $months ? $start->copy()->addMonths($months) : null;
        $start_date = $start->toDateString();
        $end_date = $end?->toDateString();

        // Generate unique invoice number
        do {
            $month = date('m');
            $day = date('d');
            $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 3));
            $invoice_no = "FRD$day$month$random";
        } while (DB::table('sales')->where('invoice_number', $invoice_no)->exists());

        // Create sale record
        $sale = Sale::create([
            'user_id'        => $user->id,
            'plan_id'        => $plan->id,
            'invoice_number' => $invoice_no,
            'amount'         => $plan->price,
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'status'         => 'pending', // default status
            'allowed_domains'=> $plan->domain_limit,
            'allowed_requests'=> $plan->request_limit,
        ]);

        // Handle Payment Method
        switch ($request->payment_method) {
            case 'sslcommerz':
                // redirect to SSLCOMMERZ page
                return redirect()->route('payment.sslcommerz', $sale->id);
            case 'nagad':
                return redirect()->route('payment.nagad', $sale->id);
            case 'bkash':
                return redirect()->route('payment.bkash', $sale->id);
            case 'cod':
                $sale->update(['status' => 'active']); // mark COD as completed immediately
                return redirect()->route('order.success', $sale->id)->with('success', 'অর্ডার সফলভাবে তৈরি হয়েছে।');
            default:
                return back()->withErrors('পেমেন্ট মেথড সঠিক নয়।');
        }
    }

    public function orderSuccess($orderId)
    {
        $sale = Sale::with('plan', 'user')->findOrFail($orderId);
        return view('frontend.order-success', compact('sale'));
    }

    public function pageFrodly()
    {
        return view('frontend.frodly-checker');
    }

    public function getFrodly(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/^\d{11}$/'
        ]);

        $phone = $request->input('phone');

        $results = [
            'Redx'       => FrodlyHelper::getRedx($phone),
            'SteadFast'  => FrodlyHelper::getSteadFast($phone),
            'Pathao'     => FrodlyHelper::getPathao($phone),
            'Carrybee'   => FrodlyHelper::getCarrybee($phone),
            'Paperfly'   => FrodlyHelper::getPaperfly($phone),
        ];

        $placeholderLogos = [
            'redx'       => asset('frodly/courier-logo/redx.jpg'),
            'steadfast'  => asset('frodly/courier-logo/steadfast.jpg'),
            'pathao'     => asset('frodly/courier-logo/pathao.jpg'),
            'carrybee'   => asset('frodly/courier-logo/carrybee.jpg'),
            'paperfly'   => asset('frodly/courier-logo/paperfly.jpg'),
        ];

        $summaries = [];
        $total_all = $success_all = $cancel_all = 0;

        foreach ($results as $courier => $data) {
            $summaries[$courier] = [
                'logo'    => $placeholderLogos[strtolower($courier)] ?? '',
                'total'   => $data['total'],
                'success' => $data['success'],
                'cancel'  => $data['cancel']
            ];

            $total_all   += $data['total'];
            $success_all += $data['success'];
            $cancel_all  += $data['cancel'];
        }

        return response()->json([
            'status' => true,
            'message' => 'Courier info retrieved successfully.',
            'data' => [
                'Summaries'    => $summaries,
                'totalSummary' => [
                    'total'       => $total_all,
                    'success'     => $success_all,
                    'cancel'      => $cancel_all,
                    'successRate' => $total_all ? round(($success_all / $total_all) * 100) : 0,
                    'cancelRate'  => $total_all ? round(($cancel_all / $total_all) * 100) : 0
                ]
            ]
        ]);
    }
}
