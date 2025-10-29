# ⚡ Quick Test Commands

## 🔍 Step 1: Verify Categories Exist

Run in terminal:

```bash
php artisan tinker
```

Then paste this code:

```php
// Check if required categories exist
$required = ['Postgraduate', 'Undergraduate (Full-time)', 'Undergraduate (Part-time)', 'Diploma'];
$existing = \App\Models\AlumniCategory::where('is_active', true)->pluck('name')->toArray();

echo "Required categories:\n";
foreach ($required as $req) {
    $exists = in_array($req, $existing);
    echo ($exists ? "✅" : "❌") . " {$req}\n";
}

echo "\nAll categories in database:\n";
foreach ($existing as $cat) {
    echo "  - {$cat}\n";
}
```

**Expected Output:**
```
✅ Postgraduate
✅ Undergraduate (Full-time)
✅ Undergraduate (Part-time)
✅ Diploma
```

---

## 🧪 Step 2: Test Category Mapping Logic

In `php artisan tinker`, test the mapping:

```php
// Simulate the import category mapping logic
$testCategories = [
    'postgraduate',
    'POSTGRADUATE',
    'Postgraduate',
    'undergraduate (full-time)',
    'Undergraduate (Full-time)',
    'undergraduate-full-time',
    'undergraduate_part_time',
    'Diploma',
    'invalid-category'
];

$categoryMap = [
    'postgraduate' => 'Postgraduate',
    'undergraduate (full-time)' => 'Undergraduate (Full-time)',
    'undergraduate (fulltime)' => 'Undergraduate (Full-time)',
    'undergraduate-full-time' => 'Undergraduate (Full-time)',
    'undergraduate_part_time' => 'Undergraduate (Part-time)',
    'undergraduate (part-time)' => 'Undergraduate (Part-time)',
    'undergraduate-part-time' => 'Undergraduate (Part-time)',
    'diploma' => 'Diploma',
];

foreach ($testCategories as $testCat) {
    $normalizedKey = strtolower($testCat);
    $lookupName = $categoryMap[$normalizedKey] ?? $testCat;
    
    $category = \App\Models\AlumniCategory::where('name', $lookupName)
        ->orWhereRaw('LOWER(name) = ?', [strtolower($lookupName)])
        ->first();
    
    if ($category) {
        echo "✅ '{$testCat}' → {$category->name} (ID: {$category->id})\n";
    } else {
        echo "❌ '{$testCat}' → NOT FOUND\n";
    }
}
```

---

## 📤 Step 3: Test Upload (Manual)

1. Use the provided `test_alumni_upload.csv` file
2. Go to `/upload-alumni` as Administrator
3. Upload the CSV
4. Check the success message

---

## 🔍 Step 4: Verify Upload Results

In `php artisan tinker`:

```php
// Check recently uploaded alumni and their categories
$recentAlumni = \App\Models\Alumni::with(['user', 'category'])
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

foreach ($recentAlumni as $alumni) {
    $categoryName = $alumni->category ? $alumni->category->name : 'NULL';
    echo "{$alumni->user->name}: Category = {$categoryName}\n";
}
```

**Expected:** All should show non-null category names

---

## 🧹 Step 5: Clean Up Test Data (Optional)

If you want to clean up test data:

```php
// Delete test alumni (WARNING: This is permanent!)
$testMatricIds = ['1011700001', '1011700002', '1011700003', '1011700004', '1011700005', '1011700006'];

foreach ($testMatricIds as $matric) {
    $alumni = \App\Models\Alumni::where('matric_number', $matric)->first();
    if ($alumni) {
        $user = $alumni->user;
        $alumni->delete();
        $user->delete();
        echo "Deleted {$matric}\n";
    }
}
```

---

## ✅ Complete Test Checklist

Run these commands to verify everything:

```bash
# 1. Check categories
php artisan tinker
# (Run category check code from Step 1)

# 2. Upload test CSV via browser
# Go to: http://your-domain/upload-alumni

# 3. Verify results
php artisan tinker
# (Run verification code from Step 4)

# 4. Check in UI
# Go to: http://your-domain/admin/alumni-categories/assign
# Search for test alumni names
```

