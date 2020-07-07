@extends('layouts.dashboard')

@section('content')

    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <div
                class="bg-info text-white text-center py-3 rounded-0 d-flex flex-column w-100 text-uppercase _hover-item">
                {{ auth()->user()->company->cars_counter }}
                <span>{{ __('dashboard.cars.cars') }}</span>
            </div>
            <div
                class="bg-danger text-white text-center py-3 rounded-0 d-flex flex-column w-100 text-uppercase _hover-item">
                {{ $carsDisconnectedCounter }}
                <span>{{ __('dashboard.cars.disconnected') }}</span>
            </div>
            <div
                class="bg-success text-white text-center py-3 rounded-0 d-flex flex-column w-100 text-uppercase _hover-item">
                {{ $carsConnectedCounter }}
                <span>{{ __('dashboard.cars.connected') }}</span>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <form
                id="delete-many-objects-form"
                method="POST"
                action="{{ action('Cars\CarsController@destroyMany', [
                                'locale' => app()->getLocale(),
                        ]) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="action[]" value="">
                <button type="button" class="btn btn-danger" id="__delete-many-btn" disabled
                        onclick="return callConfirmModal(this)"
                        data-title="{{ __('dashboard.general.forms.confirm') }}"
                        data-cancel="{{ __('dashboard.general.forms.cancel') }}"
                        data-confirm="{{ __('dashboard.general.forms.ok') }}">
                    <span class="text">{{ __('dashboard.cars.CRUD.delete') }}</span>
                </button>
            </form>

            <script>
                function callConfirmModal(button) {
                    let form = button.parentNode,
                        title = button.getAttribute('data-title'),
                        confirm = button.getAttribute('data-confirm'),
                        cancel = button.getAttribute('data-cancel');
                    Swal.fire({
                        title: title,
                        icon: 'warning',
                        showCancelButton: true,
                        focusCancel: true,
                        confirmButtonColor: '#d33',
                        cancelButtonText: cancel,
                        confirmButtonText: confirm,
                    }).then(function (request) {
                        if (request.value) {
                            let actionValues = document.querySelector('input[name="action[]"]')
                            actionValues.value = getCarIDs()

                            form.submit()
                        }
                    });
                }

                function getCarIDs() {
                    let carIDs = [];
                    checkedTableElements.forEach(item => {
                        carIDs.push(item.getAttribute('data-car-id'))
                    })

                    return carIDs;
                }
            </script>

            <a href="{{ route('cars.create', app()->getLocale()) }}" class="btn btn-success">
                <span class="text">{{ __('dashboard.cars.CRUD.create') }}</span>
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div class="mb-2 py-1 mx-2 d-flex justify-content-between">
                    <div class="btn-group">
                        <button onclick="checkAll(this)"
                                class="btn btn-outline-primary">
                            <span class="text">{{ __('dashboard.general.forms.select all') }}</span>
                        </button>
                        <button onclick="unCheckAll(this)"
                                class="btn btn-outline-danger">
                            <span class="text">{{ __('dashboard.general.forms.unselect all') }}</span>
                        </button>
                    </div>


                    <div>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light"
                                   placeholder="{{ __('dashboard.general.search') }}"
                                   aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>{{ __('dashboard.cars.table.name') }}</th>
                        <th>{{ __('dashboard.cars.table.brand_name') }}</th>
                        <th>{{ __('dashboard.cars.table.api_key') }}</th>
                        <th>{{ __('dashboard.cars.table.gov_number') }}</th>
                        <th>{{ __('dashboard.cars.table.vin_number') }}</th>
                        <th>{{ __('dashboard.cars.table.year') }}</th>
                        <th>{{ __('dashboard.cars.table.color') }}</th>
                        <th>{{ __('dashboard.general.forms.actions') }}</th>
                    </tr>
                    </thead>
                    @include('dashboard.cars.index_table', ['cars' => $cars])
                </table>
            </div>
        </div>
        @yield('pagination')
    </div>
@endsection

@section('scripts')
    <script src="{{ mix('admin/js/app_common.js') }}"></script>
@endsection
