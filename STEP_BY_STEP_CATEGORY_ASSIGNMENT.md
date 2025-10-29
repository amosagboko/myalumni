# 📘 Step-by-Step: Assigning "Postgraduate (PhD)" to an Alumni

## 🎯 The Big Picture

**There is NO "Postgraduate (PhD)" category!**

There is **ONE** category called **"Postgraduate"** that ALL postgraduate students get assigned to.

The PhD/MSc/PGD distinction happens **later** when the alumni fills out their bio data form.

---

## 📋 STEP-BY-STEP PROCESS

### **STEP 1: Admin Assigns "Postgraduate" Category**

**What Admin Does:**

1. **Log in** as Administrator
2. **Navigate to:** `/admin/alumni-categories/assign`
   - Or click "Assign Categories" in the menu
3. **Find the alumni:**
   - Use Search box: Type name or matric number
   - OR use Filters: Faculty, Year, etc.
4. **Look at the rightmost column** labeled "Actions"
   - You'll see a dropdown: `[Select Category ▼]`
5. **Click the dropdown** for that alumni row
6. **Select "Postgraduate"** from the list

**What Happens:**
- ✅ System saves: `category_id = [Postgraduate category ID]`
- ✅ The "Current Category" column now shows a blue badge: **"Postgraduate"**
- ✅ **That's it!** Category assignment is complete.

---

### **STEP 2: Alumni Logs In**

**What Happens:**
- Alumni logs in to their account
- If bio data is incomplete → They're redirected to `/alumni/bio-data`
- If bio data is complete → They go to `/alumni/home`

---

### **STEP 3: Alumni Sees Bio Data Form**

**Location:** `/alumni/bio-data`

**What the System Does Behind the Scenes:**

1. **Loads the alumni record**
2. **Checks:** `$alumni->category->slug === 'postgraduate'`
3. **If YES → Sets qualification options to:** `['PhD', 'MSc', 'PGD']`
4. **If NO → Sets qualification options to:** `['Degree', 'Diploma', 'Certificate']`

---

### **STEP 4: Alumni Sees Qualification Type Field**

**If Alumni Category = "Postgraduate":**

The dropdown will show:

```
┌─────────────────────────────────┐
│ Qualification Type *            │
├─────────────────────────────────┤
│ Select Qualification Type      │ ← Default option
│ ─────────────────────────────── │
│ PhD                             │ ← Option 1
│ MSc                             │ ← Option 2
│ PGD                             │ ← Option 3
└─────────────────────────────────┘
```

