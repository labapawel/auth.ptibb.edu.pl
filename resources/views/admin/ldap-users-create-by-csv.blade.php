@extends('admin.layouts.app')

@section('title', __('lang.admin.add_user_by_csv_btn'))

@section('content')
<div class="admin-container">
	<div class="mb-6 flex justify-between items-center">
		<h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ __('lang.admin.add_user_by_csv_btn') }}</h2>
		<button type="button" onclick="window.location.href='/admin/ldap/users'" class="admin-btn-secondary">
			{{ __('lang.admin.back_to_panel') }}
		</button>
	</div>
	@if(session('error') || session('success') || $errors->any())
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
		<form action="{{ route('ldap.users.createByCsv') }}" method="POST" enctype="multipart/form-data">
			@csrf
			<div class="mb-4">
				<label for="csv_file" class="admin-form-label">CSV</label>
				<input type="file" id="csv_file" name="csv_file" accept=".csv" class="admin-form-input" required>
				<p class="text-xs text-gray-500 mt-1">Plik CSV powinien zawierać nagłówki: cn, givenname, sn, mail, uid, userpassword, groups (oddzielone przecinkami).</p>
				@error('csv_file')
					<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
				@enderror
			</div>
			<div class="mt-6">
				<button type="submit" class="admin-btn">
					{{ __('lang.admin.add_user_by_csv_btn') }}
				</button>
			</div>
		</form>
	</div>
</div>
@endsection
