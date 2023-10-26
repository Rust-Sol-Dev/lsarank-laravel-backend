<div>
    @if($show)
        <div class="row">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                <div class="card border-3 border-top-panel">
                    <div style="height: 150px!important;"  class="card-body">
                        <div class="row">
                            <div style="padding-left: unset; margin: auto" class="col-3">
                                <img height="80px" width="80px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/trend.png') }}">
                            </div>
                            <div style="padding-left: unset; margin: auto" class="col-4 text-center">
                                <h5 class="panel-text">
                                    @if($dailyDataMissing) N/A @else Today's Average Rank: {{ $dailyRank }} @endif
                                </h5>
                            </div>
                            @if($dailyDataMissing)
                                <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                    <div class="metric-label d-inline-block float-right font-weight-bold">
                                        <span class="ml-1">N/A</span>
                                    </div>
                                </div>
                            @else
                                @if($dailyTrend == 'up')
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label flex font-weight-bold">
                                            <i style="font-size: 3.93em; z-index: 0" class="text-success-metrics fa fa-fw fa-arrow-up"></i><span style="font-size: 1.33em;"  class="ml-1 mt-1">{{ $dailyPercentage }}%</span>
                                        </div>
                                    </div>
                                @elseif($dailyTrend == 'down')
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label flex font-weight-bold">
                                            <i style="font-size: 3.93em; z-index: 0" class="text-danger-metrics fa fa-fw fa-arrow-down"></i><span style="font-size: 1.33em;"  class="ml-1 mt-1">{{ $dailyPercentage }}%</span>
                                        </div>
                                    </div>
                                @else
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label d-inline-block float-right font-weight-bold">
                                            <span style="font-size: 1.33em;" class="ml-1">{{ $dailyPercentage }}%</span>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                <div class="card border-3 border-top-panel">
                    <div style="height: 150px!important;"  class="card-body">
                        <div class="row">
                            <div style="padding-left: unset; margin: auto" class="col-3">
                                <img height="80px" width="80px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/trend.png') }}">
                            </div>
                            <div style="padding-left: unset; margin: auto" class="col-4 text-center">
                                <h5 class="panel-text">
                                    @if($weeklyDataMissing) N/A @else Weekly Average Rank: {{ $weeklyRank }} @endif
                                </h5>
                            </div>
                            @if($weeklyDataMissing)
                                <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                    <div class="metric-label d-inline-block float-right font-weight-bold">
                                        <span class="ml-1">N/A</span>
                                    </div>
                                </div>
                            @else
                                @if($weeklyTrend == 'up')
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label flex font-weight-bold">
                                            <i style="font-size: 3.93em; z-index: 0" class="text-success-metrics fa fa-fw fa-arrow-up"></i><span style="font-size: 1.33em;"  class="ml-1 mt-1">{{ $weeklyPercentage }}%</span>
                                        </div>
                                    </div>
                                @elseif($weeklyTrend == 'down')
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label flex font-weight-bold">
                                            <i style="font-size: 3.93em; z-index: 0" class="text-danger-metrics fa fa-fw fa-arrow-down"></i><span style="font-size: 1.33em;"  class="ml-1 mt-1">{{ $weeklyPercentage }}%</span>
                                        </div>
                                    </div>
                                @else
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label d-inline-block float-right font-weight-bold">
                                            <span style="font-size: 1.33em;" class="ml-1">{{ $weeklyPercentage }}%</span>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                <div class="card border-3 border-top-panel">
                    <div style="height: 150px!important;"  class="card-body">
                        <div class="row">
                            <div style="padding-left: unset; margin: auto" class="col-3">
                                <img height="80px" width="80px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/review.png') }}">
                            </div>
                            <div style="padding-left: unset; margin: auto" class="col-4 text-center">
                                <h5 class="panel-text">
                                    @if($dailyReviewsDataMissing) N/A @else New LSA Reviews Today: {{ $dailyReviews }} @endif
                                </h5>
                            </div>
                            @if($dailyReviewsDataMissing)
                                <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                    <div class="metric-label d-inline-block float-right font-weight-bold">
                                        <span class="ml-1">N/A</span>
                                    </div>
                                </div>
                            @else
                                @if($dailyReviewsTrend == 'up')
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label flex font-weight-bold">
                                            <i style="font-size: 3.93em; z-index: 0" class="text-success-metrics fa fa-fw fa-arrow-up"></i><span style="font-size: 1.33em;"  class="ml-1 mt-1">{{ $dailyReviewsPercentage }}%</span>
                                        </div>
                                    </div>
                                @elseif($dailyReviewsTrend == 'down')
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label flex font-weight-bold">
                                            <i style="font-size: 3.93em; z-index: 0" class="text-danger-metrics fa fa-fw fa-arrow-down"></i><span style="font-size: 1.33em;"  class="ml-1 mt-1">{{ $dailyReviewsPercentage }}%</span>
                                        </div>
                                    </div>
                                @else
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label d-inline-block float-right font-weight-bold">
                                            <span style="font-size: 1.33em;" class="ml-1">{{ $dailyReviewsPercentage }}%</span>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                <div class="card border-3 border-top-panel">
                    <div style="height: 150px!important;"  class="card-body">
                        <div class="row">
                            <div style="padding-left: unset; margin: auto" class="col-3">
                                <img height="80px" width="80px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/review.png') }}">
                            </div>
                            <div style="padding-left: unset; margin: auto" class="col-4 text-center">
                                <h5 class="panel-text">
                                    @if($weeklyReviewsDataMissing) N/A @else New LSA Reviews This Week: {{ $weeklyReviews }} @endif
                                </h5>
                            </div>
                            @if($weeklyReviewsDataMissing)
                                <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                    <div class="metric-label d-inline-block float-right font-weight-bold">
                                        <span class="ml-1">N/A</span>
                                    </div>
                                </div>
                            @else
                                @if($weeklyReviewsTrend == 'up')
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label flex font-weight-bold">
                                            <i style="font-size: 3.93em; z-index: 0" class="text-success-metrics fa fa-fw fa-arrow-up"></i><span style="font-size: 1.33em;"  class="ml-1 mt-1">{{ $weeklyReviewsPercentage }}%</span>
                                        </div>
                                    </div>
                                @elseif($weeklyReviewsTrend == 'down')
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label flex font-weight-bold">
                                            <i style="font-size: 3.93em; z-index: 0" class="text-danger-metrics fa fa-fw fa-arrow-down"></i><span style="font-size: 1.33em;"  class="ml-1 mt-1">{{ $weeklyReviewsPercentage }}%</span>
                                        </div>
                                    </div>
                                @else
                                    <div style="padding-left: unset; padding-right: unset; margin: auto;" class="col-5">
                                        <div class="metric-label d-inline-block float-right font-weight-bold">
                                            <span style="font-size: 1.33em;" class="ml-1">{{ $weeklyReviewsPercentage }}%</span>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
