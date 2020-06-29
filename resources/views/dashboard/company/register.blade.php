@extends('layouts.dashboard_without_company')

@section('content')
    <div class="card border-0 shadow-lg" style="margin-top: 10%;">
        <div class="card-body p-0">
            <div class="row">
                <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                <div class="col-lg-7">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">Create a company!</h1>
                        </div>
                        <form class="user"
                              action="{{ route('company_register_create', app()->getLocale()) }}"
                              method="POST">
                            @csrf
                            <div class="form-group row">
                                <div class="col-sm-12 mb-3 mb-sm-0">
                                    <input type="text" class="form-control form-control-user"
                                           id="title"
                                           name="title"
                                           placeholder="Title">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 mb-3 mb-sm-0">
                                    <select class="form-control border selectpicker"
                                            name="country_id"
                                            data-live-search="true"
                                            required
                                            title="{{ __('dashboard.general.forms.select country') }}">
                                        @foreach($countries as $country)
                                            <option
                                                value="{{ $country->id }}"
                                                data-content="<img
                                                class='mr-3'
                                                src='{{ $country->flag }}'
                                                alt='{{ $country->name }}'
                                                width='30px'
                                                >{{ $country->name }}">
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Register Account
                            </button>
                            <hr>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a class="small" href="forgot-password.html">Forgot Password?</a>
                        </div>
                        <div class="text-center">
                            <a class="small" href="login.html">Already have an account? Login!</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
