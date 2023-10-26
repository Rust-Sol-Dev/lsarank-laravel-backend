<?php

namespace App\Http\Controllers;

use App\Exports\LsaRankExport;
use App\Http\Requests\ReportDownloadRequest;
use App\Models\BusinessEntity;
use App\Models\BusinessEntityHeatMap;
use App\Models\Keyword;
use App\Models\User;
use App\Repositories\KeywordMetricRepository;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\BusinessEntityZipcodeRadiusRanking;
use App\Jobs\GenerateAndSendWeeklyReport;

class BillingController extends Controller
{
    /**
     * Return billing view
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $plan = 'Free';

        if ($user->isPaid()) {
            $plan = 'Premium';
        }

        return view('billing', ['billingPlan' => $plan]);
    }
}
