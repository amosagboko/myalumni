# PowerShell script to rename migration files segment by segment
# Each segment will require user confirmation before proceeding

# Function to rename a file with confirmation
function Rename-MigrationFile {
    param (
        [string]$oldName,
        [string]$newName
    )
    
    $oldPath = Join-Path "database/migrations" $oldName
    $newPath = Join-Path "database/migrations" $newName
    
    if (Test-Path $oldPath) {
        Write-Host "Renaming: $oldName -> $newName"
        Rename-Item -Path $oldPath -NewName $newName -Force
        return $true
    } else {
        Write-Host "Warning: File not found: $oldName" -ForegroundColor Yellow
        return $false
    }
}

# Function to process a segment of migrations
function Process-MigrationSegment {
    param (
        [string]$segmentName,
        [hashtable]$migrations
    )
    
    Write-Host "`nProcessing segment: $segmentName" -ForegroundColor Cyan
    Write-Host "The following files will be renamed:"
    
    foreach ($migration in $migrations.GetEnumerator()) {
        Write-Host "  $($migration.Key) -> $($migration.Value)"
    }
    
    $confirmation = Read-Host "`nDo you want to proceed with this segment? (y/n)"
    if ($confirmation -eq 'y') {
        $success = $true
        foreach ($migration in $migrations.GetEnumerator()) {
            if (-not (Rename-MigrationFile -oldName $migration.Key -newName $migration.Value)) {
                $success = $false
            }
        }
        if ($success) {
            Write-Host "`nSegment completed successfully!" -ForegroundColor Green
        } else {
            Write-Host "`nSegment completed with warnings. Please review the output above." -ForegroundColor Yellow
        }
    } else {
        Write-Host "`nSkipping this segment." -ForegroundColor Yellow
    }
    
    $continue = Read-Host "`nPress Enter to continue to next segment, or type 'exit' to stop"
    return $continue -ne 'exit'
}

