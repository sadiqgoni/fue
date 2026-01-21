<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TaxBracket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxBracketController extends Controller
{
    public function index()
    {
        $taxBrackets = TaxBracket::orderBy('effective_date', 'desc')->paginate(15);
        return view('admin.tax-brackets.index', compact('taxBrackets'));
    }

    public function create()
    {
        return view('admin.tax-brackets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'version_name' => 'required|string|max:255',
            'effective_date' => 'required|date',
            'tax_brackets' => 'required|array|min:1',
            'tax_brackets.*.min' => 'required|numeric|min:0',
            'tax_brackets.*.max' => 'nullable|numeric|min:0',
            'tax_brackets.*.rate' => 'required|numeric|min:0|max:100',
            'reliefs.consolidated_rent_relief.fixed' => 'nullable|numeric|min:0',
            'reliefs.pension_contribution.percentage' => 'nullable|numeric|min:0|max:100',
            'reliefs.nhf_contribution.percentage' => 'nullable|numeric|min:0|max:100',
            'reliefs.nhis_contribution.percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Create the bracket first without setting it as active
            $taxBracket = TaxBracket::create([
                'version_name' => $request->version_name,
                'effective_date' => $request->effective_date,
                'is_active' => false, // Don't set active yet
                'tax_brackets' => $request->tax_brackets,
                'reliefs' => $request->reliefs ?? [],
                'description' => $request->description,
            ]);

            // If requested to be active, deactivate all others and activate this one
            if ($request->is_active) {
                TaxBracket::where('id', '!=', $taxBracket->id)->update(['is_active' => false]);
                $taxBracket->update(['is_active' => true]);
            }

            DB::commit();
            return redirect()->route('tax-brackets.index')->with('success', 'Tax bracket created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Failed to create tax bracket: ' . $e->getMessage()]);
        }
    }

    public function show(TaxBracket $taxBracket)
    {
        return view('admin.tax-brackets.show', compact('taxBracket'));
    }

    public function edit(TaxBracket $taxBracket)
    {
        return view('admin.tax-brackets.edit', compact('taxBracket'));
    }

    public function update(Request $request, TaxBracket $taxBracket)
    {
        $request->validate([
            'version_name' => 'required|string|max:255',
            'effective_date' => 'required|date',
            'tax_brackets' => 'required|array|min:1',
            'tax_brackets.*.min' => 'required|numeric|min:0',
            'tax_brackets.*.max' => 'nullable|numeric|min:0',
            'tax_brackets.*.rate' => 'required|numeric|min:0|max:100',
            'reliefs.consolidated_rent_relief.fixed' => 'nullable|numeric|min:0',
            'reliefs.pension_contribution.percentage' => 'nullable|numeric|min:0|max:100',
            'reliefs.nhf_contribution.percentage' => 'nullable|numeric|min:0|max:100',
            'reliefs.nhis_contribution.percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $taxBracket->update([
                'version_name' => $request->version_name,
                'effective_date' => $request->effective_date,
                'tax_brackets' => $request->tax_brackets,
                'reliefs' => $request->reliefs ?? [],
                'description' => $request->description,
            ]);

            // Handle activation/deactivation
            if ($request->is_active && !$taxBracket->is_active) {
                TaxBracket::where('id', '!=', $taxBracket->id)->update(['is_active' => false]);
                $taxBracket->update(['is_active' => true]);
            } elseif (!$request->is_active && $taxBracket->is_active) {
                $taxBracket->update(['is_active' => false]);
            }

            DB::commit();
            return redirect()->route('tax-brackets.index')->with('success', 'Tax bracket updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Failed to update tax bracket: ' . $e->getMessage()]);
        }
    }

    public function destroy(TaxBracket $taxBracket)
    {
        $taxBracket->delete();
        return redirect()->route('tax-brackets.index')->with('success', 'Tax bracket deleted successfully');
    }

    public function activate(TaxBracket $taxBracket)
    {
        TaxBracket::where('id', '!=', $taxBracket->id)->update(['is_active' => false]);
        $taxBracket->update(['is_active' => true]);

        return redirect()->route('tax-brackets.index')->with('success', 'Tax bracket activated successfully');
    }

    public function testCalculation(TaxBracket $taxBracket)
    {
        $testIncome = 2100000; // ₦2.1M annual
        $tax = $taxBracket->calculateTax($testIncome);
        $reliefs = $taxBracket->getTotalReliefs(175000); // Monthly basic

        return view('admin.tax-brackets.test', compact('taxBracket', 'testIncome', 'tax', 'reliefs'));
    }
}
