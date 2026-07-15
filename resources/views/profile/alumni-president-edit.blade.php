<x-layouts.alumni-president title="My Profile | Alumni President">
    <x-admin.profile-page
        :user="$user"
        :dashboard-route="route('alumni-president.home')"
        photo-hint="Upload a photo for your Alumni President account."
    />
</x-layouts.alumni-president>
