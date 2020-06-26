@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('auth.verify your email address') }}</div>
                    {{ app()->getLocale() }}
                    <br>
                    {{ auth()->user()->id }}
                    <div class="card-body">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                {{ __('auth.fresh verification link email') }}
                            </div>
                        @endif
                        {{ __('auth.check your email verification link') }}
                        {{ __('auth.if you did not receive the email') }},
                        <form class="d-inline" method="POST"
                              action="{{ route('verification.resend', [
                                    'locale' => app()->getLocale(),
                                    'id' => auth()->user()->id,
                                    ]) }}">
                            @csrf
                            <button type="submit"
                                    class="btn btn-link p-0 m-0 align-baseline">{{ __('auth.click here to request another') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
