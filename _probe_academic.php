<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
require __DIR__ . "/vendor/autoload.php";
$app = require __DIR__ . "/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tables = collect(DB::select("SHOW TABLES"))->map(fn ($r) => array_values((array) $r)[0]);
$matching = $tables->filter(fn ($t) => preg_match("/faculty|department|programme|program|alumni|student/i", $t));
echo "ALL_TABLES_COUNT: " . $tables->count() . PHP_EOL;
echo "TABLES_MATCHING: " . $matching->implode(", ") . PHP_EOL;
echo "HAS_FACULTIES: " . (Schema::hasTable("faculties") ? "yes" : "no") . PHP_EOL;
echo "HAS_DEPARTMENTS: " . (Schema::hasTable("departments") ? "yes" : "no") . PHP_EOL;
echo "HAS_PROGRAMMES: " . (Schema::hasTable("programmes") ? "yes" : "no") . PHP_EOL;
echo "HAS_PROGRAMS: " . (Schema::hasTable("programs") ? "yes" : "no") . PHP_EOL;
echo "ALUMNI_COUNT: " . DB::table("alumni")->count() . PHP_EOL;
echo "STUDENTS_COUNT: " . (Schema::hasTable("students") ? (string) DB::table("students")->count() : "no table") . PHP_EOL;
echo "ALUMNI_COLUMNS: " . implode(", ", Schema::getColumnListing("alumni")) . PHP_EOL;
$indexes = DB::select("SHOW INDEX FROM alumni");
$academicIndexes = collect($indexes)->filter(fn ($i) => in_array($i->Column_name, ["programme", "department", "faculty"], true));
echo "ACADEMIC_INDEXES: " . $academicIndexes->map(fn ($i) => $i->Key_name . ":" . $i->Column_name)->implode(", ") . PHP_EOL;
$schema = DB::select("SHOW COLUMNS FROM alumni WHERE Field IN ('programme','department','faculty')");
foreach ($schema as $col) { echo "COL " . json_encode($col) . PHP_EOL; }
echo PHP_EOL . "=== FACULTIES ===" . PHP_EOL;
foreach (DB::table("alumni")->select("faculty", DB::raw("COUNT(*) as c"))->groupBy("faculty")->orderByDesc("c")->get() as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
echo PHP_EOL . "=== DEPARTMENTS ===" . PHP_EOL;
$dep = DB::table("alumni")->select("department", DB::raw("COUNT(*) as c"))->groupBy("department")->orderBy("department")->get();
echo "DEPARTMENT_DISTINCT: " . $dep->count() . PHP_EOL;
foreach ($dep as $r) { echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL; }
echo PHP_EOL . "=== PROGRAMMES ===" . PHP_EOL;
$prog = DB::table("alumni")->select("programme", DB::raw("COUNT(*) as c"))->groupBy("programme")->orderBy("programme")->get();
echo "PROGRAMME_DISTINCT: " . $prog->count() . PHP_EOL;
foreach ($prog as $r) { echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL; }
echo PHP_EOL . "=== COMBOS ===" . PHP_EOL;
$combos = DB::table("alumni")->select("faculty", "department", "programme", DB::raw("COUNT(*) as c"))->groupBy("faculty", "department", "programme")->orderBy("faculty")->orderBy("department")->orderBy("programme")->get();
echo "COMBO_COUNT: " . $combos->count() . PHP_EOL;
$deptPerFaculty = $combos->groupBy("faculty")->map(fn ($rows) => $rows->pluck("department")->unique()->values());
echo "DEPTS_PER_FACULTY:" . PHP_EOL;
foreach ($deptPerFaculty as $facName => $depts) {
    echo json_encode(["faculty" => $facName, "dept_count" => $depts->count(), "depts" => $depts->all()], JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
$progPerDept = $combos->groupBy(fn ($r) => $r->faculty . " || " . $r->department)->map(fn ($rows) => $rows->pluck("programme")->unique()->values());
$multiProg = $progPerDept->filter(fn ($p) => $p->count() > 1);
echo "DEPT_WITH_MULTIPLE_PROGRAMMES: " . $multiProg->count() . PHP_EOL;
foreach ($multiProg as $key => $progs) {
    echo json_encode(["key" => $key, "programmes" => $progs->all()], JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
$deptInManyFac = $combos->groupBy("department")->map(fn ($rows) => $rows->pluck("faculty")->unique()->values())->filter(fn ($f) => $f->count() > 1);
echo "DEPT_IN_MULTIPLE_FACULTIES: " . $deptInManyFac->count() . PHP_EOL;
foreach ($deptInManyFac as $dept => $facs) {
    echo json_encode(["department" => $dept, "faculties" => $facs->all()], JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
$progInManyDept = $combos->groupBy("programme")->map(fn ($rows) => $rows->map(fn ($r) => $r->faculty . " / " . $r->department)->unique()->values())->filter(fn ($d) => $d->count() > 1);
echo "PROGRAMME_IN_MULTIPLE_DEPTS: " . $progInManyDept->count() . PHP_EOL;
foreach ($progInManyDept as $progName => $homes) {
    echo json_encode(["programme" => $progName, "homes" => $homes->all()], JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
echo PHP_EOL . "=== CASE / TRIM / NULL ===" . PHP_EOL;
$caseFac = collect(DB::select("SELECT faculty, LOWER(faculty) as low, COUNT(*) c FROM alumni GROUP BY faculty, LOWER(faculty)"))->groupBy("low")->filter(fn ($g) => $g->count() > 1);
echo "FACULTY_CASE_DUP_GROUPS: " . $caseFac->count() . PHP_EOL;
foreach ($caseFac as $g) { echo json_encode($g->map(fn ($r) => ["faculty" => $r->faculty, "c" => $r->c])->all(), JSON_UNESCAPED_UNICODE) . PHP_EOL; }
$caseDept = collect(DB::select("SELECT department, LOWER(department) as low, COUNT(*) c FROM alumni GROUP BY department, LOWER(department)"))->groupBy("low")->filter(fn ($g) => $g->count() > 1);
echo "DEPT_CASE_DUP_GROUPS: " . $caseDept->count() . PHP_EOL;
foreach ($caseDept as $g) { echo json_encode($g->map(fn ($r) => ["department" => $r->department, "c" => $r->c])->all(), JSON_UNESCAPED_UNICODE) . PHP_EOL; }
$caseProg = collect(DB::select("SELECT programme, LOWER(programme) as low, COUNT(*) c FROM alumni GROUP BY programme, LOWER(programme)"))->groupBy("low")->filter(fn ($g) => $g->count() > 1);
echo "PROG_CASE_DUP_GROUPS: " . $caseProg->count() . PHP_EOL;
foreach ($caseProg as $g) { echo json_encode($g->map(fn ($r) => ["programme" => $r->programme, "c" => $r->c])->all(), JSON_UNESCAPED_UNICODE) . PHP_EOL; }
$trim = DB::select("SELECT COUNT(*) c FROM alumni WHERE faculty <> TRIM(faculty) OR department <> TRIM(department) OR programme <> TRIM(programme)");
echo "UNTRIMMED_ROWS: " . $trim[0]->c . PHP_EOL;
$nulls = DB::select("SELECT SUM(faculty IS NULL OR faculty='') empty_faculty, SUM(department IS NULL OR department='') empty_dept, SUM(programme IS NULL OR programme='') empty_prog FROM alumni");
echo "EMPTY: " . json_encode($nulls[0]) . PHP_EOL;
echo PHP_EOL . "=== SAMPLE ROWS ===" . PHP_EOL;
foreach (DB::table("alumni")->select("matric_number", "faculty", "department", "programme")->limit(20)->get() as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
if (Schema::hasTable("students")) {
    echo PHP_EOL . "=== STUDENTS DISTINCT ===" . PHP_EOL;
    echo "student faculties: " . DB::table("students")->distinct()->count("faculty") . PHP_EOL;
    echo "student depts: " . DB::table("students")->distinct()->count("department") . PHP_EOL;
    echo "student programmes: " . DB::table("students")->distinct()->count("programme") . PHP_EOL;
}
