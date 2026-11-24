<x-layouts.accountant-linkages title="My Profile | Accountant-Linkages">
    <x-admin.profile-page
        :user="$user"
        :dashboard-route="route('accountant-linkages.payments')"
        dashboard-label="Payment Status"
        photo-hint="Upload a photo for your Accountant-Linkages account."
    />
</x-layouts.accountant-linkages>
