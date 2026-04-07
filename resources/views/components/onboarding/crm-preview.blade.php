@props([
    'useCaseLabels' => [],
])

<div
    class="relative hidden h-full overflow-hidden bg-gray-50 lg:block"
    x-data
>
    {{-- CRM skeleton --}}
    <div class="pointer-events-none select-none p-5 opacity-50 blur-[0.5px]">
        <div class="flex h-full gap-0">
            {{-- Sidebar skeleton --}}
            <div class="w-44 shrink-0 space-y-2 rounded-l-lg border-r border-gray-200 bg-white p-3 shadow-xs">
                {{-- Workspace name (dynamic) --}}
                <div class="flex items-center gap-2 pb-1">
                    <div class="flex size-6 items-center justify-center rounded bg-gray-200 text-[10px] font-bold text-gray-500"
                         x-text="($wire.data?.name || 'W').charAt(0).toUpperCase()"></div>
                    <div class="truncate text-xs font-semibold text-gray-700"
                         x-text="$wire.data?.name || 'Workspace'"
                         x-cloak></div>
                </div>

                {{-- Search bar --}}
                <div class="flex items-center gap-1 rounded bg-gray-50 px-2 py-1">
                    <div class="size-3 rounded bg-gray-200"></div>
                    <div class="h-2.5 flex-1 rounded bg-gray-100"></div>
                </div>

                {{-- Nav items --}}
                <div class="space-y-0.5 pt-1">
                    @foreach (['ri-notification-line', 'ri-checkbox-circle-line', 'ri-file-copy-line', 'ri-mail-line', 'ri-chat-3-line', 'ri-calendar-line'] as $icon)
                        <div class="flex items-center gap-2 rounded px-2 py-1">
                            <x-filament::icon :icon="$icon" class="size-3.5 text-gray-300" />
                            <div class="h-2.5 rounded bg-gray-100" style="width: {{ [72, 56, 64, 48, 60, 68][($loop->index)] }}%"></div>
                        </div>
                    @endforeach
                </div>

                {{-- Separator + CRM section --}}
                <div class="border-t border-gray-100 pt-2">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2 rounded bg-primary-50 px-2 py-1">
                            <x-filament::icon icon="ri-user-line" class="size-3.5 text-primary-500" />
                            <div class="h-2.5 w-14 rounded bg-primary-100"></div>
                        </div>
                        <div class="flex items-center gap-2 rounded px-2 py-1">
                            <x-filament::icon icon="ri-building-line" class="size-3.5 text-gray-300" />
                            <div class="h-2.5 w-16 rounded bg-gray-100"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main content skeleton --}}
            <div class="flex-1 space-y-2 rounded-r-lg bg-white p-3 shadow-xs">
                {{-- Header with People tab --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="ri-user-line" class="size-4 text-primary-500" />
                        <span class="text-xs font-medium text-gray-500">People</span>
                    </div>
                    <div class="flex gap-1">
                        <div class="h-5 w-12 rounded bg-gray-100"></div>
                        <div class="size-5 rounded bg-gray-100"></div>
                    </div>
                </div>

                {{-- Filter bar --}}
                <div class="flex items-center gap-2">
                    <div class="h-5 w-16 rounded bg-gray-100"></div>
                    <div class="h-5 w-14 rounded bg-gray-100"></div>
                </div>

                {{-- Table rows --}}
                <div class="space-y-1 pt-1">
                    @for ($i = 0; $i < 9; $i++)
                        <div class="flex items-center gap-2 rounded px-1 py-1">
                            <div class="size-3 rounded bg-gray-100"></div>
                            <div class="h-2.5 flex-1 rounded bg-gray-{{ $i % 3 === 0 ? '200' : '100' }}" style="max-width: {{ rand(40, 90) }}%"></div>
                            <div class="h-2.5 w-14 rounded bg-gray-100"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Contextual module preview (floating card) --}}
    <div
        x-show="$wire.data?.onboarding_use_case"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute bottom-1/3 left-1/2 -translate-x-1/2 rounded-xl bg-white p-5 shadow-lg ring-1 ring-primary-200"
        x-cloak
    >
        <div class="space-y-3">
            <div class="flex items-center gap-2 text-sm">
                <x-filament::icon icon="ri-user-line" class="size-4 text-primary-500" />
                <span class="font-medium text-gray-900">People</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <x-filament::icon icon="ri-building-line" class="size-4 text-primary-500" />
                <span class="font-medium text-gray-900">Companies</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <x-filament::icon icon="ri-briefcase-line" class="size-4 text-primary-500" />
                <span
                    class="font-medium text-gray-900"
                    x-text="(() => {
                        const labels = {{ Js::from($useCaseLabels) }};
                        return labels[$wire.data?.onboarding_use_case] || 'Opportunities';
                    })()"
                ></span>
            </div>
        </div>
    </div>
</div>