**The alumni MUST select one of these three:**
- ✅ **PhD** (if they did a PhD)
- ✅ **MSc** (if they did a Master's)
- ✅ **PGD** (if they did a Postgraduate Diploma)

---

### **STEP 5: Alumni Selects "PhD"**

**Example: PhD Student**

1. **Alumni clicks** the Qualification Type dropdown
2. **Sees:** PhD, MSc, PGD
3. **Selects:** "PhD"
4. **Fills in** other required fields
5. **Clicks:** "Save and Continue"

**What Gets Saved:**

```
Database: alumni table
├── category_id = [Postgraduate category ID]  ← From Step 1
└── qualification_type = "PhD"                 ← From Step 5
```

---

### **STEP 6: System Combines Both for Payments**

**When Alumni Pays Registration Fee:**

**System Logic:**
```php
Category Slug:      "postgraduate"  (from category)
Qualification Type: "PhD"           (from alumni record)
                          ↓
          Combined Key: "postgraduate-phd"
                          ↓
Service Code: "003486REG2025_PHD"  (from config/services.php)
```

---

## 🎓 Visual Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│ STEP 1: ADMIN ASSIGNS CATEGORY                               │
├─────────────────────────────────────────────────────────────┤
│ Admin → Assign Categories Page                               │
│          ↓                                                    │
│       Find Alumni                                             │
│          ↓                                                    │
│    Select "Postgraduate" from dropdown                       │
│          ↓                                                    │
│    ✅ category_id = Postgraduate                             │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 2: ALUMNI LOGS IN                                       │
├─────────────────────────────────────────────────────────────┤
│ Alumni → Logs in                                              │
│          ↓                                                    │
│    Redirected to /alumni/bio-data                            │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 3: ALUMNI SEES BIO DATA FORM                            │
├─────────────────────────────────────────────────────────────┤
│ System checks: category = "Postgraduate"                     │
│          ↓                                                    │
│ Qualification Type dropdown shows:                            │
│    • PhD                                                      │
│    • MSc                                                      │
│    • PGD                                                      │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 4: ALUMNI SELECTS QUALIFICATION                         │
├─────────────────────────────────────────────────────────────┤
│ Alumni selects: "PhD"                                         │
│          ↓                                                    │
│    Clicks "Save and Continue"                                 │
│          ↓                                                    │
│    ✅ qualification_type = "PhD"                             │
└─────────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 5: PAYMENT TIME                                         │
├─────────────────────────────────────────────────────────────┤
│ System combines:                                              │
│    "postgraduate" + "PhD" = "postgraduate-phd"              │
│          ↓                                                    │
│    Looks up service code: "003486REG2025_PHD"                │
│          ↓                                                    │
│    ✅ Payment processed with correct code                     │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔍 What Each Person Sees

### **Admin Sees:**
```
Assign Categories Page:
┌──────────────────────────────────────────────────────────────┐
│ Name │ Matric │ Faculty │ Year │ Category │ Actions           │
├──────────────────────────────────────────────────────────────┤
│ John │ MAT001 │ Science │ 2025 │ Unassigned │ [Select ▼]    │
│                                                              │
│ Click dropdown → Select "Postgraduate"                      │
│                                                              │
│ After selection:                                             │
│ John │ MAT001 │ Science │ 2025 │ [Postgraduate] │ [Postgraduate ▼] │
└──────────────────────────────────────────────────────────────┘
```

### **Alumni (PhD Student) Sees:**

```
Bio Data Form:
┌──────────────────────────────────────────────────────────────┐
│ Qualification Type *                                         │
├──────────────────────────────────────────────────────────────┤
│ [Select Qualification Type ▼]                                │
│   • PhD        ← Select this                                 │
│   • MSc                                                       │
│   • PGD                                                       │
└──────────────────────────────────────────────────────────────┘
```

### **Alumni (Undergraduate) Sees:**

```
Bio Data Form:
┌──────────────────────────────────────────────────────────────┐
│ Qualification Type *                                         │
├──────────────────────────────────────────────────────────────┤
│ [Select Qualification Type ▼]                                │
│   • Degree      ← Different options!                         │
│   • Diploma                                                   │
│   • Certificate                                               │
└──────────────────────────────────────────────────────────────┘
```

---

## ✅ Quick Reference

| Item | Value | Where Set | Who Sets It |
|------|-------|-----------|-------------|
| **Category** | "Postgraduate" | `/admin/alumni-categories/assign` | **Admin** |
| **Qualification Type** | "PhD", "MSc", or "PGD" | `/alumni/bio-data` | **Alumni** |
| **Service Code** | `"003486REG2025_PHD"` | Calculated automatically | **System** |

---

## ❓ FAQ

### **Q: Do I create separate categories for PhD, MSc, PGD?**
**A:** ❌ NO! There is **ONE** "Postgraduate" category. All postgraduate students get this category.

### **Q: How does the system know if someone is PhD vs MSc?**
**A:** The alumni selects it in their bio data form. The dropdown shows PhD/MSc/PGD options for Postgraduate category.

### **Q: What if an alumni selects the wrong qualification type?**
**A:** They can update it later by visiting `/alumni/bio-data` again. The dropdown will still show the correct options based on their category.

### **Q: Can I assign "Postgraduate" to multiple alumni at once?**
**A:** Yes! Use the "Bulk Assign" feature:
1. Check the checkboxes for multiple alumni
2. Select "Postgraduate" in the bulk category dropdown
3. Click "Bulk Assign"

---

## 🎯 Summary

1. **Admin assigns:** ONE category → "Postgraduate"
2. **Alumni selects:** Their specific type → "PhD", "MSc", or "PGD"
3. **System combines:** Both → Creates service code lookup key
4. **Payment uses:** The combined key → Finds correct service code

**The key point:** 
- **Category** = Broad classification (Postgraduate vs Undergraduate)
- **Qualification Type** = Specific program (PhD vs MSc vs PGD)

