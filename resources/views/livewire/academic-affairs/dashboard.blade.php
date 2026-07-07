<x-admin.clearance-dashboard
    title="Academic Affairs Dashboard"
    subtitle="Academic affairs clearance overview and recent activity."
    :clearance-route="route('academic-affairs.clearance')"
    :audit-route="route('academic-affairs.audit')"
    :kpis="$kpis"
    :recent-activity="$recentActivity"
    :faculties="$faculties"
    :years="$years"
/>
