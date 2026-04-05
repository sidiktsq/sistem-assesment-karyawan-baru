<x-filament-widgets::widget class="fi-wi-account">
    <x-filament::section>
        <div style="display: flex; align-items: center; justify-between: space-between; gap: 12px; width: 100%;">
            <x-filament-panels::avatar.user :user="filament()->auth()->user()" />

            <div style="flex: 1;">
                <h2 style="font-size: 1rem; font-weight: 600; color: #ffffffff;" class="dark:text-white leading-6">
                    {{ __('filament-panels::widgets/account-widget.welcome') }}
                </h2>

                <p style="font-size: 0.875rem; color: #6b7280;" class="dark:text-gray-400 leading-6">
                    Reviewer
                </p>
            </div>

            <form action="{{ filament()->getLogoutUrl() }}" method="post" style="margin-block: auto;">
                @csrf

                <x-filament::button
                    color="gray"
                    icon="heroicon-m-arrow-left-on-rectangle"
                    icon-alias="widgets::account-widget.logout-button"
                    labeled-from="sm"
                    tag="button"
                    type="submit"
                >
                    {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
                </x-filament::button>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
