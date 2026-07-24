<div class="min-h-screen flex items-center justify-center px-6">
    <div class="max-w-md w-full">
        <h1 class="text-3xl font-semibold text-brand-red text-center mb-8">Famille</h1>

        <form wire:submit="login" class="bg-white rounded-2xl shadow-sm p-8 space-y-5">
            <div>
                <label class="block text-base font-medium text-neutral-600 mb-2">Email</label>
                <input type="email" wire:model="email" class="w-full rounded-xl border-2 border-neutral-200 text-base py-3 px-4 focus:border-brand-orange focus:ring-brand-orange" autofocus>
                @error('email') <p class="text-brand-red text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-base font-medium text-neutral-600 mb-2">Mot de passe</label>
                <input type="password" wire:model="password" class="w-full rounded-xl border-2 border-neutral-200 text-base py-3 px-4 focus:border-brand-orange focus:ring-brand-orange">
                @error('password') <p class="text-brand-red text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="cursor-pointer w-full bg-brand-orange text-white font-medium text-base rounded-xl py-3.5">
                Se connecter
            </button>
        </form>

        <p class="text-center text-base text-neutral-500 mt-5">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="text-brand-orange font-medium">S'inscrire</a>
        </p>
    </div>
</div>
