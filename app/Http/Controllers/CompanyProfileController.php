<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CompanyProfileController extends Controller
{
    public function edit()
    {
        $company = CompanyProfile::getActive();
        return view('settings.company', compact('company'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:150',
            'company_name' => 'nullable|string|max:150',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
            'tagline' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|string|email|max:150',
            'terms_conditions' => 'nullable|string|max:2000',
        ]);

        $name = $request->input('name') ?: $request->input('company_name');
        $tagline = $request->input('tagline');
        $address = $request->input('address') ?: $request->input('warehouse_address');
        $phone = $request->input('phone') ?: $request->input('phone_primary');
        $email = $request->input('email');
        $terms = $request->input('terms_conditions') ?: $request->input('invoice_terms');

        if (!$name) {
            return back()->withErrors(['name' => 'The company name field is required.']);
        }

        $company = CompanyProfile::getActive();
        $logoPath = $company->logo;

        // Handle remove logo
        if ($request->boolean('remove_logo')) {
            if ($logoPath && File::exists(public_path($logoPath))) {
                File::delete(public_path($logoPath));
            }
            $logoPath = null;
        }

        // Handle uploaded logo file
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $uploadDir = public_path('uploads/logos');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            // Remove old logo if exists
            if ($logoPath && File::exists(public_path($logoPath))) {
                File::delete(public_path($logoPath));
            }

            $fileName = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $logoPath = 'uploads/logos/' . $fileName;
        }

        $company->update([
            'name' => $name,
            'logo' => $logoPath,
            'tagline' => $tagline,
            'address' => $address,
            'phone' => $phone,
            'email' => $email,
            'terms_conditions' => $terms,
        ]);

        return redirect()->route('settings.company')
            ->with('success', 'Company profile and invoice print details updated successfully.');
    }
}
