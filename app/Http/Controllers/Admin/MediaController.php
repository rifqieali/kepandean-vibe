<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Desa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function upload(Request $request): JsonResponse|RedirectResponse
    {
        Gate::authorize('upload-media');

        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:5120', // 5MB max
                'mimes:jpg,jpeg,png,webp,gif,svg',
            ],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        /** @var UploadedFile $file */
        $file = $request->file('file');

        // Additional validation for SVG
        if ($file->getMimeType() === 'image/svg+xml') {
            $path = $file->getRealPath();
            $content = is_string($path) ? file_get_contents($path) : false;

            if (! is_string($content) || preg_match('/<script|on\w+\s*=/i', $content)) {
                return $this->reject($request, 'SVG files with scripts or event handlers are not allowed.');
            }
        }

        // Get current desa from middleware or user
        $desa = App::bound('current_desa') ? App::make('current_desa') : auth()->user()?->desa;

        if (! $desa instanceof Desa) {
            return $this->reject($request, 'Upload ditolak: tidak ada desa yang terdaftar untuk akun ini.');
        }

        // Create an asset and attach the file
        $asset = Asset::create([
            'desa_id' => $desa->id,
        ]);

        $asset->addMedia($file)
            ->toMediaCollection('uploads');

        return response()->json([
            'message' => 'File uploaded successfully',
            'asset_id' => $asset->id,
        ]);
    }

    private function reject(Request $request, string $message): JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        throw ValidationException::withMessages([
            'file' => $message,
        ]);
    }
}
