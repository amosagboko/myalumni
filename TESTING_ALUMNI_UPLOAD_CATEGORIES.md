# 🧪 Testing Alumni Upload with Category Assignment

## ✅ Pre-Testing Checklist

Before testing, ensure these categories exist in your database:
- ✅ Postgraduate
- ✅ Undergraduate (Full-time)
- ✅ Undergraduate (Part-time)
- ✅ Diploma

You can verify this by:
1. Going to `/admin/alumni-categories`
2. Or running: `php artisan tinker` → `AlumniCategory::all()->pluck('name')`

---

## 📋 Test 1: Basic Category Assignment

### **Step 1: Create Test CSV**

Create a file `test_alumni_upload.csv` with this content:

```csv
firstname,surname,matriculation_id,programme,department,faculty,year_of_graduation,category,date_of_birth,state,lga,year_of_entry,gender
John,Doe,1011700001,Computer Science,Computer Science,Science,2020,Postgraduate,1995-05-15,Lagos,Lagos Mainland,2016,Male
Jane,Smith,1011700002,Mathematics,Mathematics,Science,2019,Undergraduate (Full-time),1996-08-20,Abuja,Abuja Municipal,2015,Female
Mike,Johnson,1011700003,Business Admin,Business Admin,Management,2021,Undergraduate (Part-time),1994-03-10,Kano,Kano Municipal,2014,Male
Sarah,Williams,1011700004,Engineering,Electrical Engineering,Engineering,2018,Diploma,1997-11-25,Rivers,Port Harcourt,2016,Female
```

### **Step 2: Upload the CSV**
1. Log in as Administrator
2. Go to `/upload-alumni`
3. Upload `test_alumni_upload.csv`
4. Wait for import to complete

### **Step 3: Verify Category Assignment**
1. Go to `/admin/alumni-categories/assign`
2. Search for "John Doe" - Should show **Postgraduate** badge
3. Search for "Jane Smith" - Should show **Undergraduate (Full-time)** badge
4. Search for "Mike Johnson" - Should show **Undergraduate (Part-time)** badge
5. Search for "Sarah Williams" - Should show **Diploma** badge

**Expected Result:** ✅ All alumni should have correct categories assigned

---

## 📋 Test 2: Category Name Variations

### **Test Case: Case-Insensitive Matching**

Create `test_category_variations.csv`:

```csv
firstname,surname,matriculation_id,programme,department,faculty,year_of_graduation,category,date_of_birth,state,lga,year_of_entry,gender
Test1,User1,2011700001,Test,Test,Test,2020,postgraduate,1995-01-01,Lagos,Lagos,2016,Male
Test2,User2,2011700002,Test,Test,Test,2020,POSTGRADUATE,1995-01-01,Lagos,Lagos,2016,Male
Test3,User3,2011700003,Test,Test,Test,2020,undergraduate (full-time),1995-01-01,Lagos,Lagos,2016,Male
Test4,User4,2011700004,Test,Test,Test,2020,UNDERGRADUATE (FULL-TIME),1995-01-01,Lagos,Lagos,2016,Male
Test5,User5,2011700005,Test,Test,Test,2020,undergraduate-full-time,1995-01-01,Lagos,Lagos,2016,Male
Test6,User6,2011700006,Test,Test,Test,2020,diploma,1995-01-01,Lagos,Lagos,2016,Male
```

**Expected Result:** ✅ All variations should map correctly to proper categories

---

## 📋 Test 3: Invalid Category Error Handling

### **Test Case: Invalid Category Name**

Create `test_invalid_category.csv`:

```csv
firstname,surname,matriculation_id,programme,department,faculty,year_of_graduation,category,date_of_birth,state,lga,year_of_entry,gender
Invalid,User,3011700001,Test,Test,Test,2020,Invalid Category,1995-01-01,Lagos,Lagos,2016,Male
```

**Expected Result:** ❌ Import should fail with error: "The category 'Invalid Category' is invalid. Valid categories are: Postgraduate, Undergraduate (Full-time), Undergraduate (Part-time), Diploma"

---

## 📋 Test 4: Missing Category

### **Test Case: Empty Category**

Create `test_missing_category.csv`:

```csv
firstname,surname,matriculation_id,programme,department,faculty,year_of_graduation,category,date_of_birth,state,lga,year_of_entry,gender
Missing,Category,4011700001,Test,Test,Test,2020,,1995-01-01,Lagos,Lagos,2016,Male
```

**Expected Result:** ❌ Import should fail with error: "The category field is required."

---

## 📋 Test 5: Database Verification

### **Step 1: Run SQL Query**

Use `php artisan tinker`:

