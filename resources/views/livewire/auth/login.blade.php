<div class="min-h-screen flex items-center justify-center px-6">
    <div class="max-w-sm w-full">
        <h1 class="text-2xl font-semibold text-brand-red text-center mb-6">Famille</h1>

        <form wire:submit="login" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-neutral-600 mb-1">Email</label>
                <input type="email" wire:model="email" class="w-full rounded-lg border-neutral-200 text-sm" autofocus>
                @error('email') <p class="text-brand-red text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-600 mb-1">Mot de passe</label>
                <input type="password" wire:model="password" class="w-full rounded-lg border-neutral-200 text-sm">
                @error('password') <p class="text-brand-red text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-brand-orange text-white font-medium rounded-lg py-2">
                Se connecter
            </button>
        </form>

        <p class="text-center text-sm text-neutral-500 mt-4">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="text-brand-orange font-medium">S'inscrire</a>
        </p>
    </div>
</div>
