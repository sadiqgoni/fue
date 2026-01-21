<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TaxBracket;
use Illuminate\Database\Seeder;

class TaxBracketSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing tax brackets
        TaxBracket::truncate();

        // 1. Standard PAYE 2026 Structure (Formula 1 Equivalent)
        TaxBracket::create([
            'version_name' => 'PAYE 2026 - Standard Method',
            'effective_date' => '2026-01-01',
            'is_active' => true, // This will be the active one
            'description' => 'Standard PAYE calculation using full annual gross income (Formula 1 equivalent)',
            'tax_brackets' => [
                ['min' => 0, 'max' => 300000, 'rate' => 0],
                ['min' => 300000, 'max' => 600000, 'rate' => 1],
                ['min' => 600000, 'max' => 1100000, 'rate' => 2],
                ['min' => 1100000, 'max' => 1600000, 'rate' => 3],
                ['min' => 1600000, 'max' => 3200000, 'rate' => 4],
                ['min' => 3200000, 'max' => null, 'rate' => 5],
            ],
            'reliefs' => [
                'calculation_method' => 'standard',
                'consolidated_relief' => [
                    'fixed' => 200000,
                    'percentage_of_gross' => 20,
                    'description' => 'Consolidated Relief Allowance: ₦200,000 + 20% of annual gross'
                ],
                'pension' => [
                    'percentage' => 8.0,
                    'base' => 'basic', // or 'gross' depending on statutory_deduction setting
                    'annual' => true,
                    'description' => '8% pension contribution (relief)'
                ],
                'nhf' => [
                    'percentage' => 2.5,
                    'base' => 'basic',
                    'annual' => true,
                    'description' => 'National Housing Fund contribution (relief)'
                ],
                'nhis' => [
                    'percentage' => 0.05,
                    'base' => 'basic',
                    'annual' => true,
                    'description' => 'National Health Insurance Scheme contribution (relief)'
                ],
            ]
        ]);

        // 2. Benchmark Income Method (Formula 2 Equivalent)
        TaxBracket::create([
            'version_name' => 'PAYE 2026 - Benchmark Income Method',
            'effective_date' => '2026-01-01',
            'is_active' => false,
            'description' => 'PAYE calculation using Benchmark Income method: (Net Pay ÷ 2) × 12 (Formula 2 equivalent)',
            'tax_brackets' => [
                ['min' => 0, 'max' => 300000, 'rate' => 0],
                ['min' => 300000, 'max' => 600000, 'rate' => 1],
                ['min' => 600000, 'max' => 1100000, 'rate' => 2],
                ['min' => 1100000, 'max' => 1600000, 'rate' => 3],
                ['min' => 1600000, 'max' => 3200000, 'rate' => 4],
                ['min' => 3200000, 'max' => null, 'rate' => 5],
            ],
            'reliefs' => [
                'calculation_method' => 'benchmark_income',
                'benchmark_divisor' => 2,
                'consolidated_relief' => [
                    'fixed' => 200000,
                    'percentage_of_gross' => 20,
                    'description' => 'Simple relief: ₦200,000 + 20% of annual gross'
                ],
                'pension' => [
                    'percentage' => 8.0,
                    'base' => 'basic',
                    'annual' => false,
                    'description' => '8% pension (used in net pay calculation only)'
                ],
                'nhf' => [
                    'percentage' => 2.5,
                    'base' => 'basic',
                    'annual' => false,
                    'description' => 'NHF (used in net pay calculation only)'
                ],
                'nhis' => [
                    'percentage' => 0.5, // Note: 0.5% for monthly, not 0.05%
                    'base' => 'basic',
                    'annual' => false,
                    'description' => 'NHIS (used in net pay calculation only)'
                ],
            ]
        ]);

        // 3. Legacy Pre-2026 Structure
        TaxBracket::create([
            'version_name' => 'Legacy PAYE (Pre-2026)',
            'effective_date' => '2020-01-01',
            'is_active' => false,
            'description' => 'Old PAYE structure for historical records',
            'tax_brackets' => [
                ['min' => 0, 'max' => 300000, 'rate' => 7],
                ['min' => 300000, 'max' => 600000, 'rate' => 11],
                ['min' => 600000, 'max' => 1100000, 'rate' => 15],
                ['min' => 1100000, 'max' => 1600000, 'rate' => 19],
                ['min' => 1600000, 'max' => 3200000, 'rate' => 21],
                ['min' => 3200000, 'max' => null, 'rate' => 24],
            ],
            'reliefs' => [
                'calculation_method' => 'standard',
                'consolidated_relief' => [
                    'fixed' => 200000,
                    'percentage_of_gross' => 20,
                    'description' => 'Old CRA structure'
                ],
                'pension' => [
                    'percentage' => 8.0,
                    'base' => 'basic',
                    'annual' => true,
                    'description' => '8% pension relief'
                ],
                'nhf' => [
                    'percentage' => 2.5,
                    'base' => 'basic',
                    'annual' => true,
                    'description' => 'NHF relief'
                ],
                'nhis' => [
                    'percentage' => 0.05,
                    'base' => 'basic',
                    'annual' => true,
                    'description' => 'NHIS relief'
                ],
            ]
        ]);

        $this->command->info('✅ Tax brackets seeded successfully!');
        $this->command->info('✅ Active bracket: PAYE 2026 - Standard Method');
        $this->command->warn('⚠️  You can switch to Benchmark Income Method in the admin panel');
    }
}
