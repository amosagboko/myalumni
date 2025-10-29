# Complete Flow: Categories → Qualification Types → Service Codes

## 🎯 Overview

This document explains how **Categories**, **Qualification Types**, and **Credo Central Service Codes** work together in the system.

---

## 📋 Step-by-Step Flow

### **STEP 1: Admin Assigns Category to Alumni**
**Location:** `/admin/alumni-categories/assign`

- Admin selects an alumni and assigns them a category (e.g., "Postgraduate")
- System stores `category_id` in the `alumni` table
- **What happens:**
  - If assigning "Postgraduate" → System logs warning if `qualification_type` is invalid
  - If switching FROM "Postgraduate" → System automatically clears PhD/MSc/PGD from `qualification_type`

### **STEP 2: Alumni Completes Bio Data**
**Location:** `/alumni/bio-data`

- Alumni logs in and sees bio data form
- **Qualification Type dropdown shows:**
  - **If category = "Postgraduate"** → Shows: `PhD`, `MSc`, `PGD`
  - **If category = Other** → Shows: `Degree`, `Diploma`, `Certificate`
- Alumni selects their qualification type (e.g., "PhD")
- System stores `qualification_type` in the `alumni` table

### **STEP 3: Alumni Views Fees**
**Location:** `/alumni/payments`

- System loads fees based on:
  - Alumni's `category_id`
  - Alumni's `graduation_year` (2025+)
- Fees are displayed from `fee_templates` table filtered by category

### **STEP 4: Alumni Initiates Payment**
**Location:** `/alumni/payments` → Click "Pay"

- System creates a `transaction` record
- **System determines Credo Central service code:**

```
Fee Type Code (e.g., "registration")
    ↓
Category Slug (e.g., "postgraduate")
    ↓
Qualification Type (e.g., "PhD")
    ↓
Service Code Lookup
```

### **STEP 5: Service Code Resolution**
**Location:** `CredoCentralService.php`

**For Postgraduate:**
1. Category = `"postgraduate"`
2. Qualification Type = `"PhD"` (from alumni record)
3. System creates key: `"postgraduate-phd"`
4. Looks up in `config/services.php`:
   ```php
   'registration' => [
       'postgraduate-phd' => '003486REG2025_PHD',  ← Uses this
       'postgraduate-msc' => '003486REG2025_MSC',
       'postgraduate-pgd' => '003486REG2025_PGD',
   ]
   ```
5. **If not found → ERROR (no fallbacks)**

**For Other Categories:**
1. Category = `"undergraduate-full-time"` (for example)
2. Looks up in `config/services.php`:
   ```php
   'registration' => [
       'undergraduate-full-time' => '003486REG2025_UGFT',  ← Uses this
   ]
   ```
3. **If not found → ERROR (no fallbacks)**

### **STEP 6: Payment Processing**
- Service code is sent to Credo Central API
- Payment link is generated
- Alumni completes payment on Credo Central

---

## 🔑 Key Components

### **1. Database Tables**

**`alumni_categories` Table:**
- `id` → Primary key
- `name` → "Postgraduate", "Undergraduate (Full-time)", etc.
- `slug` → "postgraduate", "undergraduate-full-time", etc.

**`alumni` Table:**
- `category_id` → Foreign key to `alumni_categories.id`
- `qualification_type` → "PhD", "MSc", "PGD", "Degree", "Diploma", "Certificate"

**`fee_templates` Table:**
- `category_id` → Which category this fee applies to
- `fee_type_id` → What type of fee (registration, development_levy, etc.)
- `amount` → Fee amount

### **2. Configuration File**

**`config/services.php`:**
```php
'credocentral' => [
    'service_codes' => [
        'registration' => [
            'postgraduate-phd' => '003486REG2025_PHD',
            'postgraduate-msc' => '003486REG2025_MSC',
            'postgraduate-pgd' => '003486REG2025_PGD',
            'undergraduate-full-time' => '003486REG2025_UGFT',
            // ... etc
        ]
    ]
]
```

