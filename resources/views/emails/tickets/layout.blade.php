@extends('emails.layout')

@section('footer-links')
    <a href="{{ url('/') }}">Home</a>
    <a href="{{ url('/my-tickets') }}">My Tickets</a>
    <a href="{{ url('/contact') }}">Contact Us</a>
@endsection
