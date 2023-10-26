<?php

namespace App\Http\Livewire;

use App\Models\BusinessEntityHeatMap;
use App\Models\User;
use App\Models\UserEntityPreference;
use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReportGenerator extends Component
{
    /**
     * @var integer
     */
    public $selectedMap;

    /**
     * @var bool
     */
    public $show = false;

    /**
     * @var array
     */
    public $entityDropdownOption = [];

    /**
     * Mount the component
     */
    public function mount()
    {
        /** @var User $user */
        $user = Auth::user();
        $premium = $user->isPaid();
        $active = $user->isActive();

        $userPreferences = UserEntityPreference::where('user_id', $user->id)->get();
        $userHeatMaps = BusinessEntityHeatMap::with('businessEntity')->where('user_id', $user->id)->get();

        $userPreferencesCount = count($userPreferences);
        $userHeatMapCount = count($userHeatMaps);

        if ($premium && $active && $userPreferencesCount && $userHeatMapCount) {
            foreach ($userHeatMaps as $heatMap) {
                $lastBatchId = $heatMap->last_batch_id;

                $batch = DB::table('job_batches')
                    ->where('id',  $lastBatchId)
                    ->first();

                if (!$batch) {
                    continue;
                }

                $totalJobs = $batch->total_jobs;
                $pendingJobs = $batch->pending_jobs;
                $failedJobs = $batch->failed_jobs;

                $jobThreshold = $totalJobs * 0.5;

                $notCompletedJobs = $pendingJobs + $failedJobs;

                if ($notCompletedJobs > $jobThreshold) {
                    continue;
                }

                $this->entityDropdownOption[$heatMap->id] = $heatMap->businessEntity->name;
            }

            $this->show = true;
        }
    }

    /**
     * Generate PDF report
     *
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateReport()
    {
        if ($this->selectedMap) {
            $user = Auth::user();
            $heatMap = BusinessEntityHeatMap::findOrFail($this->selectedMap);
            $heatMap->load(['businessEntity', 'keyword']);

            try {
                $reportService = new ReportService($user, $heatMap->keyword, $heatMap->businessEntity);
                $pdfReportPath = $reportService->generatePdfReport('download');
            } catch (\Exception $exception) {
                return redirect()->back()->with('error', $exception->getMessage());
            }

            return response()->download($pdfReportPath);
        }

        return redirect()->back()->with('error', 'Business entity should be selected.');
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.report-generator');
    }
}
