@php
    unset($relatedTitle);
@endphp

@extends('mailables.mjml.shop.layout')

@push('head')
    <mj-style>
        @media only screen and (min-width: 480px) { *[class~=prod-image] { padding-right: 10px !important;} }
    </mj-style>
@endpush

@section('header')
    New Coeliac Sanctuary Order
@endsection
