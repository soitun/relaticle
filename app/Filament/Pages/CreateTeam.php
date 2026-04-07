<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Jetstream\CreateTeam as CreateTeamAction;
use App\Actions\Jetstream\InviteTeamMember;
use App\Enums\OnboardingReferralSource;
use App\Enums\OnboardingUseCase;
use App\Filament\Resources\CompanyResource;
use App\Models\Team;
use App\Models\User;
use App\Rules\ValidTeamSlug;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Override;

final class CreateTeam extends RegisterTenant
{
    protected string $view = 'filament.pages.create-team';

    public function getMaxContentWidth(): Width
    {
        return Width::FiveExtraLarge;
    }

    #[Override]
    public static function getLabel(): string
    {
        return 'Create Team';
    }

    #[Override]
    public function getHeading(): string
    {
        return '';
    }

    #[Override]
    public function getSubheading(): ?string
    {
        return null;
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    $this->getWorkspaceStep(),
                    $this->getAttributionStep(),
                    $this->getUseCaseStep(),
                    $this->getInviteStep(),
                ])
                    ->hiddenHeader()
                    ->contained(false)
                    ->skippable()
                    ->nextAction(
                        fn (Action $action) => $action
                            ->label('Continue')
                            ->size(Size::Large)
                    )
                    ->previousAction(
                        fn (Action $action) => $action
                            ->hidden()
                    )
                    ->submitAction(new HtmlString(
                        '<button type="submit" wire:click="register" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-color-primary gap-1.5 px-4 py-2.5 text-sm inline-grid shadow-sm bg-primary-600 text-white hover:bg-primary-500 focus-visible:ring-primary-500/50 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus-visible:ring-primary-400/50 w-full">Get started</button>'
                    )),
            ]);
    }

    private function getWorkspaceStep(): Step
    {
        return Step::make('Workspace')
            ->schema([
                Placeholder::make('workspace_heading')
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        '<h3 class="text-lg font-semibold text-gray-950 dark:text-white">Create your workspace</h3>'
                    ))
                    ->dehydrated(false),
                ...$this->getWorkspaceFormComponents(),
            ]);
    }

    private function getAttributionStep(): Step
    {
        return Step::make('Attribution')
            ->schema([
                Placeholder::make('attribution_heading')
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        '<h3 class="text-lg font-semibold text-gray-950 dark:text-white">How did you hear about us?</h3>'
                        .'<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please select below where you found out about Relaticle. This step is optional.</p>'
                    ))
                    ->dehydrated(false),

                ToggleButtons::make('onboarding_referral_source')
                    ->hiddenLabel()
                    ->options(
                        collect(OnboardingReferralSource::cases())
                            ->mapWithKeys(fn (OnboardingReferralSource $source): array => [
                                $source->value => $source->getLabel(),
                            ])
                            ->all()
                    )
                    ->inline(),

                $this->getSkipLink(),
            ]);
    }

    private function getUseCaseStep(): Step
    {
        return Step::make('Use case')
            ->schema([
                Placeholder::make('use_case_heading')
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        '<h3 class="text-lg font-semibold text-gray-950 dark:text-white">Help us customize your workspace</h3>'
                        .'<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Relaticle is all about empowering you to build the exact CRM you need, no matter how complex.</p>'
                        .'<p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tell us about your use case to get started with templates, or start with a blank canvas.</p>'
                    ))
                    ->dehydrated(false),

                ToggleButtons::make('onboarding_use_case')
                    ->label('What will you be using Relaticle for?')
                    ->options(
                        collect(OnboardingUseCase::cases())
                            ->mapWithKeys(fn (OnboardingUseCase $case): array => [
                                $case->value => $case->getLabel(),
                            ])
                            ->all()
                    )
                    ->icons(
                        collect(OnboardingUseCase::cases())
                            ->mapWithKeys(fn (OnboardingUseCase $case): array => [
                                $case->value => $case->getIcon(),
                            ])
                            ->all()
                    )
                    ->inline()
                    ->live(),

                ToggleButtons::make('onboarding_context')
                    ->label('Please tell us more about your use case.')
                    ->options(function (Get $get): array {
                        $useCase = OnboardingUseCase::tryFrom($get('onboarding_use_case') ?? '');

                        if (! $useCase) {
                            return [];
                        }

                        return $useCase->getSubOptions();
                    })
                    ->inline()
                    ->multiple()
                    ->visible(function (Get $get): bool {
                        $useCase = OnboardingUseCase::tryFrom($get('onboarding_use_case') ?? '');

                        return $useCase !== null && $useCase->getSubOptions() !== [];
                    }),

                $this->getSkipLink(),
            ]);
    }

    private function getInviteStep(): Step
    {
        return Step::make('Invite')
            ->schema([
                Placeholder::make('invite_heading')
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        '<h3 class="text-lg font-semibold text-gray-950 dark:text-white">Collaborate with your team</h3>'
                        .'<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The more your teammates use Relaticle, the more powerful it becomes.</p>'
                    ))
                    ->dehydrated(false),

                Placeholder::make('invite_subheading')
                    ->hiddenLabel()
                    ->content(new HtmlString(
                        '<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Invite your team to collaborate</p>'
                    ))
                    ->dehydrated(false),

                TextInput::make('invite_email_1')
                    ->hiddenLabel()
                    ->email()
                    ->placeholder('colleague@company.com'),

                TextInput::make('invite_email_2')
                    ->hiddenLabel()
                    ->email()
                    ->placeholder('colleague@company.com'),

                $this->getSkipLink('Skip for now', isLastStep: true),
            ]);
    }

    private function getSkipLink(string $label = 'Skip', bool $isLastStep = false): Placeholder
    {
        $action = $isLastStep ? '$wire.register()' : 'goToNextStep()';

        return Placeholder::make("skip_{$label}")
            ->hiddenLabel()
            ->content(new HtmlString(
                "<div class=\"text-center\"><button type=\"button\" x-on:click=\"{$action}\" class=\"text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300\">{$label}</button></div>"
            ))
            ->dehydrated(false);
    }

    /**
     * @return array<Component>
     */
    private function getWorkspaceFormComponents(): array
    {
        $appHost = parse_url(url()->getAppUrl(), PHP_URL_HOST);

        return [
            TextInput::make('name')
                ->label('Company name')
                ->required()
                ->maxLength(255)
                ->placeholder('Acme Corp')
                ->autofocus()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                    if ($get('slug_auto_generated') === true || blank($get('slug'))) {
                        $set('slug', Str::slug($state ?? ''));
                        $set('slug_auto_generated', true);
                    }
                }),

            TextInput::make('slug')
                ->label('Workspace handle')
                ->required()
                ->maxLength(255)
                ->rules([new ValidTeamSlug])
                ->unique(Team::class, 'slug')
                ->prefix("{$appHost}/")
                ->helperText('Only lowercase letters, numbers, and hyphens are allowed.')
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set): void {
                    $set('slug_auto_generated', false);
                }),

            Hidden::make('slug_auto_generated')
                ->default(true)
                ->dehydrated(false),
        ];
    }

    #[Override]
    protected function getRedirectUrl(): string
    {
        return CompanyResource::getUrl('index', ['tenant' => $this->tenant]);
    }

    #[Override]
    protected function handleRegistration(array $data): Model
    {
        /** @var User $user */
        $user = auth('web')->user();

        $team = resolve(CreateTeamAction::class)->create($user, $data);

        $this->sendOnboardingInvites($user, $team, $data);

        return $team;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sendOnboardingInvites(User $user, Team $team, array $data): void
    {
        $emails = array_filter([
            $data['invite_email_1'] ?? null,
            $data['invite_email_2'] ?? null,
        ], fn (?string $email): bool => filled($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false);

        foreach ($emails as $email) {
            try {
                resolve(InviteTeamMember::class)->invite($user, $team, $email, 'editor');
            } catch (ValidationException) {
                continue;
            }
        }
    }

    /**
     * @return array<Action|ActionGroup>
     */
    #[Override]
    protected function getFormActions(): array
    {
        return [];
    }

    #[Override]
    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->size(Size::Medium)
            ->label('Create workspace')
            ->submit('register');
    }

    /**
     * @return array<string, string>
     */
    public function getUseCaseLabelsForPreview(): array
    {
        return collect(OnboardingUseCase::cases())
            ->mapWithKeys(fn (OnboardingUseCase $case): array => [
                $case->value => $case->getLabel(),
            ])
            ->all();
    }

    public function isFirstTeam(): bool
    {
        /** @var User $user */
        $user = auth('web')->user();

        return ! $user->ownedTeams()->exists();
    }
}
