<x-layouts.alumni-relations-officer title="Upload Alumni | Alumni Relations Officer">
    <x-admin.upload-alumni-page
        :programmes="$programmes"
        :departments="$departments"
        :faculties="$faculties"
        :years="$years"
        :categories="$categories"
        :show-admin-actions="false"
        :dashboard-route="route('alumni-relations-officer.home')"
    />
</x-layouts.alumni-relations-officer>
