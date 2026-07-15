<x-alumniadmin-dashboard title="My Profile | FuLafia Alumni">
    <x-admin.profile-page
        :user="$user"
        :dashboard-route="route('admin.dashboard')"
        photo-hint="Upload a photo for your admin account."
    />
</x-alumniadmin-dashboard>
