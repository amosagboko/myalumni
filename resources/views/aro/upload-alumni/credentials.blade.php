<x-layouts.alumni-relations-officer title="Retrieve Credentials | Alumni Relations Officer">
    <x-admin.retrieve-credentials-page
        :dashboard-route="route('alumni-relations-officer.home')"
        :alumni="$alumni"
        :name="$name"
        :matriculation-id="$matriculation_id"
        :temp-email="$tempEmail"
        :category="$category"
    />
</x-layouts.alumni-relations-officer>
