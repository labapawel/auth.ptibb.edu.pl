@extends('admin.layouts.app')

@section('title', 'Użytkownicy LDAP')

@section('content')
<div class="admin-container">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Użytkownicy LDAP</h2>
        <div class="space-x-2">
            <a href="{{ route('admin.dashboard') }}" class="admin-btn-secondary">Powrót do panelu</a>
            <a href="{{ route('ldap.users.create') }}" class="admin-btn">Dodaj użytkownika</a>

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
                <label for="search" class="admin-form-label">Szukaj</label>
                <input type="text" id="search" name="search" class="admin-form-input" placeholder="Imię, email...">
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
                <div class="text-sm text-gray-500 dark:text-gray-400">
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
    document.getElementById('search').addEventListener('input', function (e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('#user-table .user-row').forEach(function (row) {
            const rowText = row.textContent.toLowerCase();
            row.style.display = rowText.includes(query) ? '' : 'none';
        });
    });
</script>
@endsection
