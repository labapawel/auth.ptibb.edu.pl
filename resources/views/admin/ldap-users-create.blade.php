@extends('admin.layouts.app')

@section('title', __('lang.admin.add_ldap_user'))

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('lang.admin.add_ldap_user') }}</h2>
        <button type="button" onclick="window.location.href='/admin/ldap/users'" class="admin-btn-secondary">
            {{ __('lang.admin.back_to_panel') }}
        </button>
    </div>
    
    @if(
        session('error') || session('success') || $errors->any()
    )
        <div class="mb-6">
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2 rounded" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-2 rounded" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2 rounded" role="alert">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif
    <div class="admin-card">
        <form action="/admin/ldap/users"method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="cn" class="admin-form-label">CN (Common Name)</label>
                    <input type="text" id="cn" name="cn" value="{{ old('cn') }}" class="admin-form-input" required>
                    @error('cn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="givenname" class="admin-form-label">{{ __('lang.admin.first_name') }}</label>
                    <input type="text" id="givenname" name="givenname" value="{{ old('givenname') }}" class="admin-form-input" required>
                    @error('givenname')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sn" class="admin-form-label">{{ __('lang.admin.last_name') }}</label>
                    <input type="text" id="sn" name="sn" value="{{ old('sn') }}" class="admin-form-input" required>
                    @error('sn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="mail" class="admin-form-label">{{ __('lang.admin.email') }}</label>
                    <input type="email" id="mail" name="mail" value="{{ old('mail') }}" class="admin-form-input" required>
                    @error('mail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="uid" class="admin-form-label">{{ __('lang.admin.login_uid') }}</label>
                    <input type="text" id="uid" name="uid" value="{{ old('uid') }}" class="admin-form-input" required>
                    @error('uid')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="userpassword" class="admin-form-label">{{ __('lang.admin.password') }}</label>
                    <input type="password" id="userpassword" name="userpassword" class="admin-form-input" required>
                    @error('userpassword')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="groups" class="admin-form-label">{{ __('lang.admin.groups') }}</label>
                    @if(empty($groups) || count($groups) === 0)
                        <p class="text-sm text-gray-500">Brak dostępnych grup.</p>
                    @else
                        <div class="mb-2">
                            <input type="text" id="group-search" class="admin-form-input" placeholder="{{ __('lang.admin.search_groups_placeholder') }}">
                        </div>
                        <div id="group-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-32 overflow-y-auto border rounded p-2 bg-gray-50">
                            @foreach($groups as $group)
                                <div class="group-item flex items-center mb-2">
                                    <input type="checkbox" id="groups_{{ $group['cn'] }}" name="groups[]" value="{{ $group['cn'] }}" 
                                           class="form-checkbox text-blue-600" 
                                           {{ (is_array(old('groups')) && in_array($group['cn'], old('groups'))) ? 'checked' : '' }}>
                                    <label for="groups_{{ $group['cn'] }}" class="ml-2 text-sm text-gray-700">
                                        {{ $group['cn'] }} @if($group['description']) - {{ $group['description'] }} @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <!-- Ukryte pola, ustawiane automatycznie w kontrolerze -->
                <input type="hidden" id="homedirectory" name="homedirectory" value="">
                <input type="hidden" id="loginshell" name="loginshell" value="">
            </div>            <div class="mt-6">
                <button type="submit" class="admin-btn">
                    {{ __('lang.admin.add_user_btn') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('group-search');
        const groupItems = document.querySelectorAll('#group-list .group-item');
        
        if (searchInput && groupItems.length > 0) {
            // Function to filter groups
            function filterGroups(query) {
                const lowerQuery = query.toLowerCase().trim();
                let visibleCount = 0;
                
                groupItems.forEach(function(item) {
                    const groupName = item.textContent.toLowerCase();
                    const isVisible = lowerQuery === '' || groupName.includes(lowerQuery);
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
                    noResultsMsg.textContent = 'Nie znaleziono grup';
                    document.getElementById('group-list').appendChild(noResultsMsg);
                } else if (!show && noResultsMsg) {
                    noResultsMsg.remove();
                }
            }
            
            // Search input event listener
            searchInput.addEventListener('input', function(e) {
                filterGroups(e.target.value);
            });
            
            // Clear search when escape is pressed
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.target.value = '';
                    filterGroups('');
                    e.target.blur();
                }
            });
        }
    });
</script>
@endsection