**Service Code Format:**
- `postgraduate-{qualification}` → For postgraduate with qualification type
- `{category-slug}` → For other categories directly

---

## 🎯 Important Rules

### **Rule 1: Category Assignment**
- ✅ Admin assigns category via `/admin/alumni-categories/assign`
- ✅ System validates and logs inconsistencies
- ✅ System cleans up invalid qualification types when switching categories

### **Rule 2: Qualification Type Selection**
- ✅ **Postgraduate** → Must select: `PhD`, `MSc`, or `PGD`
- ✅ **Other Categories** → Must select: `Degree`, `Diploma`, or `Certificate`
- ✅ Field is **mandatory** (required)

### **Rule 3: Service Code Lookup**
- ✅ **NO FALLBACKS** - Each category/qualification combination MUST have a service code
- ✅ If service code not found → **ERROR** (payment fails)
- ✅ For postgraduate → Uses `postgraduate-{qualification}` key
- ✅ For others → Uses `{category-slug}` key

---

## 🔄 Complete Example: PhD Alumni Paying Registration Fee

1. **Admin assigns:** Category = "Postgraduate" (slug: `postgraduate`)
2. **Alumni completes bio data:** Selects `qualification_type = "PhD"`
3. **Alumni views fees:** Sees "Registration Fee" (from fee_templates where category_id = postgraduate)
4. **Alumni clicks "Pay":** System creates transaction
5. **System determines service code:**
   - Fee Type: `"registration"`
   - Category Slug: `"postgraduate"`
   - Qualification Type: `"PhD"`
   - Lookup Key: `"postgraduate-phd"`
   - Service Code: `"003486REG2025_PHD"` ✅
6. **Credo Central processes payment** with service code `003486REG2025_PHD`

---

## ⚠️ Common Issues & Solutions

### **Issue 1: "No service code configured" error**
**Cause:** Service code missing in `config/services.php`
**Solution:** Add the specific service code for that category/qualification combination

### **Issue 2: Bio data form shows wrong qualification types**
**Cause:** Alumni's `category_id` is null or incorrect
**Solution:** Admin must assign correct category first

### **Issue 3: Qualification type validation fails**
**Cause:** Alumni selected wrong qualification type for their category
**Solution:** Alumni must update qualification type to match their category

### **Issue 4: Payment fails with service code error**
**Cause:** Missing service code or wrong qualification type
**Solution:** 
1. Check `qualification_type` in database
2. Check `category_id` in database
3. Verify service code exists in `config/services.php`

---

## 📊 Database Relationships

```
alumni_categories (1) ──< (many) alumni
                                │
                                ├── category_id (FK)
                                └── qualification_type (string)

alumni_categories (1) ──< (many) fee_templates
                                │
                                ├── category_id (FK)
                                └── fee_type_id (FK)

transactions
    ├── alumni_id (FK) ──> alumni
    └── fee_template_id (FK) ──> fee_templates
```

---

## 🎓 Summary

1. **Category** = Admin-assigned (Postgraduate, Undergraduate, etc.)
2. **Qualification Type** = Alumni-selected based on category (PhD/MSc/PGD or Degree/Diploma/Certificate)
3. **Service Code** = Automatically determined from Category + Qualification Type + Fee Type
4. **No Fallbacks** = Each combination must have its own service code configured

---

## 🔧 What You Need To Do

1. ✅ **Replace placeholder service codes** in `config/services.php` with actual Credo Central service codes
2. ✅ **Ensure all alumni have categories assigned** via `/admin/alumni-categories/assign`
3. ✅ **Alumni complete bio data** to set their qualification type
4. ✅ **Test payment flow** to verify service codes are resolved correctly

---

## ❓ Questions?

If you're confused about a specific part:
- **Category assignment?** → Check `AlumniCategoryAssignmentController.php`
- **Qualification types?** → Check `AlumniBioDataController.php`
- **Service code lookup?** → Check `CredoCentralService.php`
- **Configuration?** → Check `config/services.php`

