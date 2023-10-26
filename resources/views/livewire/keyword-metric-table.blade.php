<div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Lsa ranking of keyword: {{ $keyword->original_keyword }} near {{ str_replace('+', ' ', $keyword->location) }} on date {{ Carbon\Carbon::parse($selectedDate)->format('Y-m-d') }}</h4>
                    <div class="table-responsive"  wire:poll.300000ms>
                        <table class="table table-borderless table-hover table-nowrap table-centered m-0">
                            <thead class="table-light">
                            <tr>
                                <th>Company List</th>
                                @foreach($businessEntityMapping as $businessEntityData)
                                    @if ($userPreference)
                                        @if ($loop->first)
                                            <th>
                                                <div>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="preference_{{$businessEntityData['id']}}" checked disabled>
                                                        {{ $businessEntityData['name'] }}
                                                    </div>
                                                </div>
                                            </th>
                                        @else
                                            <th>
                                                <div>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="preference_{{$businessEntityData['id']}}" disabled>
                                                        {{ $businessEntityData['name'] }}
                                                    </div>
                                                </div>
                                            </th>
                                        @endif
                                    @else
                                        <th>
                                            <div>
                                                <div class="form-check">
                                                    <input type="checkbox" onclick="confirm('Are you sure you want to associate your account with this business? Later changes are possible only over support.') || event.stopImmediatePropagation();event.preventDefault();" wire:click="toggleUserPreference('{{ $businessEntityData['id'] }}')" class="form-check-input" id="preference_{{$businessEntityData['id']}}">
                                                    {{ $businessEntityData['name'] }}
                                                </div>
                                            </div>
                                        </th>
                                    @endif
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $row)
                                <tr>
                                    @foreach($row as $item)
                                        @if ($loop->first)
                                            <td>
                                                {{ \App\Services\ViewHelper::displayTime($item)}}
                                            </td>
                                        @else
                                            <td>
                                                {{ $item }}
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-2">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div wire:loading>
        <div class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-500 opacity-75 flex flex-col items-center justify-center">
            <div class="flex justify-center items-center">
                <div
                    class="animate-spin rounded-full h-32 w-32 border-b-2 border-white-900"
                ></div>
            </div>
        </div>
    </div>
</div>

