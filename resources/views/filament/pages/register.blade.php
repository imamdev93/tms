<x-filament::page>
    <form wire:submit.prevent="register">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Register
        </x-filament::button>
    </form>
</x-filament::page>
