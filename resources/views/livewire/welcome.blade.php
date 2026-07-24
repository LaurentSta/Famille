<div class="min-h-screen flex items-center justify-center px-6 pb-16">
    <div class="max-w-md w-full text-center space-y-8">
        <div>
            <h1 class="text-3xl font-semibold text-brand-red">Famille</h1>
            <p class="text-sm text-neutral-400 mt-1">{{ auth()->user()->family->name }}</p>
        </div>

        <div class="grid gap-4">
            <a href="{{ route('planning') }}" class="block bg-white rounded-xl shadow-sm p-6 text-left border-l-4 border-brand-orange">
                <span class="block text-lg font-medium">Planning</span>
                <span class="block text-sm text-neutral-500">Organiser les repas de la semaine</span>
            </a>
            <a href="{{ route('courses') }}" class="block bg-white rounded-xl shadow-sm p-6 text-left border-l-4 border-brand-mustard">
                <span class="block text-lg font-medium">Courses</span>
                <span class="block text-sm text-neutral-500">La liste générée à partir du planning</span>
            </a>
        </div>
    </div>
</div>
