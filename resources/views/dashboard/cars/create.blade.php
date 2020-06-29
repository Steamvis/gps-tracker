@extends('layouts.dashboard')


{{--'driver_id'   => 'nullable|integer',--}}
{{--'manager_id'  => 'nullable|integer',--}}

@section('content')
    <div class="row">
        <div class="col-6 offset-3">
            <form
                action="{{ route('cars.store', app()->getLocale()) }}"
                class="user"
                method="POST">
                @csrf

                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-user"
                           required
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="{{ __('dashboard.cars.table.name') }}">
                </div>
                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-user"
                           name="vin_number"
                           value="{{ old('vin_number') }}"
                           placeholder="{{ __('dashboard.cars.table.vin_number') }}">
                </div>
                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-user"
                           name="gov_number"
                           value="{{ old('gov_number') }}"
                           placeholder="{{ __('dashboard.cars.table.gov_number') }}">
                </div>
                <div class="form-group">
                    <textarea name="description"
                              value="{{ old('description') }}"
                              rows="5"
                              class="form-control form-control-user rounded-0"
                              placeholder="{{ __('dashboard.cars.table.description') }}"></textarea>
                </div>
                <div class="form-group">
                    <input type="color" name="color" value="{{ old('color') }}">
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <select name="year"
                                    class="form-control border selectpicker"
                                    data-live-search="true"
                                    title="{{ __('dashboard.general.forms.select year') }}">
                                @for($year = \Carbon\Carbon::now()->year; $year >= 1960; $year--);
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-6">
                            <select name="mark_id"
                                    class="form-control border selectpicker"
                                    data-live-search="true"
                                    title="{{ __('dashboard.general.forms.select brand') }}">
                                @foreach($countries as $country)
                                    <optgroup label="{{ $country->name }}">
                                        @foreach($country->brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                    data-content="{{ Str::upper($brand->name) }}">
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @if($errors->any())
                            @foreach($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        @endif
                    </div>
                </div>


                <button type="submit" class="btn btn-primary btn-user btn-block">
                    {{ __('dashboard.cars.CRUD.create') }}
                </button>
            </form>
        </div>
    </div>
@endsection
