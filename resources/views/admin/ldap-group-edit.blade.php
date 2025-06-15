@extends('admin.layouts.app')

@section('title', 'Edytuj grupę LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Edytuj grupę: {{ $group['cn'][0] }}</h2>
        <a href="{{ route('ldap.groups.show', $group['cn'][0]) }}" class="admin-btn-secondary">Anuluj</a>
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

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('ldap.groups.update', $group['cn'][0]) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informacje o grupie -->
            <div class="admin-card">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Informacje o grupie</h3>
                
                <div class="mb-4">
                    <label for="cn" class="admin-form-label">Nazwa grupy (CN)</label>
                    <input type="text" id="cn" name="cn" value="{{ $group['cn'][0] }}" class="admin-form-input bg-gray-100" readonly>
                    <p class="mt-1 text-xs text-gray-500">Nazwa grupy nie może być zmieniona</p>
                </div>

                <div class="mb-4">
                    <label for="description" class="admin-form-label">Opis</label>
                    <input type="text" id="description" name="description" value="{{ $group['description'][0] ?? '' }}" class="admin-form-input" placeholder="Wprowadź opis grupy...">
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">GID Number</label>
                    <input type="text" value="{{ $group['gidnumber'][0] }}" class="admin-form-input bg-gray-100" readonly>
                    <p class="mt-1 text-xs text-gray-500">GID number jest automatycznie przypisywany</p>
                </div>
            </div>

            <!-- Zarządzanie członkami -->
            <div class="admin-card">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Członkowie grupy</h3>
                
                <div class="mb-4">
                    <label for="user-search" class="admin-form-label">Wyszukaj użytkowników</label>
                    <input type="text" id="user-search" class="admin-form-input" placeholder="Wpisz nazwę użytkownika...">
                </div>

                <div class="mb-4">
                    <label for="users" class="admin-form-label">Przypisz do grupy</label>
                    <div id="user-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded p-2 bg-white">
                        @foreach($users as $user)
                            @php
                                $currentMembers = $group['memberuid'] ?? [];
                                $isChecked = in_array($user['uid'], $currentMembers);
                            @endphp
                            <label class="user-item flex items-center space-x-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="users[]" value="{{ $user['uid'] }}" class="form-checkbox text-blue-600" {{ $isChecked ? 'checked' : '' }}>
                                <span>{{ $user['cn'] }} <span class="text-xs text-gray-500">({{ $user['uid'] }})</span></span>
                            </label>
                        @endforeach
                    </div>
                    @error('users')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            <button type="submit" class="admin-btn">Zaktualizuj grupę</button>
            <a href="{{ route('ldap.groups.show', $group['cn'][0]) }}" class="admin-btn-secondary">Anuluj</a>
        </div>
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
