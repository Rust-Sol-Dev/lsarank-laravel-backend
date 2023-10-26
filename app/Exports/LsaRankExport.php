<?php

namespace App\Exports;


use App\Http\Requests\ReportDownloadRequest;
use App\Repositories\KeywordMetricRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

HeadingRowFormatter::default('none');

class LsaRankExport implements FromArray, WithHeadingRow, WithMapping, ShouldAutoSize
{
    /**
     * @var KeywordMetricRepository
     */
    public $repository;

    /**
     * @var array
     */
    public $data;

    /**
     * LsaRankExport constructor.
     * @param Request $request
     * @param KeywordMetricRepository $repository
     */
    public function __construct(ReportDownloadRequest $request, KeywordMetricRepository $repository)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $params = $request->input();

//        $params['keyword_id'] = 2;
//        $params['start_date'] = '2023-01-22';
//        $params['end_date'] = '2023-01-25';

        $this->repository = $repository;

        $user = Auth::user();

        $businessEntitiesCollection = $repository->getEntitiesByPeriod($user->id, $params['keyword_id'], $params['start_date'], $params['end_date']);

        $resultArray = $repository->getRankingsByPeriod($businessEntitiesCollection, $params['start_date'], $params['end_date']);

        $headingArray = $businessEntitiesCollection->pluck('name')->toArray();

        array_unshift($headingArray, 'Company List');

        array_unshift($resultArray, $headingArray);

        $this->data = $resultArray;
    }

    /**
     * Return data array
     *
     * @return array
     */
    public function array(): array
    {
        return $this->data;
    }

    /**
     * Map row
     *
     * @param mixed $rowObject
     * @return array
     */
    public function map($rowObject): array
    {
        $rowArray = (array) $rowObject;
        $values = array_values($rowArray);
        return $values;
    }
}
