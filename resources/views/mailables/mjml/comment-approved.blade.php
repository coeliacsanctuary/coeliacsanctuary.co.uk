@php
    use App\Models\Comments\Comment;use App\Models\EatingOut\EateryReview;
    use App\Support\Helpers;
    use Illuminate\Support\Str;
    use Money\Money;
    /** @var Comment $comment */
@endphp

@extends('mailables.mjml.layout')

@push('head')
    <mj-style>
        @media only screen and (min-width: 480px) { *[class~=prod-image] { padding-right: 10px !important;} }
    </mj-style>
@endpush

@section('header')
    <h2 style="padding: 5px 0">Comment Approved!</h2>
@endsection

@section('main-content')
    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">Hey {{ $comment->name }}</mj-text>
            <mj-text mj-class="inner">
                Your recent comment on our {{ strtolower(class_basename($comment->commentable_type)) }} <strong>{{ $comment->commentable->title }}</strong> on Coeliac Sanctuary
                has been approved!
                Thank you for sharing your thoughts with the wider community and helping others eat out safely!
            </mj-text>
            <mj-text mj-class="inner" padding-top="10px">
                Thanks, Alison - Coeliac Sanctuary
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">
                <h2 style="text-align: center">Your Comment</h2>
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section mj-class="light-section" padding="0 25px 0 25px">
        <mj-column padding="25px">
            <mj-text mj-class="inner">
                {{ $comment->comment }}
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section>
        <mj-column>
            <mj-button href="{{ $comment->commentable->link }}">
                Read more of our {{ class_basename($comment->commentable_type) }}, and see your comment and others!
            </mj-button>
        </mj-column>
    </mj-section>
@endsection
