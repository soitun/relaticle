<x-filament-panels::page.simple>
    <div class="flex min-h-[480px] overflow-hidden">
        <div class="flex flex-1 flex-col justify-between pe-8">
            {{ $this->content }}
        </div>

        <div class="-my-6 -me-6 hidden w-[45%] lg:block">
            <x-onboarding.crm-preview
                :use-case-labels="$this->getUseCaseLabelsForPreview()"
            />
        </div>
    </div>
</x-filament-panels::page.simple>
