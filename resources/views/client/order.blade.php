@extends('layouts.app')

@section('title', 'create')

@section('content')
    <div class="container text-center">
        <img class="img-thumbnail rounded-circle mb-2" src="/storage/{{ $office->avatar }}" alt="" width="100">
        <h1 class="h3 mb-3 font-weight-normal"><span class="badge badge-secondary">{{ $office->name }}</span></h1>
        <order-component :order="{{ json_encode($order) }}" :lang="{{ json_encode($lang) }}"
            :office="{{ json_encode($office) }}" :agent="{{ json_encode($agent) }}">
        </order-component>



    </div>
@endsection

@section('js')

@endsection

@section('css')

    <style>
        html,
        body {
            height: 100%;
        }



        body {

            align-items: center;
            padding-top: 16px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }


        .form-signin {
            width: 100%;

            padding: 15px;
            margin: auto;
        }

        .form-signin .checkbox {
            font-weight: 400;
        }

        .form-signin .form-control {
            position: relative;
            box-sizing: border-box;
            height: auto;
            padding: 10px;
            font-size: 16px;
        }

        .form-signin .form-control:focus {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .badge-secondary {
            color: #252525;
            background-color: #d3d3d3;
        }

    </style>
@endsection
