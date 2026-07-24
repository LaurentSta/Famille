<div class="pb-24 min-h-screen flex items-center justify-center px-6">
    <div class="max-w-md w-full text-center">
        @if ($configured)
            <p class="text-neutral-500">L'assistant arrive bientôt.</p>
        @else
            <div class="bg-white rounded-2xl shadow-sm p-8 border-l-4 border-brand-orange text-left">
                <h1 class="text-lg font-semibold mb-2">Assistant cuisine (IA)</h1>
                <p class="text-sm text-neutral-500">
                    Cet espace permettra bientôt de discuter avec une IA (DeepSeek) pour créer de nouveaux plats,
                    avec leurs ingrédients ajoutés automatiquement à ta banque de plats et à tes courses.
                </p>
                <p class="text-sm text-neutral-400 mt-4">
                    En attente de la clé API DeepSeek pour l'activer.
                </p>
            </div>
        @endif
    </div>
</div>
