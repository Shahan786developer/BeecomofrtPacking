<?php

namespace App\Http\Controllers;

use App\Models\BarcodeConfiguration;
use App\Models\BarcodeDescription;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'dozen', 'sku'])
            ->orderBy('product_code')
            ->get();

        $descriptions = BarcodeDescription::orderBy('name')->get();
        $defaultDescription = BarcodeDescription::where('is_default', true)->first() ?? $descriptions->first();

        $config = BarcodeConfiguration::getActive();

        // Optional preselected product
        $selectedProduct = null;
        if ($request->has('product_id')) {
            $selectedProduct = Product::with(['category', 'dozen', 'sku'])->find($request->product_id);
        }

        return view('barcode.index', compact(
            'products',
            'descriptions',
            'defaultDescription',
            'config',
            'selectedProduct'
        ));
    }

    public function print(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'description_id' => 'required|exists:barcode_descriptions,id',
            'quantity' => 'required|integer|min:1|max:1000',
        ]);

        $product = Product::with(['category', 'dozen', 'sku'])->findOrFail($validated['product_id']);
        $description = BarcodeDescription::findOrFail($validated['description_id']);
        $config = BarcodeConfiguration::getActive();
        $quantity = (int) $validated['quantity'];

        // Second line combined text: [Description] [Product Code] -> e.g. "INDIA AA-01"
        $secondLine = trim($description->name . ' ' . $product->product_code);

        return view('barcode.print', compact('product', 'description', 'secondLine', 'config', 'quantity'));
    }

    public function settings()
    {
        $config = BarcodeConfiguration::getActive();
        $descriptions = BarcodeDescription::orderBy('name')->get();

        return view('barcode.settings', compact('config', 'descriptions'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:100',
            'label_width_mm' => 'required|numeric|min:10|max:100',
            'label_height_mm' => 'required|numeric|min:10|max:100',
            'barcode_type' => 'required|string|max:50',
            'font_size' => 'required|integer|min:6|max:18',
            'font_family' => 'required|string|max:100',
            'text_align' => 'required|string|in:center,left,right',
            'rotation' => 'required|integer|in:0,90,180,270',
            'auto_resize' => 'nullable|boolean',
            'default_description_id' => 'nullable|exists:barcode_descriptions,id',
        ]);

        $config = BarcodeConfiguration::getActive();
        $config->update([
            'brand_name' => $validated['brand_name'],
            'label_width_mm' => $validated['label_width_mm'],
            'label_height_mm' => $validated['label_height_mm'],
            'barcode_type' => $validated['barcode_type'],
            'font_size' => $validated['font_size'],
            'font_family' => $validated['font_family'],
            'text_align' => $validated['text_align'],
            'rotation' => $validated['rotation'],
            'auto_resize' => $request->boolean('auto_resize'),
            'default_description_id' => $validated['default_description_id'] ?? null,
        ]);

        return redirect()->route('barcode.settings')
            ->with('success', 'Thermal Barcode Configuration updated successfully.');
    }

    public function storeDescription(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:barcode_descriptions,name',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use ($validated, $isDefault) {
            if ($isDefault) {
                BarcodeDescription::where('is_default', true)->update(['is_default' => false]);
            }

            $desc = BarcodeDescription::create([
                'name' => strtoupper(trim($validated['name'])),
                'is_default' => $isDefault,
            ]);

            if ($isDefault) {
                $config = BarcodeConfiguration::getActive();
                $config->update(['default_description_id' => $desc->id]);
            }
        });

        return redirect()->route('barcode.settings')
            ->with('success', "Second-line description '{$validated['name']}' added successfully.");
    }

    public function setDefaultDescription(BarcodeDescription $description)
    {
        DB::transaction(function () use ($description) {
            BarcodeDescription::where('is_default', true)->update(['is_default' => false]);
            $description->update(['is_default' => true]);

            $config = BarcodeConfiguration::getActive();
            $config->update(['default_description_id' => $description->id]);
        });

        return redirect()->route('barcode.settings')
            ->with('success', "'{$description->name}' is now set as the default second-line description.");
    }

    public function destroyDescription(BarcodeDescription $description)
    {
        $description->delete();

        return redirect()->route('barcode.settings')
            ->with('success', 'Barcode description removed successfully.');
    }
}
