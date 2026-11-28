<?php

namespace App\Http\Controllers;

use App\Models\WorkCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkCategoryController extends Controller
{
    public function index(): View
    {
        $categories = WorkCategory::query()
            ->orderBy('name')
            ->paginate(15);

        return view('work-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('work-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:work_categories,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->has('is_active') ? (bool) $data['is_active'] : true;

        WorkCategory::create($data);

        return Redirect::route('admin.work-categories.index')
            ->with('status', 'Kategori pekerjaan berhasil ditambahkan.');
    }

    public function edit(WorkCategory $workCategory): View
    {
        return view('work-categories.edit', compact('workCategory'));
    }

    public function update(Request $request, WorkCategory $workCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('work_categories', 'name')->ignore($workCategory->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $workCategory->update($data);

        return Redirect::route('admin.work-categories.index')
            ->with('status', 'Kategori pekerjaan berhasil diperbarui.');
    }

    public function destroy(WorkCategory $workCategory): RedirectResponse
    {
        $workCategory->delete();

        return Redirect::route('admin.work-categories.index')
            ->with('status', 'Kategori pekerjaan berhasil dihapus.');
    }
}
