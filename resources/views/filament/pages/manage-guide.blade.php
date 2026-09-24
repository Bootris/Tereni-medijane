<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3">
            <x-filament::button type="submit">
                Sačuvaj
            </x-filament::button>
            <x-filament::button tag="a" color="gray" :href="route('tereni.guide')" target="_blank">
                Pogledaj na sajtu
            </x-filament::button>
            <x-filament::button type="button" color="gray" wire:click="restoreDefaults"
                wire:confirm="Zameniti tekst u formi podrazumevanim? Ništa se ne menja dok ne klikneš „Sačuvaj”.">
                Vrati podrazumevani tekst
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
