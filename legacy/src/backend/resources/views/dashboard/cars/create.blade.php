@extends('layouts.dashboard')

@section('content')
    @include('dashboard.breadcrumbs')
    <div class="row">
        <div class="col-6 offset-3">
            <form
                enctype="multipart/form-data"
                @isset($car)
                action="{{ route('cars.update', ['locale' => app()->getLocale(), 'car' => $car]) }}"
                @else
                action="{{ route('cars.store', app()->getLocale()) }}"
                @endisset
                class="user"
                method="POST">
                @csrf
                @isset($car)
                    @method('PATCH')
                @endisset

                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-user
                           @error('name') border-danger" style="background-color: rgba(255,0,0,0.14)" @else" @enderror
                    required
                    name="name"
                    @isset($car) @php($value = $car->name) @else @php($value = old('name')) @endisset
                    value="{{ $value }}"
                    placeholder="{{ __('dashboard.cars.table.name') }}">
                </div>
                <div class="form-group">
                    <div class="custom-file" @isset($car) style="height: 250px" @endisset>
                        <input name='image' onchange="handleChange(this)" type="file" class="custom-file-input"
                               id="imageInput">
                        <div class="__image-file @isset($car) @else d-none @endisset">
                            @isset($car)
                                <img width="200px" style="object-fit: cover;" src="{{ $car->image }}">
                                <span data-title="{{ __('dashboard.general.forms.confirm') }}"
                                      data-cancel="{{ __('dashboard.general.forms.cancel') }}"
                                      data-confirm="{{ __('dashboard.general.forms.yes') }}"
                                      data-token="{{ csrf_token() }}"
                                      data-car="{{ $car->id }}"
                                      action="{{ route('images.destroy', [
                                            'locale' => app()->getLocale(),
                                        ]) }}">
                                &times;
                                </span>
                            @else
                                <img width="200px" style="object-fit: cover;">
                                <span>&times;</span>
                            @endisset
                        </div>
                        <label class="custom-file-label" for="imageInput"
                               data-translate="{{ __('dashboard.general.forms.choose file') }}...">
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-user
                            @error('vin_number') border-danger" style="background-color: rgba(255,0,0,0.14)" @else
                        " @enderror
                        name="vin_number"
                        @isset($car) @php($value = $car->vin_number) @else @php($value = old('vin_number')) @endisset
                        value="{{ $value }}"
                        placeholder="{{ __('dashboard.cars.table.vin_number') }}">
                </div>
                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-user @error('gov_number') border-danger"
                           style="background-color: rgba(255,0,0,0.14)" @else" @enderror
                    name="gov_number"
                    @isset($car) @php($value = $car->gov_number) @else @php($value = old('gov_number')) @endisset
                    value="{{ $value }}"
                    placeholder="{{ __('dashboard.cars.table.gov_number') }}">
                </div>
                <div class="form-group">
                    <textarea name="description"
                              @isset($car) @php($value = $car->description) @else @php($value = old('description')) @endisset
                              value="{{ $value }}"
                              rows="5"
                              class="form-control form-control-user rounded-0
                                @error('description') border-danger" style="background-color: rgba(255,0,0,0.14)" @else
                        " @enderror
                        placeholder="{{ __('dashboard.cars.table.description') }}"></textarea>
                </div>
                <div class="form-group">
                    @isset($car) @php($color = $car->color) @else @php($color = old('color')) @endif
                    <input type="color" name="color" value="{{ $color }}">
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-6">
                            <select name="year"
                                    class="form-control border selectpicker"
                                    data-live-search="true"
                                    title="{{ __('dashboard.general.forms.select year') }}">
                                @for($year = \Carbon\Carbon::now()->year; $year >= 1960; $year--);
                                <option
                                    @isset($car)
                                    @if($year === (int)$car->year)
                                    selected
                                    @endif
                                    @else
                                    @if($year === (int)old('year'))
                                    selected
                                    @endif
                                    @endisset>
                                    {{ $year }}
                                </option>
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
                                                    @isset($car)
                                                    @if($brand->id === (int)$car->brand->id)
                                                    selected
                                                    @endif
                                                    @else
                                                    @if($brand->id === (int)old('mark_id'))
                                                    selected
                                                    @endif
                                                    @endisset
                                                    data-content="{{ Str::upper($brand->name) }}">
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @include('partials.errors')
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-user btn-block mb-5">
                    @isset($car) {{ __('dashboard.general.CRUD.edit') }} @else{{ __('dashboard.general.CRUD.create') }} @endisset
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ mix('admin/js/app_common.js') }}"></script>
    <script src="{{ mix('admin/js/upload-file.js') }}"></script>
@endsection
