# Implementation Guide: Corrected 2025+ Alumni Fee Structure

## Overview
This document outlines the implementation of the corrected fee structure for alumni graduating in 2025 and beyond. The new structure ensures that each fee type is separate and category-specific, with the correct amounts as specified.

## Fee Structure Summary

### **Postgraduate Alumni (2025+)**
- **Registration Fee**: ₦5,000
- **Development Levy**: ₦13,000
- **Data Processing Fee**: ₦2,500
- **Tech Support Fee**: ₦1,000
- **Total**: ₦21,500

### **Undergraduate (Full-time) Alumni (2025+)**
- **Registration Fee**: ₦5,000
- **Development Levy**: ₦7,700
- **Data Processing Fee**: ₦2,500
- **Tech Support Fee**: ₦1,000
- **Total**: ₦16,200

### **Undergraduate (Part-time) Alumni (2025+)**
- **Registration Fee**: ₦5,000
- **Development Levy**: ₦10,000
- **Data Processing Fee**: ₦2,500
- **Tech Support Fee**: ₦1,000
- **Total**: ₦18,500

### **Diploma Alumni (2025+)**
- **Registration Fee**: ₦5,000
- **Development Levy**: ₦5,000
- **Data Processing Fee**: ₦2,500
- **Tech Support Fee**: ₦1,000
- **Total**: ₦13,500

## Implementation Steps

### **Step 1: Run the Tech Support Fee Type Seeder**
```bash
php artisan db:seed --class=TechSupportFeeTypeSeeder
```

### **Step 2: Update Credo Central Service Codes**
The service codes have been updated in `config/services.php`:
- `registration`: 003486REG2025
- `development_levy`: 003486DEV2025
- `data_processing`: 003486DAT2025
- `tech_support`: 003486TEC2025

### **Step 3: Run the 2025 Fee Templates Update Seeder**
```bash
php artisan db:seed --class=Update2025FeeTemplatesSeeder
```

### **Step 4: Verify Implementation**
Check that:
1. All fee types exist in the database
2. Fee templates are created for each category and graduation year
3. Each fee type creates a separate payment transaction
4. Alumni see only the fees applicable to their category

## Key Features

### **Separate Payments**
- Each fee type (Registration, Development Levy, Data Processing, Tech Support) is a separate payment
- Alumni must pay each fee individually
- No bundled payments

### **Category-Based Filtering**
- Fees are automatically filtered based on alumni category
- Alumni only see fees applicable to their category
- System prevents access to inappropriate fees

### **Graduation Year Logic**
- **2023 & Earlier**: Pay subscription fees only
- **2024**: Exempted from all fees
- **2025+**: Pay category-based fees (NOT subscription fees)

## Database Changes

### **New Fee Type**
- Added `tech_support` fee type with code `tech_support`

### **Updated Fee Templates**
- All existing 2025 fee templates for these fee types are cleared
- New templates are created with correct amounts and category associations
- Each template includes proper validation dates

### **Service Code Updates**
- Added new service codes for each fee type
- Ensures proper integration with Credo Central payment gateway

## Testing

### **Test Scenarios**
1. **Postgraduate 2025+**: Should see 4 separate fees totaling ₦21,500
2. **Undergraduate (Full-time) 2025+**: Should see 4 separate fees totaling ₦16,200
3. **Undergraduate (Part-time) 2025+**: Should see 4 separate fees totaling ₦18,500
4. **Diploma 2025+**: Should see 4 separate fees totaling ₦13,500

### **Payment Flow**
1. Alumni logs in and navigates to payments
2. System shows only applicable fees for their category
3. Each fee can be paid separately
4. Payment creates individual transaction records
5. Alumni can track payment status for each fee type

## Troubleshooting

### **Common Issues**
1. **Fee Types Not Found**: Ensure TechSupportFeeTypeSeeder has been run
2. **Categories Not Found**: Verify alumni categories exist in the database
3. **Service Codes Missing**: Check config/services.php for proper service codes
4. **Fee Templates Not Created**: Run Update2025FeeTemplatesSeeder

### **Verification Commands**
```bash
# Check fee types
php artisan tinker
>>> App\Models\FeeType::whereIn('code', ['registration', 'development_levy', 'data_processing', 'tech_support'])->get();

# Check fee templates
>>> App\Models\FeeTemplate::where('graduation_year', 2025)->with('feeType', 'category')->get();

# Check alumni categories
>>> App\Models\AlumniCategory::whereIn('slug', ['postgraduate', 'undergraduate-full-time', 'undergraduate-part-time', 'diploma'])->get();
```

## Rollback

If you need to rollback the changes:

### **Remove Fee Templates**
```bash
php artisan tinker
>>> DB::table('fee_templates')->where('graduation_year', 2025)->whereIn('fee_type_id', [1,2,3,4])->delete();
```

### **Remove Tech Support Fee Type**
```bash
php artisan tinker
>>> DB::table('fee_types')->where('code', 'tech_support')->delete();
```

## Support

For any issues or questions regarding this implementation, please refer to:
1. Database logs for detailed error information
2. Laravel logs for application-level errors
3. Payment gateway logs for transaction issues

---

**Note**: This implementation ensures that 2025+ alumni pay the correct fees based on their category, with each fee type being a separate payment transaction as requested. 