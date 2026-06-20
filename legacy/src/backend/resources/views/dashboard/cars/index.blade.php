@extends('layouts.dashboard')

@section('content')
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <div class="bg-info text-white text-center py-3 rounded-0 d-flex flex-column w-100 text-uppercase _hover-item">
                {{ auth()->user()->company->cars_counter }}
                <span>{{ __('dashboard.cars.cars') }}</span>
            </div>
            <div
                    class="bg-danger text-white text-center py-3 rounded-0 d-flex flex-column w-100 text-uppercase _hover-item">
                {{ $company->disconnected_cars_counter }}
                <span>{{ __('dashboard.cars.disconnected') }}</span>
            </div>
            <div
                    class="bg-success text-white text-center py-3 rounded-0 d-flex flex-column w-100 text-uppercase _hover-item">
                {{ $company->connected_cars_counter }}
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
                        data-confirm="{{ __('dashboard.general.forms.yes') }}">
                    <span class="text">{{ __('dashboard.general.CRUD.delete') }} {{ __('dashboard.cars.cars') }}</span>
                </button>
            </form>

            <a href="{{ route('cars.create', app()->getLocale()) }}" class="btn btn-success">
                <span class="text">{{ __('dashboard.general.CRUD.create') }} {{ __('dashboard.cars.car') }}</span>
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
                        <form action="{{ route('cars.index', app()->getLocale()) }}">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <a href="{{ route('cars.index', app()->getLocale()) }}"
                                       class="btn btn-primary" type="submit">
                                        <i class="fas fa-redo fa-sm"></i>
                                    </a>
                                </div>
                                <input type="text" class="form-control bg-light"
                                       name="search"
                                       @isset($search)
                                       value="{{ $search }}"
                                       @else
                                       value="{{ old('search') }}"
                                       placeholder="{{ __('dashboard.general.search') }}"
                                        @endisset>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search fa-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>{{ __('dashboard.cars.table.image') }}</th>
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
