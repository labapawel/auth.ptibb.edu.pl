<x-app-layout>
    <div class="flex flex-col items-center">
        <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden flex flex-col md:flex-row">
            <!-- Left Section - Image/Branding -->
            <div class="hidden md:block md:w-1/2 bg-indigo-600 py-10 px-10">
                <div class="text-white h-full flex flex-col justify-between">
                    <div>
                        <h2 class="text-3xl font-bold mb-6">{{ __('lang.forgot_password.reset_your_password') }}</h2>
                        <div class="mb-6 text-white">
                            {{ __('lang.forgot_password.forgot_password_message') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section - Reset Form -->
            <div class="md:w-1/2 py-8 px-6 md:px-10">
                <div class="flex justify-center md:justify-start mb-8">
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                </div>

                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">{{ __('lang.forgot_password.password_recovery') }}</h3>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-4">
                        <x-input-label for="email" :value="__('lang.forgot_password.email')" class="text-gray-700 dark:text-gray-300" />
                        <x-text-input id="email" 
                                      class="block mt-1 w-full rounded-lg border-gray-300 dark:border-gray-600 
                                             focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white" 
                                      type="email" 
                                      name="email" 
                                      :value="old('email')" 
                                      required 
                                      autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <a class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline" 
                           href="{{ route('login') }}">
                            {{ __('lang.forgot_password.back_to_login') }}
                        </a>
                    </div>

                    <x-primary-button class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-700">
                        {{ __('lang.forgot_password.email_reset_link') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>