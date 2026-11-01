<?php

namespace App\Http\Controllers;

use App\Models\Dozen;
use Illuminate\Http\Request;

class DozenController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Dozen::withCount('products');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('label', 'like', "%{$search}%")
                  ->orWhere('quantity', 'like', "%{$search}%");
            });
        }

        $dozens = $query->orderByDesc('is_default')->orderBy('quantity')->paginate(15)->withQueryString();

        return view('dozens.index', compact('dozens', 'search'));
    }

    public function setDefault(Dozen $dozen)
    {
        Dozen::query()->update(['is_default' => false]);
        $dozen->update(['is_default' => true]);

        return redirect()->route('dozens.index')
            ->with('success', "Dozen unit '{$dozen->label}' has been set as the default unit.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50|unique:dozens,label',
            'quantity' => 'required|integer|min:1|max:1000',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            Dozen::query()->update(['is_default' => false]);
        }

        $dozen = Dozen::create([
            'label' => $validated['label'],
            'quantity' => $validated['quantity'],
            'is_default' => $isDefault,
        ]);

        return redirect()->route('dozens.index')
            ->with('success', "Dozen unit '{$dozen->label}' added successfully.");
    }

    public function update(Request $request, Dozen $dozen)
    {
        $validated = $request->validate([
            'label' => "required|string|max:50|unique:dozens,label,{$dozen->id}",
            'quantity' => 'required|integer|min:1|max:1000',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            Dozen::query()->where('id', '!=', $dozen->id)->update(['is_default' => false]);
        }

        $dozen->update([
            'label' => $validated['label'],
            'quantity' => $validated['quantity'],
            'is_default' => $isDefault,
        ]);

        return redirect()->route('dozens.index')
            ->with('success', "Dozen unit '{$dozen->label}' updated successfully.");
    }

    public function destroy(Dozen $dozen)
    {
        if ($dozen->products()->count() > 0) {
            return back()->with('error', "Cannot delete dozen unit '{$dozen->label}' because it is assigned to {$dozen->products()->count()} registered product(s).");
        }

        $dozen->delete();

        return redirect()->route('dozens.index')
            ->with('success', "Dozen unit deleted successfully.");
    }
}
