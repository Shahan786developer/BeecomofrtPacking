<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricingController extends Controller
{
    public function index()
    {
        $percentageRules = PricingRule::where('rule_type', 'percentage')
            ->orderBy('percentage', 'asc')
            ->get();

        $categoryRules = PricingRule::with('category')
            ->where('rule_type', 'category')
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('pricing.index', compact('percentageRules', 'categoryRules', 'categories'));
    }

    public function storePercentage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'percentage' => 'required|numeric|min:0|max:1000',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($validated, $isDefault) {
            if ($isDefault) {
                // Unset any previous default
                PricingRule::where('is_default', true)->update(['is_default' => false]);
            }

            PricingRule::create([
                'name' => $validated['name'],
                'rule_type' => 'percentage',
                'percentage' => (float) $validated['percentage'],
                'is_default' => $isDefault,
                'description' => $validated['description'] ?? null,
            ]);
        });

        return redirect()->route('pricing.index')
            ->with('success', "Percentage pricing record '{$validated['name']}' created successfully.");
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'percentage' => 'required|numeric|min:0|max:1000',
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $category = Category::findOrFail($validated['category_id']);
        $ruleName = $validated['name'] ?: "{$category->name} Pricing ({$validated['percentage']}%)";

        PricingRule::create([
            'name' => $ruleName,
            'rule_type' => 'category',
            'category_id' => $category->id,
            'percentage' => (float) $validated['percentage'],
            'is_default' => false,
            'description' => $validated['description'] ?? "Category specific rate for {$category->name}",
        ]);

        return redirect()->route('pricing.index')
            ->with('success', "Category pricing configuration for '{$category->name}' created successfully.");
    }

    public function setDefault(PricingRule $pricingRule)
    {
        DB::transaction(function () use ($pricingRule) {
            PricingRule::where('is_default', true)->update(['is_default' => false]);
            $pricingRule->update(['is_default' => true]);
        });

        return redirect()->route('pricing.index')
            ->with('success', "'{$pricingRule->name}' is now set as the default pricing rule.");
    }

    public function destroy(PricingRule $pricingRule)
    {
        $productsUsingRule = Product::where('pricing_rule_id', $pricingRule->id)->count();

        if ($productsUsingRule > 0) {
            return redirect()->route('pricing.index')
                ->with('error', "Cannot delete '{$pricingRule->name}' because {$productsUsingRule} product(s) are actively using it.");
        }

        $wasDefault = $pricingRule->is_default;
        $pricingRule->delete();

        // If default was deleted, assign first available as default
        if ($wasDefault) {
            $first = PricingRule::first();
            if ($first) {
                $first->update(['is_default' => true]);
            }
        }

        return redirect()->route('pricing.index')
            ->with('success', 'Pricing rule deleted successfully.');
    }
}
