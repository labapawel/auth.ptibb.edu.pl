@extends('admin.layouts.app')

@section('title', 'Jednostki Organizacyjne LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Jednostki Organizacyjne LDAP</h2>
        <a href="{{ route('ldap.organizational-units.create') }}" class="admin-btn">Dodaj jednostkę organizacyjną</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @if($organizationalUnits===[])
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded" role="alert">
            <p>Brak jednostek organizacyjnych LDAP. Proszę dodać nową jednostkę organizacyjną.</p>
        </div>
    @else
        <div class="admin-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead class="admin-table-header">
                        <tr>
                            <th class="admin-table-head">OU</th>
                            <th class="admin-table-head">Opis</th>
                            <th class="admin-table-head">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($organizationalUnits as $unit)
                        <tr>
                            <td class="admin-table-cell">{{ $unit->ou }}</td>
                            <td class="admin-table-cell">{{ $unit->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('ldap.organizational-units.edit', $unit->ou) }}" class="text-green-600 hover:text-green-900 mr-3">Edytuj</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
