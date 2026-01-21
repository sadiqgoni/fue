<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_name',
        'effective_date',
        'is_active',
        'tax_brackets',
        'reliefs',
        'description'
    ];

    protected $casts = [
        'tax_brackets' => 'array',
        'reliefs' => 'array',
        'effective_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffective($query, $date = null)
    {
        $date = $date ?: now();
        return $query->where('effective_date', '<=', $date)
                    ->orderBy('effective_date', 'desc');
    }

    // Calculate tax for given taxable income
    public function calculateTax($taxableIncome)
    {
        $tax = 0;
        $remainingIncome = $taxableIncome;

        foreach ($this->tax_brackets as $bracket) {
            $min = $bracket['min'] ?? 0;
            $max = $bracket['max'] ?? null;
            $rate = $bracket['rate'] ?? 0;

            if ($remainingIncome <= 0) break;

            if ($max === null || $remainingIncome <= $max) {
                // Last bracket or income fits in this bracket
                $taxableInThisBracket = $remainingIncome;
            } else {
                // Income exceeds this bracket
                $taxableInThisBracket = $max - $min;
            }

            $tax += ($taxableInThisBracket * $rate / 100);
            $remainingIncome -= $taxableInThisBracket;
        }

        return round($tax, 2);
    }

    // Get total reliefs for an employee
    public function getTotalReliefs($basicSalary = 100000, $housingAllowance = 0, $transportAllowance = 0)
    {
        $defaultReliefs = [
            'consolidated_rent_relief' => ['fixed' => 200000, 'description' => 'Fixed consolidated rent relief allowance'],
            'pension_contribution' => ['percentage' => 8.0, 'base' => 'basic_housing_transport', 'description' => '8% of basic + housing + transport'],
            'nhf_contribution' => ['percentage' => 2.5, 'base' => 'basic', 'description' => '2.5% of basic salary'],
            'nhis_contribution' => ['percentage' => 0.5, 'base' => 'basic', 'description' => '0.5% of basic salary'],
        ];

        // Merge with saved reliefs, using defaults if not specified
        $reliefs = array_merge($defaultReliefs, $this->reliefs ?? []);

        $total = 0;
        $calculatedReliefs = [];

        foreach ($reliefs as $key => $relief) {
            if (isset($relief['fixed'])) {
                $amount = $relief['fixed'];
                $calculatedReliefs[$key] = array_merge($relief, ['calculated_amount' => $amount]);
                $total += $amount;
            } elseif (isset($relief['percentage'])) {
                $percentage = $relief['percentage'];
                $base = $relief['base'] ?? 'basic';

                // Calculate base amount
                switch ($base) {
                    case 'basic_housing_transport':
                        $baseAmount = $basicSalary + $housingAllowance + $transportAllowance;
                        break;
                    case 'basic':
                    default:
                        $baseAmount = $basicSalary;
                        break;
                }

                $amount = ($percentage / 100) * $baseAmount;
                $calculatedReliefs[$key] = array_merge($relief, ['calculated_amount' => round($amount, 2)]);
                $total += round($amount, 2);
            }
        }

        return [
            'reliefs' => $calculatedReliefs,
            'total' => round($total, 2),
            'breakdown' => [
                'basic_salary' => $basicSalary,
                'housing_allowance' => $housingAllowance,
                'transport_allowance' => $transportAllowance,
            ]
        ];
    }

    // Boot method to ensure only one active bracket
    protected static function booted()
    {
        static::saving(function ($bracket) {
            if ($bracket->is_active) {
                // Deactivate all other brackets
                static::where('id', '!=', $bracket->id)->update(['is_active' => false]);
            }
        });
    }
}
