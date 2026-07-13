<x-admin-ui::page class="admin-page-compact">
    <x-admin-ui::page-header
        :title="__('Postavke')"
        :description="__('Upravljajte registriranim postavkama sustava na jednom mjestu.')"
        icon="cog-6-tooth"
    />

    <x-admin-ui::panel>
        @if ($pages === [])
            <x-admin-ui::empty-state
                :title="__('Nema dostupnih postavki')"
                :description="__('Trenutno nije registrirana nijedna stranica postavki.')"
            >
                <x-slot:icon>
                    <flux:icon name="cog-6-tooth" class="size-5" />
                </x-slot:icon>
            </x-admin-ui::empty-state>
        @else
            <x-admin-ui::panel-header
                :title="__('Dostupne postavke')"
                :description="__('Odaberite područje koje želite urediti.')"
            />

            <div class="divide-y divide-zinc-100/80 dark:divide-zinc-800/80">
                @foreach ($pages as $page)
                    <a
                        href="{{ route('settings.pages.edit', ['pageName' => $page->name]) }}"
                        wire:navigate
                        class="group flex min-w-0 items-center gap-4 px-6 py-4 transition duration-150 hover:bg-zinc-50/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-accent/30 dark:hover:bg-zinc-900/60 sm:px-7"
                    >
                        <span class="admin-action-icon">
                            <flux:icon name="cog-6-tooth" class="size-4" />
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[15px] font-semibold text-zinc-950 dark:text-white">{{ $page->label }}</span>
                            <span class="mt-1 block text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $page->group ? __(\Illuminate\Support\Str::headline($page->group)) : __('Općenito') }}
                            </span>
                        </span>

                        <flux:icon name="chevron-right" class="size-4 shrink-0 text-zinc-300 transition group-hover:text-zinc-500 dark:text-zinc-600 dark:group-hover:text-zinc-400" />
                    </a>
                @endforeach
            </div>
        @endif
    </x-admin-ui::panel>
</x-admin-ui::page>