```php
// Check if categories are properly assigned
$alumni = \App\Models\Alumni::with('category')->latest()->take(5)->get();

foreach ($alumni as $a) {
    echo "{$a->user->name}: Category = " . ($a->category ? $a->category->name : 'NULL') . "\n";
}
```

**Expected Result:** ✅ All recent alumni should have non-null category_id

### **Step 2: Check Specific Alumni**

```php
$alumni = \App\Models\Alumni::whereHas('user', function($q) {
    $q->where('name', 'John Doe');
})->with('category')->first();

echo "Category ID: " . $alumni->category_id . "\n";
echo "Category Name: " . $alumni->category->name . "\n";
echo "Category Slug: " . $alumni->category->slug . "\n";
```

**Expected Result:** ✅ Should show category_id and correct category details

---

## 📋 Test 6: Bulk Upload Test

### **Test Case: Multiple Alumni with Same Category**

Create `test_bulk_same_category.csv` with 10+ alumni all with "Postgraduate":

```csv
firstname,surname,matriculation_id,programme,department,faculty,year_of_graduation,category,date_of_birth,state,lga,year_of_entry,gender
Bulk1,User1,5011700001,Test,Test,Test,2020,Postgraduate,1995-01-01,Lagos,Lagos,2016,Male
Bulk2,User2,5011700002,Test,Test,Test,2020,Postgraduate,1995-01-01,Lagos,Lagos,2016,Male
Bulk3,User3,5011700003,Test,Test,Test,2020,Postgraduate,1995-01-01,Lagos,Lagos,2016,Male
... (add more rows)
```

**Expected Result:** ✅ All should be assigned "Postgraduate" category correctly

---

## 🔍 Quick Test Script

You can also run this quick test via `php artisan tinker`:

```php
// Test 1: Check if categories exist
$categories = \App\Models\AlumniCategory::where('is_active', true)->pluck('name')->toArray();
print_r($categories);
// Should output: ["Postgraduate", "Undergraduate (Full-time)", "Undergraduate (Part-time)", "Diploma"]

// Test 2: Test category name mapping (simulate import logic)
$testCategories = [
    'postgraduate',
    'POSTGRADUATE',
    'undergraduate (full-time)',
    'undergraduate-full-time',
    'Diploma'
];

foreach ($testCategories as $testCat) {
    $categoryMap = [
        'postgraduate' => 'Postgraduate',
        'undergraduate (full-time)' => 'Undergraduate (Full-time)',
        'undergraduate (fulltime)' => 'Undergraduate (Full-time)',
        'undergraduate-full-time' => 'Undergraduate (Full-time)',
        'undergraduate_part_time' => 'Undergraduate (Part-time)',
        'diploma' => 'Diploma',
    ];
    
    $normalizedKey = strtolower($testCat);
    $lookupName = $categoryMap[$normalizedKey] ?? $testCat;
    
    $category = \App\Models\AlumniCategory::where('name', $lookupName)
        ->orWhereRaw('LOWER(name) = ?', [strtolower($lookupName)])
        ->first();
    
    echo "'{$testCat}' → " . ($category ? $category->name : 'NOT FOUND') . "\n";
}
```

---

## ✅ Success Criteria

All tests should pass if:
1. ✅ Valid category names are correctly assigned
2. ✅ Case variations are handled properly
3. ✅ Format variations (hyphens, underscores, spacing) are handled
4. ✅ Invalid categories show clear error messages
5. ✅ Missing categories show required field error
6. ✅ Database records have correct category_id
7. ✅ Category assignment persists after upload

---

## 🐛 Troubleshooting

### **Issue: Categories not being assigned**

**Check:**
1. Are the categories in the database? → `AlumniCategory::all()->pluck('name')`
2. Check logs: `storage/logs/laravel.log` for category-related errors
3. Verify CSV format matches exactly (including headers)

### **Issue: "Category is invalid" error**

**Check:**
1. Category name must match exactly (after normalization):
   - ✅ `Postgraduate`
   - ✅ `Undergraduate (Full-time)`
   - ✅ `Undergraduate (Part-time)`
   - ✅ `Diploma`
2. Check for typos in CSV
3. Verify categories exist in database

### **Issue: Import completes but category_id is NULL**

**Check:**
1. Look at `AlumniImport.php` around line 163-177
2. Verify category lookup is working: Add logging
3. Check if validation is passing but assignment is failing

---

## 📝 Test Checklist

- [ ] Test 1: Basic category assignment (all 4 categories)
- [ ] Test 2: Category name variations (case, format)
- [ ] Test 3: Invalid category error handling
- [ ] Test 4: Missing category error handling
- [ ] Test 5: Database verification
- [ ] Test 6: Bulk upload with same category
- [ ] Verify in `/admin/alumni-categories/assign` view
- [ ] Check logs for any warnings/errors

