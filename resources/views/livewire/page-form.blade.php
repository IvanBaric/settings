<x-admin-ui::page class="admin-page-compact">
    <x-admin-ui::page-header
        :title="$page->label"
        :description="$page->group
            ? __('Postavke područja :group.', ['group' => __(\Illuminate\Support\Str::headline($page->group))])
            : __('Upravljajte vrijednostima odabranih postavki.')"
        icon="cog-6-tooth"
    >
        <x-slot:actions>
            <flux:button :href="route('settings.pages.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Sve postavke') }}
            </flux:button>
        </x-slot:actions>
    </x-admin-ui::page-header>

    <div class="grid min-w-0 gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
        <aside class="admin-panel h-fit p-2">
            <div class="px-3 pb-2 pt-3">
                <h2 class="admin-panel-title">{{ __('Postavke') }}</h2>
                <p class="admin-panel-description">{{ __('Registrirana područja postavki.') }}</p>
            </div>

            <flux:navlist aria-label="{{ __('Stranice postavki') }}">
                @foreach ($pages as $navPage)
                    <flux:navlist.item
                        :href="route('settings.pages.edit', ['pageName' => $navPage->name])"
                        :current="$navPage->name === $page->name"
                        wire:navigate
                    >
                        {{ $navPage->label }}
                    </flux:navlist.item>
                @endforeach
            </flux:navlist>
        </aside>

        <x-admin-ui::panel>
            <form wire:submit="save" wire:loading.class="admin-panel-content-loading" wire:target="save" class="relative space-y-8 p-6 sm:p-7">
                <x-admin-ui::loading-overlay target="save" :text="__('Spremanje...')" />

                @foreach ($page->fields() as $field)
                    @include($viewResolver->resolve($field->type), ['field' => $field])
                @endforeach

                @error('authorization')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror

                <div class="flex justify-end border-t border-zinc-100 pt-6 dark:border-zinc-800">
                    <x-admin-ui::submit-button target="save">{{ __('Spremi promjene') }}</x-admin-ui::submit-button>
                </div>
            </form>
        </x-admin-ui::panel>
    </div>
</x-admin-ui::page>
