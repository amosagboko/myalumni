@extends('layouts.elcom')

@section('title', 'Real-Time Election Results - ' . $election->title)

@section('content')
<div class="container-fluid py-4">
    @livewire('real-time-results', ['election' => $election])
</div>
@endsection 