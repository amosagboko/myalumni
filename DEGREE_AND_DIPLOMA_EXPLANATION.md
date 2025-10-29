# 📚 Categories: Degree and Diploma Explained

## ⚠️ Important Clarification

**"Degree" is NOT a category!** It's a **qualification type**.

The actual categories are:
1. **Postgraduate**
2. **Undergraduate (Full-time)**
3. **Undergraduate (Part-time)**
4. **Diploma** (This IS a category)
5. **Alumni Annual Registration** (Subscription)

---

## 🎓 Category Structure

### **Category vs Qualification Type**

| Category (Assigned by Admin) | Qualification Types (Selected by Alumni) |
|------------------------------|------------------------------------------|
| **Postgraduate** | PhD, MSc, PGD |
| **Undergraduate (Full-time)** | Degree, Diploma, Certificate |
| **Undergraduate (Part-time)** | Degree, Diploma, Certificate |
| **Diploma** (Category) | Degree, Diploma, Certificate |
| **Alumni Annual Registration** | Degree, Diploma, Certificate |

---

## 📋 CATEGORY 1: Undergraduate (Full-time) and Undergraduate (Part-time)

### **How These Work:**

These are **separate categories** because they have **different fee structures**.

### **Step 1: Admin Assigns Category**

**What Admin Does:**
1. Go to `/admin/alumni-categories/assign`
2. Find the alumni
3. Select from dropdown:
   - **"Undergraduate (Full-time)"** OR
   - **"Undergraduate (Part-time)"**

**What Happens:**
- ✅ System saves: `category_id = [Undergraduate Full-time OR Part-time category ID]`
- ✅ Different categories = Different service codes for payments

### **Step 2: Alumni Completes Bio Data**

**What Alumni Sees:**

**If Category = "Undergraduate (Full-time)" or "Undergraduate (Part-time)":**

```
┌─────────────────────────────────┐
│ Qualification Type *            │
├─────────────────────────────────┤
│ Select Qualification Type       │
│ ─────────────────────────────── │
│ Degree        ← Option 1        │
│ Diploma       ← Option 2        │
│ Certificate   ← Option 3        │
└─────────────────────────────────┘
```

**Alumni must select ONE:**
- ✅ **Degree** (if they got a Degree)
- ✅ **Diploma** (if they got a Diploma)
- ✅ **Certificate** (if they got a Certificate)

### **Step 3: Service Code Lookup**

**For Undergraduate (Full-time) + Degree:**

```
Category Slug:      "undergraduate-full-time"  (from category - used as KEY)
Qualification Type: "Degree"                    (from alumni record - NOT used for lookup!)
                          ↓
System looks up in config/services.php:
  'undergraduate-full-time' => '[ACTUAL_CREDO_CENTRAL_SERVICE_CODE]'
                          ↓
Service Code: [Whatever Credo Central gave you]  (uses category slug as lookup key)
```

**Important Points:**
- ✅ The **KEY** is the category slug: `'undergraduate-full-time'`
- ✅ The **VALUE** is whatever Credo Central service code you were given
- ✅ The value doesn't need to match any pattern - it's just a code Credo Central provides
- ✅ For undergraduate categories, qualification type is **NOT** used in service code lookup

**Example:**
```php
// In config/services.php
'registration' => [
    'undergraduate-full-time' => 'REG001',  // ← Replace with actual Credo Central code
    'undergraduate-part-time' => 'REG002',  // ← Replace with actual Credo Central code
    'diploma' => 'REG003',                  // ← Replace with actual Credo Central code
],
```

**Note:** The codes like `003486REG2025_UGFT` shown in examples are **PLACEHOLDERS**. Replace them with the actual service codes Credo Central provides!

---

## 📋 CATEGORY 2: Diploma (Category)

### **How This Works:**

**"Diploma" is BOTH:**
- A **category** (assigned by admin)
- A **qualification type** (selected by alumni in other categories)

### **Step 1: Admin Assigns "Diploma" Category**

**What Admin Does:**
1. Go to `/admin/alumni-categories/assign`
2. Find the alumni (who completed a Diploma program)
3. Select **"Diploma"** from dropdown

**What Happens:**
- ✅ System saves: `category_id = [Diploma category ID]`
- ✅ This is for alumni who completed **Diploma programs** (not degree programs)

### **Step 2: Alumni Completes Bio Data**

**What Alumni Sees:**

**If Category = "Diploma":**

```
┌─────────────────────────────────┐
│ Qualification Type *            │
├─────────────────────────────────┤
│ Select Qualification Type       │
│ ─────────────────────────────── │
│ Degree        ← Option 1        │
│ Diploma       ← Option 2        │
│ Certificate   ← Option 3        │
└─────────────────────────────────┘
```

