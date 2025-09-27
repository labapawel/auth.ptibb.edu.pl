@extends('admin.layouts.app')

@section('title', 'Podgląd grupy LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Podgląd grupy: {{ $group['cn'][0] }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('ldap.groups.edit', $group['cn'][0]) }}"><button class="admin-btn">Edytuj grupę</button></a>
            <a href="{{ route('ldap.groups.index') }}"><button  class="admin-btn-secondary">Powrót do listy</button></a>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informacje o grupie -->
        <div class="admin-card">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Informacje o grupie</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nazwa grupy (CN)</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 p-2 rounded">
                        {{ $group['cn'][0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Opis</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 p-2 rounded">
                        {{ $group['description'][0] ?? 'Brak opisu' }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">GID Number</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 p-2 rounded">
                        {{ $group['gidnumber'][0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Liczba członków</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 p-2 rounded">
                        {{ count($users) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Lista członków -->
        <div class="admin-card">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Członkowie grupy</h3>
            @if(empty($users))
                <p class="text-sm text-gray-500">Brak członków w tej grupie.</p>
            @else
                <div class="space-y-2">
                    @foreach($users as $user)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Akcje -->
    <div class="mt-6 flex gap-4">
        <form method="POST" action="{{ route('ldap.groups.destroy', $group['cn'][0]) }}" class="inline-block">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200" 
                    onclick="return confirm('Czy na pewno chcesz usunąć tę grupę? Ta akcja jest nieodwracalna.')">
                Usuń grupę
            </button>
        </form>
    </div>
</div>
@endsection
