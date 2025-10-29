# Step-by-Step: Assigning Postgraduate Category to Alumni

## 🎯 Complete Flow Overview

This guide explains the **exact steps** to assign a "Postgraduate" category to an alumni and what happens next.

---

## 📋 STEP 1: Admin Assigns "Postgraduate" Category

### What Admin Does:
1. Log in as **Administrator**
2. Navigate to: `/admin/alumni-categories/assign`
3. Find the alumni (using search or filters)
4. In the **Actions** column (rightmost), click the dropdown
5. Select **"Postgraduate"** from the list

### What Happens Behind the Scenes:
- The system saves: `alumni.category_id = [ID of Postgraduate category]`
- **The alumni record now has category = "Postgraduate"**

### Important Notes:
- ✅ There is **ONE** category called "Postgraduate" (not separate PhD/MSc/PGD categories)
- ✅ You assign "Postgraduate" to **ALL** postgraduate students (PhD, MSc, and PGD)
- ✅ The system does NOT create separate categories for PhD, MSc, or PGD

---

## 📋 STEP 2: Alumni Logs In

When the alumni logs in after category assignment:

### What Alumni Sees:
1. If bio data is incomplete → Redirected to `/alumni/bio-data`
2. If bio data is complete → Goes to `/alumni/home`

---

## 📋 STEP 3: Alumni Completes Bio Data

### When Alumni Visits `/alumni/bio-data`:

The system checks:
- ✅ What is the alumni's `category_id`? → **"Postgraduate"**
- ✅ What is the category slug? → **"postgraduate"**

### Qualification Type Dropdown Shows:

**For Postgraduate Alumni:**
- ✅ **PhD** (Option 1)
- ✅ **MSc** (Option 2)
- ✅ **PGD** (Option 3)

**NOT these options:**
- ❌ Degree
- ❌ Diploma
- ❌ Certificate

### Example Scenario:

**Alumni: John Doe**
- Category: **Postgraduate** ✅
- Qualification Type Dropdown Shows:
  ```
  [Dropdown]
  Select Qualification Type
  ─────────────────────
  PhD      ← Select this for PhD students
  MSc      ← Select this for MSc students
  PGD      ← Select this for PGD students
  ```

**Alumni: Jane Smith (Non-Postgraduate)**
- Category: **Undergraduate (Full-time)** ✅
- Qualification Type Dropdown Shows:
  ```
  [Dropdown]
  Select Qualification Type
  ─────────────────────
  Degree
  Diploma
  Certificate
  ```

---

## 📋 STEP 4: Alumni Selects Qualification Type

### Example: PhD Student

1. **Alumni selects:** `PhD` from the dropdown
2. **System stores:** `alumni.qualification_type = "PhD"`
3. **Complete other required fields**
4. **Click "Save and Continue"**

### After Saving:

**Database has:**
```
alumni record:
  - category_id = [Postgraduate category ID]
  - qualification_type = "PhD"
```

---

## 📋 STEP 5: How System Uses This Information

### When Alumni Makes a Payment:

**Example: Registration Fee Payment**

1. **System checks:**
   - Fee Type: `"registration"`
   - Category Slug: `"postgraduate"` (from category)
   - Qualification Type: `"PhD"` (from alumni record)

2. **System creates lookup key:**
   ```
   "postgraduate" + "-" + "PhD" = "postgraduate-phd"
   ```

3. **System looks up in `config/services.php`:**
   ```php
   'registration' => [
       'postgraduate-phd' => '003486REG2025_PHD',  ← Uses this code
       'postgraduate-msc' => '003486REG2025_MSC',
       'postgraduate-pgd' => '003486REG2025_PGD',
   ]
   ```

4. **Result:** Service code `003486REG2025_PHD` is sent to Credo Central

---

## 🔑 Key Points to Remember

### ✅ Category Assignment (Admin):
- **ONE** category: **"Postgraduate"**
- Assign this to **ALL** postgraduate students
- **NO** separate categories for PhD, MSc, PGD

### ✅ Qualification Type Selection (Alumni):
- Alumni selects their **specific program**: PhD, MSc, or PGD
- This is stored separately in `qualification_type` field
- **Different alumni in same category can have different qualification types**

### ✅ Service Code Lookup:
- Combines: `category_slug` + `qualification_type`
- Creates: `"postgraduate-phd"`, `"postgraduate-msc"`, `"postgraduate-pgd"`
- Each has its **own unique service code**

---

## 📊 Complete Example: PhD Alumni Journey

### Step 1: Admin Assignment
```
Admin → Assign Categories → John Doe → Select "Postgraduate"
✅ Saved: category_id = Postgraduate
```

### Step 2: Alumni Bio Data Form
```
John Doe logs in → /alumni/bio-data
Qualification Type dropdown shows:
  - PhD
  - MSc
  - PGD
```

### Step 3: Alumni Selection
```
John Doe selects: "PhD"
✅ Saved: qualification_type = "PhD"
```

### Step 4: Payment Time
```
John Doe pays Registration Fee
System combines:
  - Category: "postgraduate"
  - Qualification: "PhD"
  - Creates key: "postgraduate-phd"
  - Uses service code: "003486REG2025_PHD"
```

---

## 🎓 Summary Table

| Step | Who | Action | Result |
|------|-----|--------|--------|
| 1 | Admin | Assign "Postgraduate" category | `category_id` = Postgraduate |
| 2 | Alumni | Logs in | Redirected to bio data form |
| 3 | System | Checks category | Shows PhD/MSc/PGD options |
| 4 | Alumni | Selects "PhD" | `qualification_type` = "PhD" |
| 5 | System | Payment lookup | Uses "postgraduate-phd" → service code |

---

## ❓ Common Questions

### Q: Why not create separate categories for PhD, MSc, PGD?
**A:** Because they share the same fee structure and rules. The distinction is only needed for service codes at payment time.

### Q: What if an alumni selects wrong qualification type?
**A:** They can update it later in the bio data form. The dropdown will always show PhD/MSc/PGD for Postgraduate category.

### Q: Can I have PhD and MSc alumni both assigned to "Postgraduate" category?
**A:** Yes! ✅ They both have `category = "Postgraduate"`, but different `qualification_type` values ("PhD" vs "MSc").

### Q: What if alumni hasn't completed bio data yet?
**A:** The qualification type will be empty/null. They must complete bio data to select PhD/MSc/PGD. Payment will fail until qualification type is set.

---

## ✅ Checklist for Admin

- [ ] Category "Postgraduate" exists in database
- [ ] Alumni assigned to "Postgraduate" category
- [ ] Alumni logs in and sees bio data form
- [ ] Qualification type dropdown shows: PhD, MSc, PGD
- [ ] Alumni selects their qualification type
- [ ] System stores both: category + qualification_type

---

## 🐛 Troubleshooting

### Problem: Alumni sees "Degree, Diploma, Certificate" options
**Cause:** Alumni is not assigned to "Postgraduate" category
**Solution:** Admin must assign "Postgraduate" category first

### Problem: Alumni sees no options in dropdown
**Cause:** Category is null or invalid
**Solution:** Check `category_id` in database, reassign category

### Problem: Payment fails with "service code not found"
**Cause:** Missing qualification_type or wrong category
**Solution:** Verify both `category_id` and `qualification_type` are set correctly