**Alumni must select ONE:**
- ✅ **Degree** (rare for Diploma category)
- ✅ **Diploma** (most common - they select "Diploma" again)
- ✅ **Certificate**

**Note:** Yes, this seems redundant, but it's how the system works. The category is "Diploma" and they can also select "Diploma" as qualification type.

### **Step 3: Service Code Lookup**

**For Diploma Category:**

```
Category Slug:      "diploma"       (from category)
Qualification Type: "Diploma"        (from alumni record - NOT used for lookup!)
                          ↓
Service Code: "003486REG2025_DIP"   (uses category slug directly)
```

**Same as undergraduate:** Qualification type is **NOT** used in service code lookup for Diploma category.

---

## 🔍 Key Differences

### **Postgraduate vs Others:**

| Aspect | Postgraduate | Undergraduate/Diploma |
|--------|--------------|----------------------|
| **Qualification Types** | PhD, MSc, PGD | Degree, Diploma, Certificate |
| **Service Code Uses** | Category + Qualification Type | Category only |
| **Example Service Code** | `postgraduate-phd` | `undergraduate-full-time` |
| **Complexity** | Higher (combines both) | Simpler (category only) |

---

## 📊 Complete Examples

### **Example 1: Undergraduate Full-time (Degree)**

**Step 1:** Admin assigns → **"Undergraduate (Full-time)"**
```
category_id = [Undergraduate Full-time ID]
```

**Step 2:** Alumni sees bio data → Qualification Type dropdown shows:
```
- Degree
- Diploma
- Certificate
```

**Step 3:** Alumni selects → **"Degree"**
```
qualification_type = "Degree"
```

**Step 4:** Payment time → Service code:
```
Category: "undergraduate-full-time"
Qualification: "Degree" (not used in lookup)
Result: Service code = "003486REG2025_UGFT"
```

---

### **Example 2: Diploma Category**

**Step 1:** Admin assigns → **"Diploma"**
```
category_id = [Diploma category ID]
```

**Step 2:** Alumni sees bio data → Qualification Type dropdown shows:
```
- Degree
- Diploma
- Certificate
```

**Step 3:** Alumni selects → **"Diploma"**
```
qualification_type = "Diploma"
```

**Step 4:** Payment time → Service code:
```
Category: "diploma"
Qualification: "Diploma" (not used in lookup)
Result: Service code = "003486REG2025_DIP"
```

---

## 🎯 Summary Table

| Category | Qualification Types Shown | How Service Code Works |
|----------|---------------------------|------------------------|
| **Postgraduate** | PhD, MSc, PGD | Uses: `category + qualification_type` → `postgraduate-phd` |
| **Undergraduate (Full-time)** | Degree, Diploma, Certificate | Uses: `category slug only` → `undergraduate-full-time` |
| **Undergraduate (Part-time)** | Degree, Diploma, Certificate | Uses: `category slug only` → `undergraduate-part-time` |
| **Diploma** | Degree, Diploma, Certificate | Uses: `category slug only` → `diploma` |

---

## ❓ Common Questions

### **Q: Why does "Diploma" appear both as category and qualification type?**
**A:** 
- **Diploma (Category)** = For alumni who completed Diploma programs
- **Diploma (Qualification Type)** = One of the qualification types that alumni can select

Yes, it's a bit confusing, but:
- The **category** determines fees and service codes
- The **qualification type** is just descriptive information

### **Q: What if an Undergraduate student selects "Diploma" as qualification type?**
**A:** It's fine! The qualification type doesn't affect payments for undergraduate categories. The system only uses the category slug for service code lookup.

### **Q: Is "Degree" a category?**
**A:** ❌ NO! "Degree" is **only** a qualification type. There is no "Degree" category. The closest categories are:
- **Undergraduate (Full-time)**
- **Undergraduate (Part-time)**

### **Q: Why separate Full-time and Part-time categories?**
**A:** They have **different fee structures**, so they need different service codes:
- Full-time → `003486REG2025_UGFT`
- Part-time → `003486REG2025_UGPT`

---

## ✅ Quick Reference

### **For Undergraduate Students:**
1. Admin assigns: **"Undergraduate (Full-time)"** or **"Undergraduate (Part-time)"**
2. Alumni selects qualification type: **Degree, Diploma, or Certificate**
3. Service code: Based on category only (`undergraduate-full-time` or `undergraduate-part-time`)

### **For Diploma Program Students:**
1. Admin assigns: **"Diploma"** category
2. Alumni selects qualification type: **Degree, Diploma, or Certificate**
3. Service code: Based on category only (`diploma`)

### **For Postgraduate Students:**
1. Admin assigns: **"Postgraduate"** category
2. Alumni selects qualification type: **PhD, MSc, or PGD**
3. Service code: Based on category + qualification type (`postgraduate-phd`, `postgraduate-msc`, `postgraduate-pgd`)

