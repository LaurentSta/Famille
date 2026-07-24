<div class="min-h-screen flex items-center justify-center px-6 py-12">
    <div class="max-w-md w-full">
        <h1 class="text-3xl font-semibold text-brand-red text-center">Famille</h1>
        <p class="text-center text-sm text-neutral-400 mt-1 mb-8">Planning des repas et liste de courses, à organiser en famille.</p>

        <form wire:submit="register" class="bg-white rounded-2xl shadow-sm p-8 space-y-5">
            <div>
                <label class="block text-base font-medium text-neutral-600 mb-2">Prénom</label>
                <input type="text" wire:model="name" class="w-full rounded-xl border-2 border-neutral-200 text-base py-3 px-4 focus:border-brand-orange focus:ring-brand-orange" autofocus>
                @error('name') <p class="text-brand-red text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-base font-medium text-neutral-600 mb-2">Email</label>
                <input type="email" wire:model="email" class="w-full rounded-xl border-2 border-neutral-200 text-base py-3 px-4 focus:border-brand-orange focus:ring-brand-orange">
                @error('email') <p class="text-brand-red text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-base font-medium text-neutral-600 mb-2">Mot de passe</label>
                <input type="password" wire:model="password" class="w-full rounded-xl border-2 border-neutral-200 text-base py-3 px-4 focus:border-brand-orange focus:ring-brand-orange">
                @error('password') <p class="text-brand-red text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-base font-medium text-neutral-600 mb-2">Confirmer le mot de passe</label>
                <input type="password" wire:model="password_confirmation" class="w-full rounded-xl border-2 border-neutral-200 text-base py-3 px-4 focus:border-brand-orange focus:ring-brand-orange">
            </div>

            <div>
                <label class="block text-base font-medium text-neutral-600 mb-2">Code famille</label>
                <input type="text" wire:model="family_code" class="w-full rounded-xl border-2 border-neutral-200 text-base py-3 px-4 focus:border-brand-orange focus:ring-brand-orange">
                <p class="text-sm text-neutral-400 mt-1.5">Crée un nouveau code, ou entre celui de ta famille pour la rejoindre.</p>
                @error('family_code') <p class="text-brand-red text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="cursor-pointer w-full bg-brand-orange text-white font-medium text-base rounded-xl py-3.5">
                S'inscrire
            </button>
        </form>

        <p class="text-center text-base text-neutral-500 mt-5">
            Déjà un compte ?
            <a href="{{ route('login') }}" class="text-brand-orange font-medium">Se connecter</a>
        </p>
    </div>
</div>
