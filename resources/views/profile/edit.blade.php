@if ($user->hasAnyRole(['customer', 'admin', 'mechanic']))
    @include('profile.customer')
@else
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-slate-900 tracking-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white overflow-hidden shadow-sm border border-slate-200/70 sm:rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white overflow-hidden shadow-sm border border-slate-200/70 sm:rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white overflow-hidden shadow-sm border border-slate-200/70 sm:rounded-2xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@endif
