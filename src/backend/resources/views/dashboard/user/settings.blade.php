@extends('layouts.dashboard')

@section('content')
    <div class="row mt-5">
        <div class="col-6 offset-3">
            <form method="POST"
                  action="{{ route('user.profile.settings.update', app()->getLocale()) }}">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-12 bg-white shadow p-5">
                        @foreach($checkboxes as $setting)
                            <div class="row text-center">
                                <div class="col-10">
                                    {{ $setting->relationSetting->translate }}
                                </div>
                                <div class="col-2">
                                    <label class="switch">
                                        <input type="checkbox"
                                               class="success"
                                               name="{{ $setting->relationSetting->name }}"
                                               value="{{ $setting->value }}"
                                               @if($setting->value) checked @endif
                                        >
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        @endforeach

                        @foreach($selects as $setting)
                            <div class="row text-center form-group">
                                <div class="col-10">
                                    {{ $setting->relationSetting->translate }}
                                </div>
                                <div class="col-2">
                                    <select class="form-control form-control-user"
                                            name="{{ $setting->relationSetting->name }}">
                                        @foreach($setting->relationSetting->value_variants as $variant)
                                            <option value="{{ $variant }}"
                                                    @if($setting->value === $variant) selected @endif>
                                                {{ $variant }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach

                        <div class="text-center">
                            <button type="submit" class="px-5 mt-3 btn btn-success">
                                {{ __('dashboard.general.forms.submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // name="map-image-loaded-car"
        var settingsCheckboxes = document.querySelectorAll('input[type="checkbox"]');

        settingsCheckboxes.forEach(input => {

            input.onclick = function (event) {
                // input.checked = !input.checked
                if (input.checked) {

                }
                console.log(input.checked)
            }
        })

    </script>
@endsection