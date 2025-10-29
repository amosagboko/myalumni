# 📖 Understanding "Degree" and "Diploma" Categories

## ⚠️ Important First: "Degree" is NOT a Category!

**"Degree" is a qualification type**, not a category!

---

## 🎓 The Actual Categories

### **Primary Categories:**
1. ✅ **Postgraduate**
2. ✅ **Undergraduate (Full-time)**
3. ✅ **Undergraduate (Part-time)**
4. ✅ **Diploma** (This IS a category)
5. **Alumni Annual Registration** (Subscription)

---

## 📋 UNDERGRADUATE CATEGORIES

### **Category: "Undergraduate (Full-time)"**

#### **Step 1: Admin Assigns Category**
**What Admin Does:**
1. Navigate to `/admin/alumni-categories/assign`
2. Find the alumni
3. Select **"Undergraduate (Full-time)"** from dropdown

**What Happens:**
- ✅ `category_id` = [Undergraduate Full-time category ID]
- ✅ Category slug = `"undergraduate-full-time"`

#### **Step 2: Alumni Completes Bio Data**
**What Alumni Sees in Qualification Type Dropdown:**

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

**Alumni selects ONE:**
- **Degree** (if they have a Degree)
- **Diploma** (if they have a Diploma)
- **Certificate** (if they have a Certificate)

#### **Step 3: Service Code for Payments**
**When Alumni Pays Registration Fee:**

- Category: `"undergraduate-full-time"`
- Qualification Type: `"Degree"` (or Diploma/Certificate) ← **NOT USED in service code lookup**
- Service Code: `"003486REG2025_UGFT"` ← **Uses category slug only**

**Important:** For undergraduate categories, qualification type doesn't affect service codes!

---

### **Category: "Undergraduate (Part-time)"**

#### **Step 1: Admin Assigns Category**
Select **"Undergraduate (Part-time)"** from dropdown

#### **Step 2: Alumni Completes Bio Data**
**Same Qualification Type Options:**
- Degree
- Diploma
- Certificate

#### **Step 3: Service Code for Payments**
- Category: `"undergraduate-part-time"`
- Service Code: `"003486REG2025_UGPT"` ← Different from full-time!

**Why Different Categories?**
- Full-time and Part-time students pay **different amounts**
- They need **different service codes**

---

## 📋 DIPLOMA CATEGORY

### **Category: "Diploma"**

**Note:** "Diploma" appears in TWO places:
1. ✅ As a **category** (assigned by admin)
2. ✅ As a **qualification type** (selected by alumni in bio data)

#### **Step 1: Admin Assigns "Diploma" Category**
**What Admin Does:**
1. Navigate to `/admin/alumni-categories/assign`
2. Find alumni who completed **Diploma programs**
3. Select **"Diploma"** from dropdown

**When to Use:**
- Alumni who completed Diploma programs (not degree programs)
- Different from "Undergraduate (Full-time)" category

#### **Step 2: Alumni Completes Bio Data**
**What Alumni Sees in Qualification Type Dropdown:**

```
┌─────────────────────────────────┐
│ Qualification Type *            │
├─────────────────────────────────┤
│ Select Qualification Type       │
│ ─────────────────────────────── │
│ Degree        ← Option 1        │
│ Diploma       ← Option 2        │ (Most likely)
│ Certificate   ← Option 3        │
└─────────────────────────────────┘
```

**Common Scenario:**
- Category = **"Diploma"**
- Qualification Type = **"Diploma"** (they select it again)

Yes, it seems redundant, but:
- The **category** determines fees and service codes
- The **qualification type** is just descriptive

#### **Step 3: Service Code for Payments**
- Category: `"diploma"`
- Service Code: `"003486REG2025_DIP"` ← Uses category slug only

---

## 📊 Comparison Table

| Category | Qualification Types Shown | Service Code Key | Example Code |
|----------|---------------------------|------------------|--------------|
| **Postgraduate** | PhD, MSc, PGD | `category + qualification_type` | `postgraduate-phd` → `003486REG2025_PHD` |
| **Undergraduate (Full-time)** | Degree, Diploma, Certificate | `category slug only` | `undergraduate-full-time` → `003486REG2025_UGFT` |
| **Undergraduate (Part-time)** | Degree, Diploma, Certificate | `category slug only` | `undergraduate-part-time` → `003486REG2025_UGPT` |
| **Diploma** | Degree, Diploma, Certificate | `category slug only` | `diploma` → `003486REG2025_DIP` |

