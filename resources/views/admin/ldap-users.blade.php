@extends('admin.layouts.app')

@section('title', 'Użytkownicy LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Użytkownicy LDAP</h2>
        <div class="space-x-2">
            <button onclick="window.location.href='{{ route('admin.dashboard') }}'" type="button" class="admin-btn-secondary">
                Powrót do panelu
            </button>
            <button onclick="window.location.href='{{ route('ldap.users.create') }}'" type="button" class="admin-btn">
                Dodaj użytkownika
            </button>

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
    <div class="admin-card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="search" class="admin-form-label">Szukaj użytkowników</label>
                <input type="text" id="search" name="search" class="admin-form-input" placeholder="Szukaj po imieniu, nazwisku, emailu lub loginie">
                <p class="mt-1 text-xs text-gray-500">Wpisz tekst aby filtrować użytkowników. Naciśnij Escape aby wyczyścić.</p>
            </div>
        </div>
    </div>
    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead class="admin-table-header">
                    <tr>
                        <th class="admin-table-head">CN</th>
                        <th class="admin-table-head">Imię</th>
                        <th class="admin-table-head">Nazwisko</th>
                        <th class="admin-table-head">Email</th>
                        <th class="admin-table-head">Login (UID)</th>
                        <th class="admin-table-head">Akcje</th>
                    </tr>
                </thead>
                <tbody id="user-table" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php if(isset($users) && is_iterable($users) && count($users)): ?>
                    <?php foreach($users as $user): ?>
                    <tr class="user-row">
                        <td class="admin-table-cell"><?php echo htmlspecialchars($user->cn ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="admin-table-cell"><?php echo htmlspecialchars($user->givenname ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="admin-table-cell"><?php echo htmlspecialchars($user->sn ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="admin-table-cell"><?php echo htmlspecialchars($user->mail ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="admin-table-cell"><?php echo htmlspecialchars($user->uid ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('ldap.users.edit', $user->uid) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edytuj</a>
                            <form action="{{ route('ldap.users.delete', $user->cn) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 mr-3" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?');">Usuń</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">Brak użytkowników LDAP.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-between">
                <div class="text-sm text-gray-500 dark:text-gray-400" id="results-counter">
                    Wyświetlanie <?php echo isset($users) ? count($users) : 0; ?> z <?php echo isset($users) ? count($users) : 0; ?> wyników
                </div>
                <div class="flex space-x-2">
                    <button class="admin-btn-secondary text-sm py-1 px-3">Poprzednia</button>
                    <button class="admin-btn-secondary text-sm py-1 px-3">Następna</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        const userRows = document.querySelectorAll('#user-table .user-row');
        const emptyRow = document.querySelector('#user-table tr td[colspan]');

        function performSearch() {
            const query = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            userRows.forEach(function(row) {
                // Get text content from specific cells, excluding action buttons
                const cells = row.querySelectorAll('td:not(:last-child)');
                let searchableText = '';
                
                cells.forEach(function(cell) {
                    searchableText += ' ' + cell.textContent.toLowerCase();
                });

                const isVisible = query === '' || searchableText.includes(query);
                row.style.display = isVisible ? '' : 'none';
                
                if (isVisible) {
                    visibleCount++;
                }
            });

            // Update counter
            updateResultsCounter(visibleCount);

            // Show/hide empty state
            if (emptyRow) {
                emptyRow.parentElement.style.display = (visibleCount === 0 && query !== '') ? '' : 'none';
            }
        }

        function updateResultsCounter(visibleCount) {
            const counterElement = document.getElementById('results-counter');
            if (counterElement) {
                const totalUsers = userRows.length;
                if (searchInput.value.trim() === '') {
                    counterElement.textContent = `Wyświetlanie ${totalUsers} z ${totalUsers} wyników`;
                } else {
                    counterElement.textContent = `Wyświetlanie ${visibleCount} z ${totalUsers} wyników (filtrowane)`;
                }
            }
        }

        // Search on input
        searchInput.addEventListener('input', performSearch);

        // Clear search on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                performSearch();
                searchInput.blur();
            }
        });

        // Initial setup
        performSearch();
    });
</script>
@endsection
