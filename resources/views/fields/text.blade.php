<div class="space-y-3">
    @if ($field->isRequired())
        <flux:input wire:model="values.{{ $field->name }}" :label="$field->label" data-required />
    @else
        <flux:input wire:model="values.{{ $field->name }}" :label="$field->label" />
    @endif

    @if ($field->description)
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $field->description }}</p>
    @endif

    @error('values.'.$field->name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
