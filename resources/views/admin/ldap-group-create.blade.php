@extends('admin.layouts.app')

@section('title', 'Dodaj grupę (klasę) LDAP')

@section('content')
<div class="admin-container max-w-lg mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Dodaj nową grupę (klasę)</h2>
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    <form method="POST" action="{{ route('admin.ldap.groups.store') }}">
        @csrf
        <div class="mb-4">
            <label for="group_cn" class="block text-sm font-medium text-gray-700">Nazwa grupy (np. 2025a)</label>
            <input type="text" id="group_cn" name="group_cn" class="admin-form-input" required>
        </div>
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Opis (opcjonalnie)</label>
            <input type="text" id="description" name="description" class="admin-form-input">
        </div>
        <button type="submit" class="admin-btn">Utwórz grupę</button>
    </form>
</div>
@endsection
