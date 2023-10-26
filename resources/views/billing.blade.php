<x-app-layout>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <h2>Billing plans</h2>
            </div>
            <div class="row" style="text-align: right">
                <h1 style="font-weight: 900; font-size: 42px;">You are on the {{ $billingPlan }} plan.</h1>
            </div>
            <div class="row">
                <div class="table-responsive">
                    <table class="table table-bordered table-centered m-0 text-center justify-center">
                        <thead class="table-light">
                        <tr style="border-width: 0px">
                            <th style="background-color: unset;" class="text-center justify-center billing-title-left">
                                <div style="margin-left: 31%">
                                    <div style="text-align: left!important;">
                                        Please contact us if you:
                                        <ul style="list-style-type:disc;">
                                            <li>Want to pay monthly (25% more)</li>
                                            <li>Want agency pricing</li>
                                        </ul>
                                    </div>
                                </div>
                            </th>
                            <th style="border: 1px groove #262626;">
                                <div class="row">
                                    <h3>FREEMIUM</h3>
                                </div>
                                <div class="row">
                                    <h5>FREE</h5>
                                </div>
                            </th>
                            <th style="border: 1px groove #262626;">
                                <div class="row">
                                    <h3>SMB CITY</h3>
                                </div>
                                <div class="row">
                                    <h5>$500/year</h5>
                                </div>
                            </th>
                            <th style="border: 1px groove #262626;">
                                <div class="row">
                                    <h3>SMB STATE</h3>
                                </div>
                                <div class="row">
                                    <h5>$2500/year</h5>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody style="border-top: 0 !important;">
                        <tr style="border: 0 !important;">
                            <td class="text-center justify-center billing-title-left">
                                Tracks Keyword every hour
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                        </tr>
                        <tr style="border: 0 !important;">
                            <td class="text-center justify-center billing-title-left">
                                Tracks Keyword every 5min
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/red-x2.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                        </tr>
                        <tr style="border: 0 !important;">
                            <td class="text-center justify-center billing-title-left">
                                Geo Grids
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/red-x2.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                        </tr>
                        <tr style="border: 0 !important;">
                            <td class="text-center justify-center billing-title-left">
                                Tracks Top 5 Competitors with most weekly reviews
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/red-x2.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                        </tr>
                        <tr style="border: 0 !important;">
                            <td class="text-center justify-center billing-title-left">
                                Tracks Top 5 Competitors with most weekly ranking increase
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/red-x2.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                        </tr>
                        <tr style="border: 0 !important;">
                            <td class="text-center justify-center billing-title-left">
                                New competitor in your LSA Market alert
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/red-x2.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <img height="50px" width="50px" src="{{ \Illuminate\Support\Facades\Vite::asset('resources/images/approve1.png') }}">
                            </td>
                        </tr>
                        <tr style="border: 0 !important;">
                            <td class="text-center justify-center billing-title-left">

                            </td>
                            <td align="center" style="border: 1px groove #262626;">

                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <a href="https://my.freshbooks.com/#/checkout/e4769c49fae04571937cdf77817745d1">
                                    <button type="button" class="btn1 btn-lg"><span style="margin: 15px">Upgrade plan</span></button>
                                </a>
                            </td>
                            <td align="center" style="border: 1px groove #262626;">
                                <a href="https://my.freshbooks.com/#/checkout/6783bd7c7f7e4b59a7616901782f2b08">
                                    <button type="button" class="btn1 btn-lg"><span style="margin: 15px">Upgrade plan</span></button>
                                </a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- end card-body -->
    </div> <!-- end card-->
</x-app-layout>
