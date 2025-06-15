@extends('admin.layouts.app')

@section('title', 'Dodaj grupę LDAP')

@section('content')
<div class="admin-container max-w-lg mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Dodaj nową grupę</h2>
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
        {{ dd($users) }}
    @endif
    <form method="POST" action="{{ route('ldap.groups.store') }}">
        @csrf
        <div class="mb-4">
            <label for="group_name" class="block text-sm font-medium text-gray-700">Nazwa grupy (np. klasa2025a)</label>
            <input type="text" id="group_name" name="group_name" class="admin-form-input" required>
        </div>
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Opis (opcjonalnie)</label>
            <input type="text" id="description" name="description" class="admin-form-input">
        </div>
        <div class="mb-4">
            <label for="user-search" class="block text-sm font-medium text-gray-700">Wyszukaj użytkowników</label>
            <input type="text" id="user-search" class="admin-form-input" placeholder="Wpisz nazwę użytkownika...">
        </div>
        <div class="mb-4">
            <label for="users" class="block text-sm font-medium text-gray-700">Wybierz użytkowników do przypisania</label>
            <div id="user-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded p-2 bg-white">
                @foreach($users as $user)
                    <label class="user-item flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="users[]" value="{{ $user->uid }}" class="form-checkbox text-blue-600">
                        <span>{{ $user->cn }} <span class="text-xs text-gray-500">({{ $user->uid }})</span></span>
                    </label>
                @endforeach
            </div>
        </div>
        <button type="submit" class="admin-btn">Utwórz grupę</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('user-search');
        const userItems = document.querySelectorAll('#user-list .user-item');
        
        // Function to filter users
        function filterUsers(query) {
            const lowerQuery = query.toLowerCase().trim();
            let visibleCount = 0;
            
            userItems.forEach(function(item) {
                const userName = item.textContent.toLowerCase();
                const isVisible = lowerQuery === '' || userName.includes(lowerQuery);
                item.style.display = isVisible ? 'flex' : 'none';
                if (isVisible) visibleCount++;
            });
            
            // Show/hide "no results" message
            updateNoResultsMessage(visibleCount === 0 && lowerQuery !== '');
        }
        
        // Function to show/hide no results message
        function updateNoResultsMessage(show) {
            let noResultsMsg = document.getElementById('no-results-message');
            
            if (show && !noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'no-results-message';
                noResultsMsg.className = 'text-center text-gray-500 py-4';
                noResultsMsg.textContent = 'Nie znaleziono użytkowników';
                document.getElementById('user-list').appendChild(noResultsMsg);
            } else if (!show && noResultsMsg) {
                noResultsMsg.remove();
            }
        }
        
        // Search input event listener
        searchInput.addEventListener('input', function(e) {
            filterUsers(e.target.value);
        });
        
        // Clear search when escape is pressed
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                e.target.value = '';
                filterUsers('');
                e.target.blur();
            }
        });
    });
</script>
@endsection
