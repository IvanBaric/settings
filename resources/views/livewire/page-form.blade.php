<div class="mx-auto max-w-6xl p-8">
    <div class="flex items-start gap-10 max-md:flex-col">
        <div class="w-full space-y-4 md:w-[260px]">
            <div class="space-y-2">
                <flux:heading size="lg">Settings</flux:heading>
                <flux:subheading>Registered settings pages.</flux:subheading>
            </div>

            <flux:navlist aria-label="Settings pages">
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
        </div>

        <flux:separator class="md:hidden" />

        <div class="flex-1 space-y-6">
            <div class="space-y-1">
                <flux:heading size="xl">{{ $page->label }}</flux:heading>
                <flux:subheading>
                    {{ $page->group ? \Illuminate\Support\Str::headline($page->group).' settings' : 'Manage registered settings values.' }}
                </flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-8">
                @foreach ($page->fields() as $field)
                    @include($viewResolver->resolve($field->type), ['field' => $field])
                @endforeach

                @error('authorization')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">Spremi</flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
