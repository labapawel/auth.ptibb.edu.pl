

<x-app-layout>
    <div class="flex h-full items-center bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 py-10 px-4">
            @auth
                    <div >
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">{{ __('auth.your_account_overview') }}</h3>
                        @php
                            $user = Auth::user();
                            $roles = collect();
                            if ($user) {
                                if (method_exists($user, 'roles')) {
                                    $roles = collect($user->roles)->pluck('name')->filter();
                                }
                                if ($roles->isEmpty() && method_exists($user, 'getRoleNames')) {
                                    $roles = collect($user->getRoleNames());
                                }
                            }
                        @endphp
                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ __('auth.your_roles') }}</h4>
                            @if($roles->isNotEmpty())
                                <ul class="space-y-1">
                                    @foreach($roles as $role)
                                        <li class="text-sm text-gray-700 dark:text-gray-300">{{ __($role) }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('auth.no_roles_assigned') }}</p>
                            @endif
                        </div>
                        <div class="mb-4">
                            @if($user->isTaskPermission())
                                <div class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded px-3 py-2 mb-2 text-xs">{{ __('auth.task_management_access') }}</div>
                            @endif
                            @if($user->isVPNclient())
                                <div class="bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded px-3 py-2 mb-2 text-xs">{{ __('auth.vpn_client_access') }}</div>
                            @endif
                            @if($user->isEmailPermission())
                                <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded px-3 py-2 text-xs">{{ __('auth.email_management_access') }}</div>
                            @endif

                        </div>
                    </div>

                    @if (Session::has('success'))
                        <div class="fixed right-2 bottom-2 rounded-l-xl rounded-t-xl bg-blue-500 text-white text-sm font-bold px-4 py-3" role="alert">
                            <div class="flex items-start">
                                <svg class="fill-current w-4 h-4 mr-2 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"/></svg>
                                <div>
                                    <div class="font-semibold mb-2">{{ __('auth.security_notice') }}</div>
                                    <ul class="space-y-1 text-sm">
                                        <li>{{ __('auth.remember_logout_public_computers') }}</li>
                                        <li>{{ __('auth.never_share_password_anyone') }}</li>
                                        <li>{{ __('auth.unauthorized_access_change_password') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
            @else
            <div class="flex flex-1 items-center justify-center min-h-[60vh] w-full">
                <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden flex flex-col md:flex-row">
                    <!-- Left Section - Branding/Graphic -->
                    <div class="hidden md:flex md:w-1/2 bg-indigo-600 py-10 px-10 flex-col justify-center">
                        <div>
                            <h2 class="text-3xl font-bold text-white mb-6">{{ __('auth.welcome') }}</h2>
                            <div class="mb-6 text-indigo-100">
                                {{ __('auth.log_in_to_access_dashboard') }}
                            </div>
                        </div>
                    </div>
                    <!-- Right Section - Login Prompt -->
                    <div class="md:w-1/2 py-10 px-6 md:px-10 flex flex-col justify-center">
                        <div class="flex justify-center md:justify-start mb-8">
                            <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">{{ __('auth.please_log_in') }}</h3>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition ease-in-out duration-150">
                            {{ __('auth.log_in') }}
                        </a>
                    </div>
                </div>
            </div>
            @endauth
        </div>
</x-app-layout>
