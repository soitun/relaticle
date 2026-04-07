<x-filament-panels::page.simple>
    <div class="space-y-6">
        <div class="space-y-3">
            @foreach ($emails as $index => $email)
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="email"
                        wire:model.blur="emails.{{ $index }}"
                        placeholder="colleague@company.com"
                    />
                </x-filament::input.wrapper>
            @endforeach

            <button
                type="button"
                wire:click="addEmailField"
                class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300"
            >
                + Add more
            </button>
        </div>

        <div class="flex flex-col gap-3">
            <x-filament::button
                wire:click="sendInvites"
                class="w-full"
            >
                Send invites
            </x-filament::button>

            <button
                type="button"
                wire:click="skip"
                class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 text-center"
            >
                Skip for now
            </button>
        </div>
    </div>
</x-filament-panels::page.simple>
