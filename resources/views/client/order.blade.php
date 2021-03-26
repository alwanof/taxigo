@extends('layouts.front')

@section('title', 'create')
@section('bodyClass', 'd-flex flex-column h-100')
@section('content')
    <main class="flex-shrink-0">
        <div class="container pt-2">
            <div class="row">
                <div class="col">
                    <img src="/images/logo-sm.png" class="mx-auto d-block" height="100"
                        alt="{{ config('app.name', 'Project0') }}">
                    <!-- Logo area-->
                    <div class="row mt-3">
                        <div class="col-3">
                            <img src="/storage/{{ $office->avatar }}" class="mx-auto d-block" width="93" alt="logo">
                        </div>
                        <div class="col-9 border-bottom p-2 px-3">
                            <h4>{{ $office->name }}</h4>
                            <a href="tel:{{ $office->settings['phone'] }}" class="btn btn-sm btn-success">
                                <i class="fas fa-phone-alt"></i>
                                {{ __('app.Call') }}
                            </a>

                        </div>
                    </div>
                    <div class="mt-3">
                        <order-component :parse="{{ json_encode($parseKeys) }}" :order="{{ json_encode($order) }}"
                            :lang="{{ json_encode($lang) }}" :office="{{ json_encode($office) }}"
                            :agent="{{ json_encode($agent) }}">
                        </order-component>
                    </div>

                </div>
            </div>
        </div>
    </main>

@endsection

@section('js')

@endsection

@section('css')

    <style>
        /* Custom page CSS
              -------------------------------------------------- */
        /* Not required for template or sticky footer method. */

        .container {
            width: auto;
            max-width: 680px;
            padding: 0 15px;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .form-check-input:checked {
            background-color: #fbb921;
            border-color: #fbb921;
        }

        .form-check .form-check-input {
            float: left;
            margin-left: 0em;
        }

        .btn-primary {
            color: #fff;
            background-color: #fbb921;
            border-color: #fbb921;
        }

        .vl {
            border-left: 1px solid #ddd;
            height: 50%;
            margin: 0 50%;
        }

        .checked {
            color: orange;
        }

    </style>
@endsection
