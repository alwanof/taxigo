@extends('templates.default.layout')
@section('title', $data['home'][0]['caption'])
@section('content')

    @include('templates.default.components.hero',['data'=>$data['home'][0]['hero']])
    <!-- Section Start -->
    <section class="section overflow-hidden">
        @include('templates.default.components.whatWeDo3C',['data'=>$data['home'][0]['whatWeDo3C']])
        @include('templates.default.components.galleryA',['data'=>$data['home'][0]['galleryA']])
        @include('templates.default.components.leftStory2C',['data'=>$data['home'][0]['leftStory2C']])
        @include('templates.default.components.rightStory2C',['data'=>$data['home'][0]['rightStory2C']])

    </section>
    <!--end section-->

    @include('templates.default.components.formA',['data'=>$data['home'][0]['formA']])

    @include('templates.default.components.solidStory2C',['data'=>$data['home'][0]['solidStory2C']])




@stop
