<x-app-layout>
    <div class="row">
        <div class="col-3">
            <livewire:logo-uploader/>
            <livewire:report-generator/>
            <hr>
        </div>
        <div class="vl col-9">
            <div class="row">
                @foreach($reportPeriodsArray as $keyword => $keywordPeriodArray)
                    <div class="col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">Keyword: {{ $keyword }}</h4>
                                <ul class="list-group">
                                    @foreach($keywordPeriodArray as $periodItem)
                                        <li class="list-group-item">
                                            <a href="{{ route('download', ['hash' => \App\Helpers\BladeHelper::hideUrlParams($periodItem)]) }}">
                                                <i class="mdi mdi-download me-1"></i> {{ $periodItem['title'] }}: {{ \Carbon\Carbon::parse($periodItem['start_date'])->format('m/d/y') }} <b>-</b> {{ \Carbon\Carbon::parse($periodItem['end_date'])->format('m/d/y') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                @endforeach
            </div>
        </div>
    </div> <!-- end col -->
</x-app-layout>