# Define migration segments
$segments = @{
    "Base System Tables" = @{
        "0001_01_01_000000_create_users_table.php" = "0001_01_01_000000_create_users_table.php"
        "0001_01_01_000001_create_cache_table.php" = "0001_01_01_000001_create_cache_table.php"
        "0001_01_01_000002_create_jobs_table.php" = "0001_01_01_000002_create_jobs_table.php"
    }
    
    "Core User and Authentication" = @{
        "2024_03_19_000000_add_status_to_users_table.php" = "2024_03_19_000000_add_status_to_users_table.php"
        "2024_03_19_000001_create_reports_table.php" = "2024_03_19_000001_create_reports_table.php"
        "2024_03_21_000000_create_activity_log_table.php" = "2024_03_21_000000_create_activity_log_table.php"
        "2024_03_21_000001_add_is_first_login_to_users_table.php" = "2024_03_21_000001_add_is_first_login_to_users_table.php"
    }
    
    "Alumni Core" = @{
        "2024_04_25_create_alumni_table.php" = "2024_04_25_000000_create_alumni_table.php"
    }
    
    "Social Features" = @{
        "2024_12_13_221953_create_posts_table.php" = "2024_12_13_221953_create_posts_table.php"
        "2024_12_13_221954_create_comments_table.php" = "2024_12_13_221954_create_comments_table.php"
        "2024_12_13_222044_create_customer_supports_table.php" = "2024_12_13_222044_create_customer_supports_table.php"
        "2024_12_13_222127_create_events_table.php" = "2024_12_13_222127_create_events_table.php"
        "2024_12_13_222154_create_followers_table.php" = "2024_12_13_222154_create_followers_table.php"
        "2024_12_13_222209_create_followings_table.php" = "2024_12_13_222209_create_followings_table.php"
        "2024_12_13_222229_create_friends_table.php" = "2024_12_13_222229_create_friends_table.php"
        "2024_12_13_222243_create_groups_table.php" = "2024_12_13_222243_create_groups_table.php"
        "2024_12_13_222259_create_likes_table.php" = "2024_12_13_222259_create_likes_table.php"
        "2024_12_13_222342_create_notifications_table.php" = "2024_12_13_222342_create_notifications_table.php"
        "2024_12_13_222359_create_pages_table.php" = "2024_12_13_222359_create_pages_table.php"
        "2024_12_13_222415_create_page_likes_table.php" = "2024_12_13_222415_create_page_likes_table.php"
        "2024_12_13_222449_create_post_media_table.php" = "2024_12_13_222449_create_post_media_table.php"
        "2024_12_13_222613_create_saved_posts_table.php" = "2024_12_13_222613_create_saved_posts_table.php"
        "2024_12_13_222630_create_stories_table.php" = "2024_12_13_222630_create_stories_table.php"
        "2024_12_13_222825_create_vacancies_table.php" = "2024_12_13_222825_create_vacancies_table.php"
        "2024_12_15_005550_create_story_comments_table.php" = "2024_12_15_005550_create_story_comments_table.php"
        "2024_12_15_010723_create_group_members_table.php" = "2024_12_15_010723_create_group_members_table.php"
    }

    "Election System" = @{
        "2024_12_13_222111_create_elections_table.php" = "2024_12_13_222111_create_elections_table.php"
        "2024_12_13_222112_create_election_offices_table.php" = "2024_12_13_222112_create_election_offices_table.php"
        "2024_12_13_222113_create_candidates_table.php" = "2024_12_13_222113_create_candidates_table.php"
        "2025_05_11_000000_create_accredited_voters_table.php" = "2025_05_11_000000_create_accredited_voters_table.php"
        "2025_05_11_000002_create_votes_table.php" = "2025_05_11_000002_create_votes_table.php"
        "2025_05_11_000003_create_election_results_table.php" = "2025_05_11_000003_create_election_results_table.php"
        "2025_05_11_000005_add_soft_deletes_to_elections_table.php" = "2025_05_11_000005_add_soft_deletes_to_elections_table.php"
        "2025_05_16_000000_add_deleted_at_to_elections_table.php" = "2025_05_16_000000_add_deleted_at_to_elections_table.php"
        "2025_05_17_000000_add_eoi_status_to_elections_table.php" = "2025_05_17_000000_add_eoi_status_to_elections_table.php"
        "2025_05_21_add_eoi_period_to_elections.php" = "2025_05_21_000000_add_eoi_period_to_elections_table.php"
    }

    "Student and Alumni Management" = @{
        "2025_02_10_162728_create_students_table.php" = "2025_02_10_162728_create_students_table.php"
        "2025_02_15_181317_create_permission_tables.php" = "2025_02_15_181317_create_permission_tables.php"
        "2025_03_06_232113_add_created_by_to_users_table.php" = "2025_03_06_232113_add_created_by_to_users_table.php"
        "2025_03_27_111841_create_follows_table.php" = "2025_03_27_111841_create_follows_table.php"
        "2025_04_17_013828_create_friend_requests_table.php" = "2025_04_17_013828_create_friend_requests_table.php"
        "2025_04_30_015821_create_alumni_years_table.php" = "2025_04_30_015821_create_alumni_years_table.php"
    }

    "Fee and Transaction System" = @{
        "2025_04_30_015846_create_fee_templates_table.php" = "2025_04_30_015846_create_fee_templates_table.php"
        "2025_04_30_015859_create_transactions_table.php" = "2025_04_30_015859_create_transactions_table.php"
        "2025_05_02_091922_create_alumni_categories_table.php" = "2025_05_02_091922_create_alumni_categories_table.php"
        "2025_05_02_094454_create_category_transaction_fees_table.php" = "2025_05_02_094454_create_category_transaction_fees_table.php"
        "2025_05_02_101417_migrate_from_fee_templates_to_category_transaction_fees.php" = "2025_05_02_101417_migrate_from_fee_templates_to_category_transaction_fees.php"
        "2025_05_02_102634_add_alumni_year_id_to_category_transaction_fees_table.php" = "2025_05_02_102634_add_alumni_year_id_to_category_transaction_fees_table.php"
        "2025_05_15_082545_create_fee_types_table.php" = "2025_05_15_082545_create_fee_types_table.php"
        "2025_05_15_082546_create_category_transaction_fees_table.php" = "2025_05_15_082546_create_category_transaction_fees_table.php"
        "2025_05_16_000000_add_fee_type_id_to_election_offices_table.php" = "2025_05_16_000000_add_fee_type_id_to_election_offices_table.php"
    }

    "Candidate and Agent System" = @{
        "2024_03_21_000002_create_expressions_of_interest_table.php" = "2024_03_21_000002_create_expressions_of_interest_table.php"
        "2024_03_22_000000_add_agent_id_to_candidates_table.php" = "2024_03_22_000000_add_agent_id_to_candidates_table.php"
        "2025_05_18_143500_add_candidate_documents_to_candidates_table.php" = "2025_05_18_143500_add_candidate_documents_to_candidates_table.php"
    }

    "Message System" = @{
        "2024_12_13_222327_create_messages_table.php" = "2024_12_13_222327_create_messages_table.php"
        "2025_03_21_000003_update_messages_table_add_columns.php" = "2025_03_21_000003_update_messages_table_add_columns.php"
    }

    "Notification System" = @{
        "2025_05_21_180500_update_notifications_table.php" = "2025_05_21_180500_update_notifications_table.php"
    }
}

# Process each segment
foreach ($segment in $segments.GetEnumerator()) {
    if (-not (Process-MigrationSegment -segmentName $segment.Key -migrations $segment.Value)) {
        Write-Host "`nScript stopped by user." -ForegroundColor Yellow
        break
    }
}

Write-Host "`nMigration renaming process completed." -ForegroundColor Green 