<x-layouts.academic-affairs title="My Profile | Academic Affairs">
    <x-admin.profile-page
        :user="$user"
        :dashboard-route="route('academic-affairs.home')"
        photo-hint="Upload a photo for your Academic Affairs account."
    />
</x-layouts.academic-affairs>
