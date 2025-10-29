# Step-by-Step: Assign a Single Alumni to Postgraduate Category

## 🎯 Important Note First

**There is NO separate "Postgraduate (PhD)" category.** Here's how it works:

1. **Category Assignment (Admin)** → Assign "Postgraduate" category (this is what you do now)
2. **Qualification Type Selection (Alumni)** → Alumni selects "PhD" when completing bio data later

The system combines:
- **Category** = "Postgraduate" 
- **Qualification Type** = "PhD" (selected by alumni later)
= **Result** = Uses service code for "postgraduate-phd"

---

## 📋 Step-by-Step Instructions

### **STEP 1: Navigate to Assign Categories Page**

1. Log in as **Administrator**
2. Go to the menu and click **"Assign Categories"** or navigate directly to:
   ```
   /admin/alumni-categories/assign
   ```

### **STEP 2: Find the Alumni**

You'll see a table with columns:
- ☑️ Checkbox (for bulk selection)
- Name
- Matric
- Faculty
- Graduation Year
- **Current Category** (will show "Unassigned" if no category)
- **Actions** (this is where you assign!)

**To find your alumni:**
- **Option A:** Use the **Search** box at the top to search by name, email, or matric number
- **Option B:** Use **Filters**:
  - Select **Faculty** from the dropdown
  - Select **Graduation Year** from the dropdown
  - Select **Category** = "Unassigned" to see only alumni without categories
- **Option C:** Scroll through the table (it's paginated, 20 per page)

### **STEP 3: Locate the Actions Column**

Look at the rightmost column labeled **"Actions"**. You'll see a dropdown that says:
```
[Select Category ▼]
```

### **STEP 4: Select the Category**

1. Click on the **dropdown** in the "Actions" column for your alumni row
2. You'll see a list of categories:
   - Select Category
   - Postgraduate  ← **SELECT THIS ONE**
   - Undergraduate (Full-time)
   - Undergraduate (Part-time)
   - Diploma
   - Alumni Annual Registration (Subscription)

3. Click on **"Postgraduate"**

### **STEP 5: Wait for Auto-Save**

- The page will **automatically save** (no need to click a button!)
- You'll see a brief loading indicator
- The page will **automatically reload** when done

### **STEP 6: Verify the Assignment**

After the page reloads, check:

1. **Current Category** column should now show:
   - A **blue badge** saying **"Postgraduate"** (instead of gray "Unassigned")

2. **Actions** column dropdown should now show:
   - **"Postgraduate"** is selected (instead of "Select Category")

### **STEP 7: What Happens Next**

✅ **Category is assigned!** 

**Next Steps:**
1. When the alumni logs in, they'll be required to complete bio data
2. On the bio data form, they'll see qualification type options: **PhD**, **MSc**, **PGD**
3. They select **"PhD"** (or MSc/PGD depending on their program)
4. System stores both:
   - `category_id` = Postgraduate
   - `qualification_type` = "PhD"
5. When they make a payment, system uses: `postgraduate-phd` → service code lookup

---

## 🔍 Troubleshooting

### **Problem: Dropdown doesn't appear or is empty**
- **Check:** Are categories created? Check `/admin/categories` page
- **Solution:** Run the seeder or create categories manually

### **Problem: Page doesn't reload after selection**
- **Check:** Browser console for errors (F12 → Console tab)
- **Check:** Network tab for failed API calls
- **Solution:** Check server logs for errors

### **Problem: "Category assigned successfully" but no change**
- **Check:** Browser cache - try refreshing (Ctrl+F5 or Cmd+Shift+R)
- **Check:** Database - verify `category_id` was updated in `alumni` table

### **Problem: Cannot find the alumni**
- **Use filters:** Set Category = "Unassigned"
- **Use search:** Enter name or matric number
- **Check pagination:** Alumni might be on another page

---

## 📸 Visual Guide

```
┌─────────────────────────────────────────────────────────────┐
│ Assign Categories                                            │
├─────────────────────────────────────────────────────────────┤
│ [Search: ___________] [Faculty: ▼] [Year: ▼] [Category: ▼]  │
├─────────────────────────────────────────────────────────────┤
│ ☑️ Select All | [Category ▼] [Bulk Assign] | 0 selected    │
├─────────────────────────────────────────────────────────────┤
│ ☑️ │ Name         │ Matric │ Faculty │ Year │ Category │ Actions│
├────┼──────────────┼────────┼─────────┼──────┼──────────┼────────┤
│ ☑️ │ John Doe     │ MAT001 │ Science │ 2025 │ Unassigned│ [Select Category ▼]│ ← CLICK HERE
└────┴──────────────┴────────┴─────────┴──────┴──────────┴────────┘
                                                                ↑
                                              Click dropdown → Select "Postgraduate"
```

---

## ✅ Quick Checklist

- [ ] Logged in as Administrator
- [ ] Navigated to `/admin/alumni-categories/assign`
- [ ] Found the alumni (using search or filters)
- [ ] Located "Actions" column dropdown
- [ ] Selected "Postgraduate" from dropdown
- [ ] Page reloaded automatically
- [ ] Verified "Current Category" shows "Postgraduate" badge
- [ ] ✅ Category assignment complete!

---

## 🎓 Understanding the System

**Remember:**
- **"Postgraduate"** is the **category** (assigned by admin)
- **"PhD"** is the **qualification type** (selected by alumni in bio data)
- Both are needed to determine the correct service code: `postgraduate-phd`

**The flow is:**
1. Admin assigns category → ✅ "Postgraduate"
2. Alumni completes bio data → ✅ Selects "PhD"
3. System combines both → ✅ Uses `postgraduate-phd` service code

