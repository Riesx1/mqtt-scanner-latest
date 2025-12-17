<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10"
                                type="password"
                                name="password"
                                required autocomplete="new-password"
                                oninput="checkPasswordStrength()" />

                <button type="button" onclick="togglePassword('password')" class="absolute top-1/2 -translate-y-1/2 right-0 pr-3 flex items-center">
                    <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>

            <!-- Password Strength Indicator -->
            <div id="password-strength" class="mt-2 hidden">
                <div class="flex items-center gap-2 mb-1">
                    <div class="flex-1 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                        <div id="strength-bar" class="h-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <span id="strength-text" class="text-xs font-medium"></span>
                </div>
            </div>

            <!-- Password Requirements -->
            <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                <p class="font-medium mb-1">Password must contain:</p>
                <ul class="space-y-0.5 ml-4">
                    <li id="req-length" class="flex items-center gap-1">
                        <span class="text-gray-500">○</span> At least 8 characters
                    </li>
                    <li id="req-uppercase" class="flex items-center gap-1">
                        <span class="text-gray-500">○</span> One uppercase letter
                    </li>
                    <li id="req-lowercase" class="flex items-center gap-1">
                        <span class="text-gray-500">○</span> One lowercase letter
                    </li>
                    <li id="req-number" class="flex items-center gap-1">
                        <span class="text-gray-500">○</span> One number
                    </li>
                    <li id="req-special" class="flex items-center gap-1">
                        <span class="text-gray-500">○</span> One special character (!@#$%^&*)
                    </li>
                </ul>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <div class="relative">
                <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

                <button type="button" onclick="togglePassword('password_confirmation')" class="absolute top-1/2 -translate-y-1/2 right-0 pr-3 flex items-center">
                    <svg class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <script>
            function togglePassword(fieldId) {
                const passwordInput = document.getElementById(fieldId);
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
            }

            function checkPasswordStrength() {
                const password = document.getElementById('password').value;
                const strengthIndicator = document.getElementById('password-strength');
                const strengthBar = document.getElementById('strength-bar');
                const strengthText = document.getElementById('strength-text');

                // Show indicator when typing
                if (password.length > 0) {
                    strengthIndicator.classList.remove('hidden');
                } else {
                    strengthIndicator.classList.add('hidden');
                    return;
                }

                // Check requirements
                const requirements = {
                    length: password.length >= 8,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
                };

                // Update requirement indicators
                updateRequirement('req-length', requirements.length);
                updateRequirement('req-uppercase', requirements.uppercase);
                updateRequirement('req-lowercase', requirements.lowercase);
                updateRequirement('req-number', requirements.number);
                updateRequirement('req-special', requirements.special);

                // Calculate strength
                const metRequirements = Object.values(requirements).filter(Boolean).length;
                let strength = 0;
                let strengthLabel = '';
                let strengthColor = '';

                if (metRequirements === 5) {
                    strength = 100;
                    strengthLabel = 'Strong';
                    strengthColor = 'bg-green-500';
                } else if (metRequirements >= 3) {
                    strength = 60;
                    strengthLabel = 'Medium';
                    strengthColor = 'bg-yellow-500';
                } else {
                    strength = 30;
                    strengthLabel = 'Weak';
                    strengthColor = 'bg-red-500';
                }

                // Update strength bar
                strengthBar.style.width = strength + '%';
                strengthBar.className = 'h-full transition-all duration-300 ' + strengthColor;
                strengthText.textContent = strengthLabel;
                strengthText.className = 'text-xs font-medium ' +
                    (strengthLabel === 'Strong' ? 'text-green-400' :
                     strengthLabel === 'Medium' ? 'text-yellow-400' : 'text-red-400');
            }

            function updateRequirement(id, met) {
                const element = document.getElementById(id);
                const icon = element.querySelector('span');

                if (met) {
                    element.classList.remove('text-gray-500');
                    element.classList.add('text-green-400');
                    icon.textContent = '✓';
                    icon.classList.remove('text-gray-500');
                    icon.classList.add('text-green-400');
                } else {
                    element.classList.remove('text-green-400');
                    element.classList.add('text-gray-500');
                    icon.textContent = '○';
                    icon.classList.remove('text-green-400');
                    icon.classList.add('text-gray-500');
                }
            }
        </script>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
