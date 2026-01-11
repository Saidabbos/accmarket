<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::ordered()->get();

        return Inertia::render('Admin/Languages/Index', [
            'languages' => $languages,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
        ]);

        $maxOrder = Language::max('sort_order') ?? 0;

        Language::create([
            'code' => $request->code,
            'name' => $request->name,
            'native_name' => $request->native_name,
            'is_active' => true,
            'is_default' => false,
            'sort_order' => $maxOrder + 1,
        ]);

        Language::clearCache();

        return back()->with('success', 'Language created successfully.');
    }

    public function update(Request $request, Language $language)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'native_name' => 'required|string|max:255',
        ]);

        $language->update([
            'name' => $request->name,
            'native_name' => $request->native_name,
        ]);

        Language::clearCache();

        return back()->with('success', 'Language updated successfully.');
    }

    public function toggleActive(Language $language)
    {
        // Don't allow deactivating the default language
        if ($language->is_default && $language->is_active) {
            return back()->withErrors(['language' => 'Cannot deactivate the default language.']);
        }

        $language->update(['is_active' => !$language->is_active]);

        Language::clearCache();

        return back()->with('success', 'Language status updated successfully.');
    }

    public function setDefault(Language $language)
    {
        // Remove default from all languages
        Language::where('is_default', true)->update(['is_default' => false]);

        // Set this language as default and ensure it's active
        $language->update([
            'is_default' => true,
            'is_active' => true,
        ]);

        Language::clearCache();

        return back()->with('success', 'Default language updated successfully.');
    }

    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return back()->withErrors(['language' => 'Cannot delete the default language.']);
        }

        $language->delete();

        Language::clearCache();

        return back()->with('success', 'Language deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'languages' => 'required|array',
            'languages.*.id' => 'required|exists:languages,id',
            'languages.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->languages as $item) {
            Language::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        Language::clearCache();

        return back()->with('success', 'Languages reordered successfully.');
    }
}
