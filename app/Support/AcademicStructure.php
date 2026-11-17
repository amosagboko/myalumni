<?php

namespace App\Support;

use App\Models\Alumni;

class AcademicStructure
{
    /**
     * Faculty → department → programme tree from complete alumni rows.
     *
     * Labels that differ only by case, spacing, or invisible characters are collapsed to one option.
     *
     * @return array<string, array<string, list<string>>>
     */
    public static function tree(): array
    {
        $rows = Alumni::query()
            ->whereNotNull('faculty')
            ->where('faculty', '!=', '')
            ->where('faculty', 'not like', 'Test Faculty%')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->whereNotNull('programme')
            ->where('programme', '!=', '')
            ->select('faculty', 'department', 'programme')
            ->distinct()
            ->orderBy('faculty')
            ->orderBy('department')
            ->orderBy('programme')
            ->get();

        $facultyLabels = [];
        $departmentLabels = [];
        $programmeLabels = [];

        foreach ($rows as $row) {
            $faculty = self::normalizeLabel((string) $row->faculty);
            $department = self::normalizeLabel((string) $row->department);
            $programme = self::normalizeLabel((string) $row->programme);

            if ($faculty === '' || $department === '' || $programme === '') {
                continue;
            }

            $facultyKey = self::uniqueKey($faculty);
            $departmentKey = self::uniqueKey($department);
            $programmeKey = self::uniqueKey($programme);

            $facultyLabels[$facultyKey] ??= $faculty;
            $departmentLabels[$facultyKey][$departmentKey] ??= $department;
            $programmeLabels[$facultyKey][$departmentKey][$programmeKey] ??= $programme;
        }

        $tree = [];

        foreach ($facultyLabels as $facultyKey => $faculty) {
            $departments = $departmentLabels[$facultyKey] ?? [];
            natcasesort($departments);

            foreach ($departments as $departmentKey => $department) {
                $programmes = array_values($programmeLabels[$facultyKey][$departmentKey] ?? []);
                natcasesort($programmes);
                $tree[$faculty][$department] = array_values($programmes);
            }
        }

        uksort($tree, 'strnatcasecmp');

        return $tree;
    }

    public static function isValidCombination(string $faculty, string $department, string $programme): bool
    {
        $facultyKey = self::uniqueKey(self::normalizeLabel($faculty));
        $departmentKey = self::uniqueKey(self::normalizeLabel($department));
        $programmeKey = self::uniqueKey(self::normalizeLabel($programme));

        if ($facultyKey === '' || $departmentKey === '' || $programmeKey === '') {
            return false;
        }

        foreach (self::tree() as $treeFaculty => $departments) {
            if (self::uniqueKey($treeFaculty) !== $facultyKey) {
                continue;
            }

            foreach ($departments as $treeDepartment => $programmes) {
                if (self::uniqueKey($treeDepartment) !== $departmentKey) {
                    continue;
                }

                foreach ($programmes as $treeProgramme) {
                    if (self::uniqueKey($treeProgramme) === $programmeKey) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function normalizeLabel(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[\x{00A0}\x{1680}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}\x{FEFF}]/u', ' ', $value) ?? $value;
        $value = str_replace(["\r", "\n", "\t"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    public static function uniqueKey(string $value): string
    {
        return mb_strtolower(self::normalizeLabel($value), 'UTF-8');
    }
}
