<x-layouts.student-affairs title="My Profile | Student Affairs">
    <x-admin.profile-page
        :user="$user"
        :dashboard-route="route('student-affairs.home')"
        photo-hint="Upload a photo for your Student Affairs account."
    />
</x-layouts.student-affairs>
