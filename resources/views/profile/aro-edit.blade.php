<x-layouts.alumni-relations-officer title="My Profile | Alumni Relations Officer">
    <x-admin.profile-page
        embedded
        :user="$user"
        :dashboard-route="route('alumni-relations-officer.home')"
        photo-hint="Upload a photo for your ARO account."
    />
</x-layouts.alumni-relations-officer>
