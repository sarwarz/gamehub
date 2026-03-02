@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<script>window.location.href = "{{ route('settings.general') }}";</script>
@endsection
