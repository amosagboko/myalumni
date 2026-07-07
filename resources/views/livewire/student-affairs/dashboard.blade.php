<x-admin.clearance-dashboard
    title="Student Affairs Dashboard"
    subtitle="Student affairs clearance overview and recent activity."
    :clearance-route="route('student-affairs.clearance')"
    :audit-route="route('student-affairs.audit')"
    :kpis="$kpis"
    :recent-activity="$recentActivity"
    :faculties="$faculties"
    :years="$years"
/>
