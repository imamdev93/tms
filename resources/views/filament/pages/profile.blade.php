<x-filament::page>
    <form wire:submit.prevent="save">
         <div class="mt-10 p-10 flex justify-end">
            <x-filament::button type="submit">
                Update Profil
            </x-filament::button>
        </div>
        <br>
        {{ $this->form }}
    </form>
</x-filament::page>