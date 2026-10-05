@extends('layouts.app')
@section('title', 'Change Password')

@section('nav')
@php $role = auth()->user()->role; @endphp
@php
  $navLink = 'flex items-center gap-2.5 px-5 py-2.5 text-sm border-l-[3px] text-gray-500 border-transparent hover:bg-[#f5f0ff] hover:text-[#3C3489]';
@endphp

@if($role === 'supervisor')
<a href="{{ route('supervisor.dashboard') }}" class="{{ $navLink }}"><i class="ti ti-home text-lg w-5"></i> Home</a>
<a href="{{ route('supervisor.pipeline') }}" class="{{ $navLink }}"><i class="ti ti-route text-lg w-5"></i> Pipeline</a>
<a href="{{ route('supervisor.tasks') }}" class="{{ $navLink }}"><i class="ti ti-checkbox text-lg w-5"></i> Tasks</a>
<a href="{{ route('supervisor.appraisal.history') }}" class="{{ $navLink }}"><i class="ti ti-history text-lg w-5"></i> History</a>
@elseif($role === 'hr')
<a href="{{ route('hr.dashboard') }}" class="{{ $navLink }}"><i class="ti ti-home text-lg w-5"></i> Home</a>
<a href="{{ route('hr.users') }}" class="{{ $navLink }}"><i class="ti ti-users text-lg w-5"></i> Users</a>
<a href="{{ route('hr.assignments') }}" class="{{ $navLink }}"><i class="ti ti-sitemap text-lg w-5"></i> Assignments</a>
<a href="{{ route('hr.cycles') }}" class="{{ $navLink }}"><i class="ti ti-calendar text-lg w-5"></i> Cycles</a>
<a href="{{ route('hr.tasks') }}" class="{{ $navLink }}"><i class="ti ti-checkbox text-lg w-5"></i> Tasks</a>
<a href="{{ route('hr.appraisal.history') }}" class="{{ $navLink }}"><i class="ti ti-history text-lg w-5"></i> History</a>
@else
<a href="{{ route('staff.dashboard') }}" class="{{ $navLink }}"><i class="ti ti-home text-lg w-5"></i> Home</a>
<a href="{{ route('staff.tasks') }}" class="{{ $navLink }}"><i class="ti ti-checkbox text-lg w-5"></i> Tasks</a>
<a href="{{ route('staff.appraisal') }}" class="{{ $navLink }}"><i class="ti ti-file-description text-lg w-5"></i> Appraisal</a>
<a href="{{ route('staff.appraisal.history') }}" class="{{ $navLink }}"><i class="ti ti-history text-lg w-5"></i> History</a>
@endif
@endsection

@section('content')
<div class="mb-1 text-2xl font-bold text-gray-900">Change password</div>
<div class="text-sm text-gray-500 mb-5">Confirm your current password, then choose a new one.</div>

<div class="bg-white border border-[#e0daf5] rounded-xl p-5 max-w-md">
  {{-- Success and validation errors are shown by the layout banners above --}}
  <form method="POST" action="{{ route('password.change.update') }}" class="space-y-4">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Current password</label>
      <input type="password" name="current_password" required autocomplete="current-password"
        class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] focus:ring-2 focus:ring-[#eeedfe]">
    </div>

    <div>
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">New password</label>
      <input type="password" name="password" required minlength="8" autocomplete="new-password"
        class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] focus:ring-2 focus:ring-[#eeedfe]">
      <div class="text-xs text-gray-400 mt-1">At least 8 characters.</div>
    </div>

    <div>
      <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Confirm new password</label>
      <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
        class="w-full px-3 py-2.5 border border-[#e0daf5] rounded-lg text-sm focus:outline-none focus:border-[#7F77DD] focus:ring-2 focus:ring-[#eeedfe]">
    </div>

    <button type="submit" class="bg-[#3C3489] text-white px-5 py-2.5 rounded-lg font-medium text-sm hover:bg-[#26215C] transition">
      Update password
    </button>
  </form>
</div>
@endsection
