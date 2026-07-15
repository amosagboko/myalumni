<x-layouts.elcom-chairman title="My Profile | ELCOM Chairman">
    <x-admin.profile-page
        :user="$user"
        :dashboard-route="route('elcom-chairman.home')"
        photo-hint="Upload a photo for your ELCOM Chairman account."
    />
</x-layouts.elcom-chairman>
