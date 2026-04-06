<div class="space-y-3">
    <flux:switch wire:model="values.{{ $field->name }}" :label="$field->label" />

    @if ($field->description)
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $field->description }}</p>
    @endif

    @error('values.'.$field->name)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
