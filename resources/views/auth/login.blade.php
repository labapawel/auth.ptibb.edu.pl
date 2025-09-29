<x-app-layout>
    <div class="flex justify-center pt-10 items-center">
        <div class=" md:w-1/2  bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden flex flex-col md:flex-row">
            <!-- Left Section - Image/Branding -->
            <div class="hidden md:block md:w-1/2 bg-indigo-600 py-10 px-10">
                <div class="text-white h-full flex flex-col justify-between">
                    <div>
                        <h2 class="text-3xl font-bold mb-6">{{ __('lang.logowanie.welcome_back') }}</h2>
                        <div class="mb-6 text-white">
                            {{ __('lang.logowanie.first_login_instruction') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section - Login Form -->
            <div class="md:w-1/2 py-8 px-6 md:px-10">
                <div class="flex justify-center md:justify-start mb-8">
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                </div>

                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">{{ __('lang.logowanie.login_account') }}</h3>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- username Address -->
                    <div class="mb-4">
                        <x-input-label for="username" :value="__('lang.logowanie.email')" class="text-gray-700 dark:text-gray-300" />
                        <x-text-input id="username" 
                                      class="block mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 
                                             focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white" 
                                      type="email" 
                                      name="username" 
                                      :value="old('username')" 
                                      required 
                                      autofocus 
                                      autocomplete="username" />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <x-input-label for="password" :value="__('lang.logowanie.password')" class="text-gray-700 dark:text-gray-300" />
                        <x-text-input id="password" 
                                      class="block mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 
                                             focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white" 
                                      type="password" 
                                      name="password" 
                                      required 
                                      autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" 
                                   type="checkbox" 
                                   class="rounded dark:bg-gray-700 border-gray-300 dark:border-gray-600 
                                          text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600" 
                                   name="remember">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('lang.logowanie.remember') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        @if (Route::has('password.request'))
                            <a class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline" 
                               href="{{ route('password.request') }}">
                                {{ __('lang.logowanie.forgot') }}
                            </a>
                        @endif

            
                    </div>

                    <x-primary-button class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-700">
                        {{ __('lang.logowanie.login') }}
                    </x-primary-button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>