<?php

namespace App\Http\Controllers;

use App\Support\Base64Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class FaceReferenceController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Auth/FaceSecurity', [
            'security' => [
                'has_face_reference' => $request->user()?->hasFaceReference() ?? false,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference_image' => ['required', 'string'],
        ]);

        try {
            $path = Base64Image::storeReference($validated['reference_image'], $request->user()->id);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors([
                'reference_image' => $exception->getMessage(),
            ]);
        }

        $request->user()->update([
            'face_reference_path' => $path,
        ]);

        return back()->with('status', 'Face ID configurado correctamente.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->face_reference_path) {
            Storage::disk('local')->delete($user->face_reference_path);
            $user->update(['face_reference_path' => null]);
        }

        return back()->with('status', 'Face ID eliminado de la cuenta.');
    }
}
