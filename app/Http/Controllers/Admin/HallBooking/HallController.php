<?php

namespace App\Http\Controllers\Admin\HallBooking;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HallController extends Controller
{
    public function index()
    {
        return view('admin.hall-bookings.halls.index', [
            'halls' => Hall::withCount(['bookings', 'priceRules', 'blockedDates'])->with('images')->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        $hall = Hall::create($data);
        $this->storeImages($request, $hall);

        return back()->with('success', db_trans('hall_created_successfully'));
    }

    public function update(Request $request, Hall $hall)
    {
        $data = $this->validated($request, $hall->id);
        $data['is_active'] = $request->boolean('is_active');

        if ($hall->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $hall->id);
        }

        $hall->update($data);
        $this->storeImages($request, $hall);

        return back()->with('success', db_trans('hall_updated_successfully'));
    }

    public function destroy(Hall $hall)
    {
        abort_if($hall->bookings()->exists(), 422, db_trans('hall_has_bookings_and_cannot_be_deleted'));
        $hall->delete();

        return back()->with('success', db_trans('hall_deleted_successfully'));
    }

    public function deleteImage(Hall $hall, int $image)
    {
        $imageModel = $hall->images()->findOrFail($image);
        Storage::disk('public')->delete($imageModel->image_path);
        $imageModel->delete();

        return back()->with('success', db_trans('hall_image_deleted_successfully'));
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'conditions' => ['nullable', 'string'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:180'],
            'bank_account_name' => ['nullable', 'string', 'max:180'],
            'bank_account_number' => ['nullable', 'string', 'max:120'],
            'payment_instructions' => ['nullable', 'string'],
            'images.*' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    protected function storeImages(Request $request, Hall $hall): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('hall-bookings/halls', 'public');
            $hall->images()->create([
                'image_path' => $path,
                'caption' => $hall->name,
                'sort_order' => $hall->images()->count() + $index,
                'is_cover' => $hall->images()->count() === 0 && $index === 0,
            ]);
        }
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Hall::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
