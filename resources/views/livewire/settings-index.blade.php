<div class="mx-auto max-w-5xl space-y-8 p-8">
    <div class="space-y-2">
        <flux:heading size="xl">Settings</flux:heading>
        <flux:subheading>Manage registered package settings pages.</flux:subheading>
    </div>

    @if ($pages === [])
        <div class="rounded-3xl border border-dashed border-zinc-300 bg-white/70 p-8 text-sm text-zinc-500 shadow-xs dark:border-zinc-700 dark:bg-zinc-900/60 dark:text-zinc-400">
            No settings pages are currently registered.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($pages as $page)
                <a
                    href="{{ route('settings.pages.edit', ['pageName' => $page->name]) }}"
                    wire:navigate
                    class="block rounded-3xl border border-zinc-200 bg-white p-6 shadow-xs transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-xs font-medium uppercase tracking-[0.18em] text-zinc-400">
                                {{ $page->group ? \Illuminate\Support\Str::headline($page->group) : 'General' }}
                            </div>

                            @if ($page->icon)
                                <div class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $page->icon }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <flux:heading size="lg">{{ $page->label }}</flux:heading>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $page->name }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
