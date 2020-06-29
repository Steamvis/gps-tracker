@extends('layouts.dashboard')

@section('content')

    <h1 class="h3 mb-3 text-gray-800">
        test
    </h1>

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
                <button type="button" class="btn btn-danger" id="__delete-many-btn" disabled
                        onclick="callConfirmModal(this)"
                        data-title="{{ __('dashboard.general.forms.confirm') }}"
                        data-cancel="{{ __('dashboard.general.forms.cancel') }}"
                        data-confirm="{{ __('dashboard.general.forms.ok') }}">
                    <span class="text">{{ __('dashboard.cars.CRUD.delete') }}</span>
                </button>
            </form>

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
                        <th style="width: 1%;"></th>
                        <th>{{ __('dashboard.cars.table.name') }}</th>
                        <th>{{ __('dashboard.cars.table.brand_name') }}</th>
                        <th>{{ __('dashboard.cars.table.location') }}</th>
                        <th>{{ __('dashboard.cars.table.driver') }}</th>
                        <th>{{ __('dashboard.cars.table.manager') }}</th>
                        <th>{{ __('dashboard.cars.table.gov_number') }}</th>
                        <th>{{ __('dashboard.cars.table.vin_number') }}</th>
                        <th>{{ __('dashboard.cars.table.year') }}</th>
                        <th>{{ __('dashboard.general.forms.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cars as $car)
                        <tr>
                            <td>
                                <input class="__action" form="delete-many-objects-form" type="checkbox"
                                       name="action[]" value="{{ $car->id }}">
                            </td>
                            <td>{{ $car->name }}</td>
                            <td>{{ $car->brand->name }}</td>
                            <td>location</td>
                            <td>driver</td>
                            <td>manager</td>
                            <td>{{ $car->gov_number }}</td>
                            <td>{{ $car->vin_number }}</td>
                            <td>{{ $car->year }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button"
                                            id="dropdownItemActions" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                        {{ __('dashboard.general.forms.actions') }}
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownItemActions">
                                        <form method="POST"
                                              action="{{ route('cars.destroy', [
                                                    'car' => $car,
                                                    'locale' => app()->getLocale(),
                                                ]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    name="id"
                                                    value="{{ $car->id }}"
                                                    onclick="callConfirmModal(this)"
                                                    data-title="{{ __('dashboard.general.forms.confirm') }}"
                                                    data-cancel="{{ __('dashboard.general.forms.cancel') }}"
                                                    data-confirm="{{ __('dashboard.general.forms.ok') }}"
                                                    class="dropdown-item">
                                                {{ __('dashboard.cars.CRUD.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                {{ $cars->links('dashboard.pagination') }}
            </div>
        </div>
    </div>


@endsection

@section('scripts')
    <script src="{{ mix('admin/js/app_common.js') }}"></script>
    <script src="{{ mix('admin/js/sweetalert.js') }}"></script>
    @include('sweetalert::alert', ['cdn' => ''])
@endsection



