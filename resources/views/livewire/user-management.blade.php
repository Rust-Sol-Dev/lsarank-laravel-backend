<div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">Users</h4>
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover table-nowrap table-centered m-0">
                            <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Keyword count</th>
                                <th>Active</th>
                                <th>Free/Premium</th>
                                <th>Enable/Disable</th>
                                <th>Delete</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <h5 class="m-0 fw-normal">{{ $user->name }}</h5>
                                    </td>
                                    <td>
                                        {{ $user->email }}
                                    </td>
                                    <td>
                                        {{ $user->created_at }}
                                    </td>
                                    <td>
                                        {{ $user->keywordCount }}
                                    </td>
                                    <td>
                                        @if($user->active) Yes @else No @endif
                                    </td>
                                    <td>
                                        <div class="p-10 space-y-2" x-data="{toggle: @if($user->paid) true @else false @endif}">
                                            <button
                                                class="transition ease-in-out duration-300 w-12 bg-gray-200 rounded-full focus:outline-none"
                                                :class="{ 'bg-green-300': toggle }"
                                                wire:click="togglePaid({{ $user->id }})"
                                            >
                                                <div
                                                    class="transition ease-in-out duration-300 rounded-full h-6 w-6 bg-white shadow"
                                                    :class="{ 'transform translate-x-full ': toggle }"
                                                ></div>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="p-10 space-y-2" x-data="{toggle: @if($user->active) true @else false @endif}">
                                            <button
                                                class="transition ease-in-out duration-300 w-12 bg-gray-200 rounded-full focus:outline-none"
                                                :class="{ 'bg-green-300': toggle }"
                                                wire:click="toggleActive({{ $user->id }})"
                                            >
                                                <div
                                                    class="transition ease-in-out duration-300 rounded-full h-6 w-6 bg-white shadow"
                                                    :class="{ 'transform translate-x-full ': toggle }"
                                                ></div>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <button wire:click="deleteUser({{ $user->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
