<div class="min-h-screen">
    @if (! $configured)
        <div class="min-h-screen flex items-center justify-center px-6">
            <div class="bg-white rounded-2xl shadow-sm p-8 border-l-4 border-brand-orange text-left max-w-md">
                <h1 class="text-lg font-semibold mb-2">Assistant cuisine (IA)</h1>
                <p class="text-sm text-neutral-400">En attente de la clé API DeepSeek pour l'activer.</p>
            </div>
        </div>
    @else
        <div class="px-4 pt-6 pb-40 max-w-2xl w-full mx-auto space-y-4">
            @if (empty($messages))
                <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-brand-orange space-y-2">
                    <h1 class="text-lg font-semibold">Assistant cuisine</h1>
                    <p class="text-sm text-neutral-500">Je peux t'aider de deux façons :</p>
                    <ul class="text-sm text-neutral-500 space-y-1.5 list-disc list-inside">
                        <li>
                            <span class="font-medium text-neutral-700">Recettes</span> — décris un plat ou demande-moi une idée
                            (« une recette de gratin », « je ne sais pas quoi faire ce soir »…) : je te propose une recette
                            complète que tu peux ajouter en un clic à ta banque de recettes.
                        </li>
                        <li>
                            <span class="font-medium text-neutral-700">Ingrédients</span> — les ingrédients d'une recette que
                            tu ajoutes sont automatiquement créés dans ton catalogue (avec leur catégorie) s'ils n'existent
                            pas déjà.
                        </li>
                    </ul>
                </div>
            @endif

            @foreach ($messages as $index => $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm {{ $message['role'] === 'user' ? 'bg-brand-orange text-white' : 'bg-white shadow-sm' }}">
                        <p class="whitespace-pre-line">{{ $message['content'] }}</p>

                        @if ($message['dish'] ?? null)
                            <div class="mt-3 pt-3 border-t {{ $message['role'] === 'user' ? 'border-white/30' : 'border-neutral-100' }}">
                                <p class="font-semibold text-brand-brick">
                                    {{ config('emoji.dish_types.'.$message['dish']['type'], config('emoji.dish_type_default')) }}
                                    {{ $message['dish']['name'] }}
                                </p>
                                <ul class="list-disc list-inside text-xs text-neutral-500 mt-1">
                                    @foreach ($message['dish']['ingredients'] as $ingredient)
                                        <li>{{ is_array($ingredient) ? $ingredient['name'] : $ingredient }}</li>
                                    @endforeach
                                </ul>

                                @if ($message['added'])
                                    <p class="mt-2 text-xs text-brand-mustard font-medium">✓ Ajouté à tes plats</p>
                                @else
                                    <button
                                        type="button"
                                        wire:click="addDish({{ $index }})"
                                        class="cursor-pointer mt-2 text-xs bg-brand-mustard text-white font-medium rounded-full px-3 py-1.5"
                                    >
                                        Ajouter à mes plats
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <div wire:loading wire:target="send, suggestFromStock" class="flex justify-start">
                <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm bg-white shadow-sm flex items-center gap-2 text-neutral-400">
                    <span>L'assistant réfléchit</span>
                    <span class="flex gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-orange animate-bounce [animation-delay:-0.3s]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-orange animate-bounce [animation-delay:-0.15s]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-orange animate-bounce"></span>
                    </span>
                </div>
            </div>

            <div wire:key="scroll-{{ count($messages) }}" x-data x-init="$el.scrollIntoView({ block: 'end' })"></div>
        </div>

        <form wire:submit.prevent="send" class="fixed bottom-16 inset-x-0 bg-white border-t border-neutral-200 px-4 py-3 z-10">
            <div class="max-w-2xl mx-auto mb-2">
                <button
                    type="button"
                    wire:click="suggestFromStock"
                    wire:loading.attr="disabled"
                    class="cursor-pointer w-full rounded-full border-2 border-brand-mustard/40 bg-brand-mustard/10 text-brand-mustard font-medium text-sm px-4 py-2"
                >
                    🍽️ Je ne sais pas quoi manger !!
                </button>
            </div>
            <div class="max-w-2xl mx-auto flex gap-2">
                <input
                    type="text"
                    wire:model="input"
                    placeholder="Écris ton message…"
                    class="flex-1 rounded-full border-2 border-brand-orange/30 text-sm px-4 py-2.5 focus:border-brand-orange focus:ring-brand-orange"
                    wire:loading.attr="disabled"
                    autocomplete="off"
                >
                <button
                    type="submit"
                    class="cursor-pointer shrink-0 bg-brand-orange text-white rounded-full px-5 py-2.5 text-sm font-medium"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Envoyer</span>
                    <span wire:loading>…</span>
                </button>
            </div>
        </form>
    @endif
</div>
