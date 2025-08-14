# Chat Cleanup System

## Overview

The Chat Cleanup System automatically removes chat messages older than a specified retention period to manage database storage and maintain system performance.

## Features

### ✅ Automatic Cleanup
- **Daily Execution**: Runs automatically every day via scheduled command
- **30-Day Retention**: Default retention period is 30 days
- **Configurable**: Can be adjusted to different retention periods
- **Safe Operation**: Logs all cleanup activities for audit purposes

### ✅ Smart Message Management
- **Age-Based Filtering**: Only deletes messages older than specified days
- **Soft Delete Support**: Works with existing soft delete functionality
- **Relationship Preservation**: Maintains database integrity
- **Storage Optimization**: Frees up database space automatically

## Commands

### 1. Main Cleanup Command

```bash
# Clean up messages older than 30 days (default)
php artisan chat:cleanup

# Clean up messages older than custom days
php artisan chat:cleanup --days=60
```

**What it does:**
- Finds messages older than specified days
- Permanently deletes old messages
- Logs cleanup activities
- Reports deletion count

### 2. Test Command (Safe Preview)

```bash
# Preview what would be deleted (30 days default)
php artisan chat:test-cleanup

# Preview with custom retention period
php artisan chat:test-cleanup --days=60
```

**What it does:**
- Shows count of messages that would be deleted
- Displays sample messages without deleting
- Estimates storage space that would be freed
- Safe for testing and verification

## Configuration

### Retention Periods

| Period | Use Case | Command |
|--------|----------|---------|
| 30 days | Standard retention | `php artisan chat:cleanup` |
| 60 days | Extended retention | `php artisan chat:cleanup --days=60` |
| 90 days | Long retention | `php artisan chat:cleanup --days=90` |
| Custom | Specific needs | `php artisan chat:cleanup --days=X` |

### Scheduled Execution

The cleanup runs automatically every day at midnight:

```php
// In app/Console/Kernel.php
$schedule->command('chat:cleanup')->daily();
```

## Database Impact

### What Gets Deleted
- Messages older than retention period
- Both regular and soft-deleted messages
- Associated metadata and timestamps

### What Stays Safe
- Messages within retention period
- User relationships and accounts
- Message structure and schema

### Storage Benefits
- **Immediate**: Frees up database space
- **Long-term**: Prevents unlimited growth
- **Performance**: Maintains query efficiency

## Safety Features

### ✅ Built-in Protections
- **Logging**: All cleanup activities are logged
- **Error Handling**: Graceful failure with detailed error messages
- **Transaction Safety**: Database operations are atomic
- **Audit Trail**: Complete record of what was deleted and when

### ✅ Verification Commands
- **Test Mode**: Preview deletions without executing
- **Count Verification**: See exactly how many messages will be affected
- **Age Analysis**: Group messages by age ranges
- **Storage Estimation**: Calculate space savings

## Usage Examples

### Daily Maintenance
```bash
# Check current message count
php artisan tinker --execute="echo 'Total messages: ' . \App\Models\Message::count();"

# Test cleanup (safe)
php artisan chat:test-cleanup

# Perform actual cleanup
php artisan chat:cleanup
```

### Custom Retention Periods
```bash
# Keep messages for 60 days
php artisan chat:cleanup --days=60

# Keep messages for 1 week
php artisan chat:cleanup --days=7

# Keep messages for 3 months
php artisan chat:cleanup --days=90
```

### Emergency Cleanup
```bash
# Clean very old messages (e.g., 1 year+)
php artisan chat:cleanup --days=365

# Clean moderately old messages (e.g., 2 weeks)
php artisan chat:cleanup --days=14
```

## Monitoring & Logs

### Log Locations
- **Application Logs**: `storage/logs/laravel.log`
- **Cleanup Logs**: Look for "Chat cleanup completed" entries
- **Error Logs**: Look for "Chat cleanup failed" entries

### Log Format
```json
{
    "cutoff_date": "2025-01-01 00:00:00",
    "messages_deleted": 150,
    "days_old": 30,
    "executed_at": "2025-01-15 00:00:00"
}
```

### Monitoring Commands
```bash
# Check recent cleanup logs
tail -f storage/logs/laravel.log | grep "Chat cleanup"

# View cleanup statistics
php artisan chat:test-cleanup --days=30
```

## Troubleshooting

### Common Issues

#### No Messages Deleted
- Check if messages exist older than retention period
- Verify database connection and permissions
- Check application logs for errors

#### Permission Errors
- Ensure proper database user permissions
- Check file system permissions for logs
- Verify Laravel environment configuration

#### Performance Issues
- Monitor database performance during cleanup
- Consider running during low-traffic periods
- Adjust retention period if needed

### Debug Commands
```bash
# Check message age distribution
php artisan tinker --execute="
    \$ages = \App\Models\Message::selectRaw('
        CASE 
            WHEN created_at >= NOW() - INTERVAL 7 DAY THEN \"< 7 days\"
            WHEN created_at >= NOW() - INTERVAL 30 DAY THEN \"7-30 days\"
            WHEN created_at >= NOW() - INTERVAL 90 DAY THEN \"30-90 days\"
            ELSE \"90+ days\"
        END as age_group,
        COUNT(*) as count
    ')
    ->groupBy('age_group')
    ->get();
    foreach(\$ages as \$age) { echo \$age->age_group . ': ' . \$age->count . PHP_EOL; }
"

# Verify cleanup command registration
php artisan list | grep chat
```

## Best Practices

### Before Running Cleanup
1. **Test First**: Always use `chat:test-cleanup` before actual cleanup
2. **Backup**: Ensure database backups are current
3. **Monitor**: Check system performance and storage
4. **Notify**: Inform users if significant cleanup is planned

### Retention Period Selection
1. **30 Days**: Standard for most applications
2. **60 Days**: Extended retention for compliance
3. **90 Days**: Long retention for legal requirements
4. **Custom**: Based on specific business needs

### Scheduling Considerations
1. **Low Traffic**: Run during off-peak hours
2. **Monitoring**: Watch for performance impact
3. **Logging**: Ensure logs are properly rotated
4. **Alerts**: Set up monitoring for cleanup failures

## Migration & Setup

### New Installations
1. Commands are automatically registered
2. Scheduled task is automatically configured
3. No additional setup required

### Existing Systems
1. Commands will be available immediately
2. Existing messages are preserved
3. Cleanup starts from next scheduled run

### Verification
```bash
# Verify command availability
php artisan chat:test-cleanup

# Check scheduled tasks
php artisan schedule:list

# Test cleanup functionality
php artisan chat:cleanup --days=1
```

## Security Considerations

### Data Protection
- **Audit Trail**: Complete logging of all deletions
- **User Privacy**: Messages are permanently removed
- **Compliance**: Retention policies can be documented
- **Recovery**: No automatic recovery (manual backup required)

### Access Control
- **Console Only**: Commands run via Artisan CLI
- **Server Access**: Requires server command line access
- **Logging**: All activities are logged for review
- **Monitoring**: Failed operations are logged with details

## Support & Maintenance

### Regular Tasks
- **Daily**: Automatic cleanup runs
- **Weekly**: Review cleanup logs
- **Monthly**: Verify retention policies
- **Quarterly**: Assess storage impact

### Updates & Modifications
- **Retention Periods**: Adjust via command parameters
- **Scheduling**: Modify in `app/Console/Kernel.php`
- **Logging**: Configure in `config/logging.php`
- **Monitoring**: Set up alerts and notifications

---

**Note**: This system provides automatic chat cleanup while maintaining data integrity and providing comprehensive monitoring capabilities. 