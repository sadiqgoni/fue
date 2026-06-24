<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryDeductionTemplate extends Model
{
    use HasFactory;
    protected $fillable=[
        'id',
        'salary_structure_id',
        'grade_level_from',
        'grade_level_to',
        'deduction_id',
        'deduction_type',
        'value',
        'created_at',
        'updated_at',
    ];

    public static function isNumericGradeLevel(mixed $value): bool
    {
        return is_numeric(trim((string) $value));
    }

    public function scopeMatchesGradeLevel($query, mixed $gradeLevel)
    {
        $gradeText = mb_strtolower(trim((string) $gradeLevel));

        if (self::isNumericGradeLevel($gradeLevel)) {
            $gradeNumber = (int) $gradeLevel;

            return $query->where(function ($query) use ($gradeNumber, $gradeText) {
                $query
                    ->where(function ($query) use ($gradeNumber) {
                        $query
                            ->whereRaw("TRIM(grade_level_from) REGEXP '^[0-9]+$'")
                            ->whereRaw("TRIM(grade_level_to) REGEXP '^[0-9]+$'")
                            ->whereRaw('? BETWEEN CAST(grade_level_from AS UNSIGNED) AND CAST(grade_level_to AS UNSIGNED)', [$gradeNumber]);
                    })
                    ->orWhereRaw('LOWER(TRIM(grade_level_from)) = ?', [$gradeText])
                    ->orWhereRaw('LOWER(TRIM(grade_level_to)) = ?', [$gradeText]);
            });
        }

        return $query->where(function ($query) use ($gradeText) {
            $query
                ->whereRaw('LOWER(TRIM(grade_level_from)) = ?', [$gradeText])
                ->orWhereRaw('LOWER(TRIM(grade_level_to)) = ?', [$gradeText]);
        });
    }

    public function scopeOverlapsGradeRange($query, mixed $from, mixed $to)
    {
        $fromText = mb_strtolower(trim((string) $from));
        $toText = mb_strtolower(trim((string) $to));

        if (self::isNumericGradeLevel($from) && self::isNumericGradeLevel($to)) {
            $fromNumber = (int) $from;
            $toNumber = (int) $to;

            return $query
                ->whereRaw("TRIM(grade_level_from) REGEXP '^[0-9]+$'")
                ->whereRaw("TRIM(grade_level_to) REGEXP '^[0-9]+$'")
                ->where(function ($query) use ($fromNumber, $toNumber) {
                    $query
                        ->whereRaw('CAST(grade_level_from AS UNSIGNED) BETWEEN ? AND ?', [$fromNumber, $toNumber])
                        ->orWhereRaw('CAST(grade_level_to AS UNSIGNED) BETWEEN ? AND ?', [$fromNumber, $toNumber])
                        ->orWhere(function ($query) use ($fromNumber, $toNumber) {
                            $query
                                ->whereRaw('CAST(grade_level_from AS UNSIGNED) <= ?', [$fromNumber])
                                ->whereRaw('CAST(grade_level_to AS UNSIGNED) >= ?', [$toNumber]);
                        });
                });
        }

        return $query->where(function ($query) use ($fromText, $toText) {
            $query
                ->where(function ($query) use ($fromText, $toText) {
                    $query
                        ->whereRaw('LOWER(TRIM(grade_level_from)) = ?', [$fromText])
                        ->whereRaw('LOWER(TRIM(grade_level_to)) = ?', [$toText]);
                })
                ->orWhere(function ($query) use ($fromText, $toText) {
                    $query
                        ->whereRaw('LOWER(TRIM(grade_level_from)) = ?', [$toText])
                        ->whereRaw('LOWER(TRIM(grade_level_to)) = ?', [$fromText]);
                });
        });
    }
}