---

## 🎯 Complete Examples

### **Example 1: Undergraduate Full-time Student with Degree**

**Step 1: Admin Assigns**
- Select: **"Undergraduate (Full-time)"**
- ✅ `category_id` = Undergraduate Full-time

**Step 2: Alumni Bio Data**
- Sees: Degree, Diploma, Certificate
- Selects: **"Degree"**
- ✅ `qualification_type` = "Degree"

**Step 3: Payment**
- Service code: `"003486REG2025_UGFT"` (category-based only)

---

### **Example 2: Diploma Program Student**

**Step 1: Admin Assigns**
- Select: **"Diploma"** category
- ✅ `category_id` = Diploma

**Step 2: Alumni Bio Data**
- Sees: Degree, Diploma, Certificate
- Selects: **"Diploma"**
- ✅ `qualification_type` = "Diploma"

**Step 3: Payment**
- Service code: `"003486REG2025_DIP"` (category-based only)

---

### **Example 3: Undergraduate Part-time Student with Diploma**

**Step 1: Admin Assigns**
- Select: **"Undergraduate (Part-time)"**
- ✅ `category_id` = Undergraduate Part-time

**Step 2: Alumni Bio Data**
- Sees: Degree, Diploma, Certificate
- Selects: **"Diploma"** (they got a Diploma, not a Degree)
- ✅ `qualification_type` = "Diploma"

**Step 3: Payment**
- Service code: `"003486REG2025_UGPT"` (category-based, not qualification-based)

---

## 🔑 Key Takeaways

### **For All Non-Postgraduate Categories:**

1. **Qualification Type is Descriptive Only**
   - Does NOT affect service codes
   - Only the category slug is used

2. **Same Qualification Options**
   - All show: Degree, Diploma, Certificate
   - Alumni selects based on what they actually got

3. **Category Determines Everything**
   - Fees are based on category
   - Service codes are based on category slug
   - Different categories = Different service codes

---

## ❓ FAQ

### **Q: Why is "Diploma" both a category AND a qualification type?**
**A:** 
- **Category "Diploma"** = For alumni who completed Diploma programs
- **Qualification Type "Diploma"** = One option that alumni can select

This allows:
- Someone in "Diploma" category to select "Diploma" as qualification type
- Someone in "Undergraduate" category to select "Diploma" as qualification type (if they got a Diploma, not a Degree)

### **Q: What's the difference between "Undergraduate (Full-time)" and "Diploma" categories?**
**A:**
- **Undergraduate (Full-time)** = For alumni who did undergraduate degree programs full-time
- **Diploma** = For alumni who did Diploma programs specifically

They have different fee structures, hence different categories.

### **Q: Can an Undergraduate student select "Diploma" as qualification type?**
**A:** Yes! If they completed a Diploma program (not a full Degree), they would:
- Category: "Undergraduate (Full-time)" or "Undergraduate (Part-time)"
- Qualification Type: "Diploma"

The qualification type is just descriptive - it doesn't change the service code.

### **Q: Is there a "Degree" category?**
**A:** ❌ **NO!** There is no "Degree" category. The categories are:
- Undergraduate (Full-time)
- Undergraduate (Part-time)
- Diploma

"Degree" is only a qualification type that alumni can select.

---

## ✅ Quick Decision Guide

### **Which Category to Assign?**

| Alumni Completed | Assign Category | Qualification Type |
|------------------|----------------|-------------------|
| PhD Program | **Postgraduate** | PhD |
| MSc Program | **Postgraduate** | MSc |
| PGD Program | **Postgraduate** | PGD |
| Degree Program (Full-time) | **Undergraduate (Full-time)** | Degree (usually) |
| Degree Program (Part-time) | **Undergraduate (Part-time)** | Degree (usually) |
| Diploma Program | **Diploma** | Diploma (usually) |

---

## 🎓 Summary

**Key Point:**
- **Postgraduate** = Special case (uses category + qualification type for service codes)
- **All Other Categories** = Use category slug only (qualification type is descriptive)

For undergraduate and diploma categories, the qualification type is just information - it doesn't affect payments or service codes!

