<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    public function edit()
    {
        $company = CompanyProfile::getActive();
        return view('settings.company', compact('company'));
    }

    public function update(Request $request)
    {
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
        $company->update([
            'name' => $name,
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
