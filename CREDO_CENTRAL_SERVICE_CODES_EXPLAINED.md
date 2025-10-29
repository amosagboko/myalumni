# 🔑 Credo Central Service Codes - How They Work

## ⚠️ Important Clarification

The service codes currently in `config/services.php` are **PLACEHOLDERS** that need to be replaced with **actual Credo Central service codes**.

---

## 🎯 How Service Code Lookup Works

### **The System Uses Category Slug as KEY**

```
Category Slug (KEY)     →    Service Code (VALUE - from Credo Central)
─────────────────────────────────────────────────────────────────────
'undergraduate-full-time'  →  [Whatever Credo Central gave you]
```

**The VALUE can be ANY format Credo Central provides:**
- It doesn't need to have `_UGFT` suffix
- It doesn't need to match any pattern
- It's just the code Credo Central assigns to that category/fee combination

---

## 📋 How the Lookup Works

### **Example 1: Undergraduate Full-time Registration Fee**

**Step 1: System Determines Category**
```
Category Slug: "undergraduate-full-time"  (from alumni.category)
```

**Step 2: System Looks Up Service Code**
```php
// In config/services.php
'registration' => [
    'undergraduate-full-time' => '[YOUR_ACTUAL_CREDO_CODE]',  ← System finds this
]

// The KEY 'undergraduate-full-time' is what matters
// The VALUE can be anything Credo Central provided
```

**Step 3: System Uses the Value**
```
Service Code = [Whatever you put as the value]
```

---

## ✅ What You Need to Do

### **Replace Placeholder Values with Actual Codes**

**Current (Placeholders):**
```php
'registration' => [
    'undergraduate-full-time' => '003486REG2025_UGFT',  ← PLACEHOLDER
],
```

**What You Need (Actual Credo Central Codes):**
```php
'registration' => [
    'undergraduate-full-time' => 'ABC123XYZ',  ← Actual code from Credo Central
],
```

**Or it could be:**
```php
'registration' => [
    'undergraduate-full-time' => 'REG-UG-FT-001',  ← Different format from Credo Central
],
```

**Or:**
```php
'registration' => [
    'undergraduate-full-time' => '003486REG2025',  ← No suffix needed
],
```

---

## 📊 Complete Example: How to Set Up

### **Step 1: Get Service Codes from Credo Central**

Contact Credo Central and get service codes for:
- Registration Fee for Undergraduate (Full-time)
- Registration Fee for Undergraduate (Part-time)
- Registration Fee for Diploma
- Registration Fee for Postgraduate (PhD)
- Registration Fee for Postgraduate (MSc)
- Registration Fee for Postgraduate (PGD)
- Development Levy for each category
- Data Processing for each category
- Tech Support for each category

### **Step 2: Map Codes to Category Slugs**

**Credo Central might give you something like:**
```
Registration Fee - Undergraduate Full-time: REG-001
Registration Fee - Undergraduate Part-time: REG-002
Registration Fee - Diploma: REG-003
Registration Fee - Postgraduate PhD: REG-PHD-001
Registration Fee - Postgraduate MSc: REG-MSC-001
Registration Fee - Postgraduate PGD: REG-PGD-001
```

### **Step 3: Update config/services.php**

```php
'registration' => [
    // Use category slug as KEY
    'undergraduate-full-time' => 'REG-001',  ← Actual code from Credo Central
    'undergraduate-part-time' => 'REG-002',  ← Actual code from Credo Central
    'diploma' => 'REG-003',                  ← Actual code from Credo Central
    'postgraduate-phd' => 'REG-PHD-001',     ← Actual code from Credo Central
    'postgraduate-msc' => 'REG-MSC-001',     ← Actual code from Credo Central
    'postgraduate-pgd' => 'REG-PGD-001',     ← Actual code from Credo Central
],
```

---

## 🔑 Key Points

### **1. The KEY Must Match Category Slug**
✅ `'undergraduate-full-time'` - This must match exactly
❌ `'undergraduate_full_time'` - Wrong (uses underscore instead of hyphen)
❌ `'UGFT'` - Wrong (not the category slug)

### **2. The VALUE Can Be Anything**
✅ `'REG-001'` - Any format from Credo Central
✅ `'ABC123'` - Any format
✅ `'003486REG2025'` - Any format
✅ `'some-random-code-xyz'` - As long as Credo Central recognizes it

### **3. The System Doesn't Care About VALUE Format**
- The system just takes whatever value you put
- It sends that value to Credo Central API
- Credo Central processes it based on their own database

---

## 📋 Configuration Structure

```php
'service_codes' => [
    'registration' => [
        // KEY = Category slug (system uses this to find the code)
        // VALUE = Actual Credo Central service code (can be any format)
        'postgraduate-phd' => '[ACTUAL_CODE_FROM_CREDO]',
        'postgraduate-msc' => '[ACTUAL_CODE_FROM_CREDO]',
        'postgraduate-pgd' => '[ACTUAL_CODE_FROM_CREDO]',
        'undergraduate-full-time' => '[ACTUAL_CODE_FROM_CREDO]',
        'undergraduate-part-time' => '[ACTUAL_CODE_FROM_CREDO]',
        'diploma' => '[ACTUAL_CODE_FROM_CREDO]',
    ],
    'development_levy' => [
        // Same structure for each fee type
        'postgraduate-phd' => '[ACTUAL_CODE_FROM_CREDO]',
        // ... etc
    ],
]
```

---

## 🎯 Summary

1. **KEY = Category Slug** (e.g., `'undergraduate-full-time'`)
   - This is what the system searches for
   - Must match exactly

2. **VALUE = Credo Central Service Code** (e.g., `'REG-001'` or `'ABC123'`)
   - This can be any format Credo Central provides
   - No pattern required
   - Just the code they gave you

3. **Replace All Placeholders**
   - Find all `'REPLACE_WITH_ACTUAL_CREDO_CODE...'` entries
   - Replace with actual codes from Credo Central
   - The format doesn't matter - as long as Credo Central recognizes it

---

## ❓ FAQ

### **Q: The Credo Central code doesn't have "_UGFT" suffix - is that OK?**
**A:** ✅ **YES!** The value can be ANY format. The system only cares about finding the KEY (category slug). The value is sent directly to Credo Central.

### **Q: How do I know which code to use for which category?**
**A:** Map them based on what Credo Central tells you:
- "This code is for Undergraduate Full-time Registration" → Use with `'undergraduate-full-time'` key
- "This code is for Postgraduate PhD Registration" → Use with `'postgraduate-phd'` key

### **Q: What if Credo Central gives me codes with different naming?**
**A:** That's fine! Just map them correctly:
```php
'undergraduate-full-time' => 'REG-001',     // ← Whatever Credo Central gave you
'undergraduate-part-time' => 'REG-002',     // ← Different code
'diploma' => 'REG-003',                     // ← Another code
```

The system doesn't care about the format - it just needs the correct mapping.

