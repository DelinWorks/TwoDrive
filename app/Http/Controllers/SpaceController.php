<?php

namespace App\Http\Controllers;

use App\Models\File;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use Hidehalo\Nanoid\Client;

class SpaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['store', 'delete', 'create_folder', 'share']);
    }

    public function index($uuid = '0')
    {
        $userId = Auth::id();
        $isAuthenticated = Auth::check();
        
        // Handle root access
        if ($uuid === '0') {
            if (!$isAuthenticated) {
                return redirect('/login');
            }
            
            $files = File::where('owner_id', $userId)->where('parent_uuid', $uuid)
                ->select('uuid', 'filename', 'file_size', 'created_at', 'is_folder', 'is_shared')
                ->orderBy('is_folder', 'desc')->orderBy('created_at', 'desc')
                ->get();
                
            $path = [];
            return view('space', compact('files', 'path'));
        }
        
        // Check access to the requested folder
        $currentFolder = File::where('uuid', $uuid)->first();
        
        if (!$currentFolder) {
            abort(404);
        }
        
        $userOwnsFolder = $isAuthenticated && $currentFolder->owner_id === $userId;
        $sharedParentUuid = null;
        
        if (!$userOwnsFolder) {
            // Check if accessible through shared parent
            $currentUuid = $uuid;
            $hasAccess = false;
            
            while ($currentUuid !== '0') {
                $folder = File::select('uuid', 'parent_uuid', 'is_shared', 'is_folder')
                    ->where('uuid', $currentUuid)
                    ->first();
                
                if (!$folder) break;
                
                if ($folder->is_shared && $folder->is_folder) {
                    $hasAccess = true;
                    $sharedParentUuid = $folder->uuid;
                    break;
                }
                
                $currentUuid = $folder->parent_uuid;
            }
            
            if (!$hasAccess) {
                abort(404);
            }
        }
        
        // Get files based on access type
        if ($userOwnsFolder) {
            $files = File::where('owner_id', $userId)->where('parent_uuid', $uuid)
                ->select('uuid', 'filename', 'file_size', 'created_at', 'is_folder', 'is_shared')
                ->orderBy('is_folder', 'desc')->orderBy('created_at', 'desc')
                ->get();
        } else {
            $files = File::where('parent_uuid', $uuid)
                ->select('uuid', 'filename', 'file_size', 'created_at', 'is_folder', 'is_shared')
                ->orderBy('is_folder', 'desc')->orderBy('created_at', 'desc')
                ->get();
        }
        
        // Build breadcrumb path (don't go beyond shared folder for non-owners)
        $path = [];
        $tempUuid = $uuid;
        
        while ($tempUuid !== '0' && $tempUuid !== $sharedParentUuid) {
            $file = File::select('uuid', 'filename', 'parent_uuid')
                ->where('uuid', $tempUuid)
                ->first();
            
            if (!$file) break;
            
            $path[] = $file;
            $tempUuid = $file->parent_uuid;
        }
        
        // Add shared folder to path if accessing through shared access
        if ($sharedParentUuid && !$userOwnsFolder) {
            $sharedFolder = File::select('uuid', 'filename', 'parent_uuid')
                ->where('uuid', $sharedParentUuid)
                ->first();
            
            if ($sharedFolder) {
                $path[] = $sharedFolder;
            }
        }
        
        return view('space', compact('files', 'path'));
    }

    private function propagateFileSize($uuid, $subtract = false)
    {
        $file = File::where('uuid', $uuid)->first();

        if (!$file || $file->is_folder)
        {
            return;
        }
        
        $fileSize = $file->file_size;
        $currentParentUuid = $file->parent_uuid;

        while ($currentParentUuid !== '0')
        {
        
            $parentFolder = File::where('uuid', $currentParentUuid)->first();
            
            if ($subtract)
            {
                File::where('uuid', $currentParentUuid)
                    ->decrement('file_size', $fileSize);
            }
            else
            {
                File::where('uuid', $currentParentUuid)
                    ->increment('file_size', $fileSize);
            }

            $currentParentUuid = $parentFolder->parent_uuid;
        }
    }

    public function store(Request $request)
    {      
        try {
            $validated = $request->validate([
                'uuid' => 'required|string',
                'files' => 'required|array|min:1',
                'files.*' => 'required|file|max:102400',
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'File upload failed: ' . $e->getMessage());
        }

        $uuid = $validated['uuid'];

        $userId = Auth::id();
        if ($uuid != 0 && !File::where('owner_id', $userId)->where('uuid', $uuid)->where('is_folder', true)->count()) {
            return redirect()->back();
        }

        foreach ($request->file('files') as $file) {
            $id = (new Client())->generateId(16);
            $originalName = $file->getClientOriginalName();

            try {
                File::create([
                    'uuid' => $id,
                    'filename' => $originalName,
                    'parent_uuid' => $uuid,
                    'owner_id' => auth()->id(),
                    'file_size' => $file->getSize(),
                    'is_folder' => false,
                ]);

                $this->propagateFileSize($id);
                $file->storeAs("uploads", $id, 'local');

            } catch (\Exception $e) {
                return redirect()
                    ->back()
                    ->with('error', 'File upload failed: ' . $e->getMessage());
            }
        }

        return redirect()
            ->back()
            ->with('success', count($request->file('files')) . ' file(s) uploaded successfully.');
    }

    private function recursiveDelete($uuid)
    {
        $item = File::where('uuid', $uuid)->first();
        
        if (!$item) {
            return;
        }
        
        if (!$item->is_folder) {
            return;
        }
        
        $children = File::where('parent_uuid', $uuid)->get();
        
        foreach ($children as $child) {
            if ($child->is_folder) {
                $this->recursiveDelete($child->uuid);
            } else {
                $path = "uploads/{$child->uuid}";
                if (Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->delete($path);
                }
            }

            File::where('uuid', $child->uuid)->delete();
        }
    }

    public function delete(Request $request)
    {      
        $userId = Auth::id();

        $ids = json_decode($request->input('ids'), true);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()
                ->back()
                ->with('error', 'Invalid or empty file list.');
        }

        foreach ($ids as $id) {
            if (!is_string($id)) {
                return redirect()
                    ->back()
                    ->with('error', 'Invalid ID format.');
            }
        }

        $files = File::where('owner_id', $userId)
            ->whereIn('uuid', $ids)
            ->get();

        foreach ($files as $file) {
            if (!$file->is_folder)
            {
                $path = "uploads/{$file->uuid}";
                if (Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->delete($path);
                }
                $this->propagateFileSize($file->uuid, true);

            } else $this->recursiveDelete($file->uuid);
        }

        File::where('owner_id', $userId)
            ->whereIn('uuid', $ids)
            ->delete();

        return redirect()
            ->back()
            ->with('success', count($files) . ' file(s) deleted successfully.');
    }

    private function hasFileAccess($fileUuid)
    {
        $file = File::where('uuid', $fileUuid)->first();
        
        if (!$file) {
            return false;
        }
        
        if (Auth::check() && $file->owner_id === Auth::id()) {
            return true;
        }
        
        $currentUuid = $file->is_folder ? $file->uuid : $file->parent_uuid;
        
        while ($currentUuid !== '0') {
            $folder = File::select('uuid', 'parent_uuid', 'is_shared', 'is_folder')
                ->where('uuid', $currentUuid)
                ->first();
            
            if (!$folder) {
                break;
            }
            
            if ($folder->is_shared && $folder->is_folder) {
                return true;
            }
            
            $currentUuid = $folder->parent_uuid;
        }
        
        return false;
    }

    public function download($id)
    {
        try {
            if (!$this->hasFileAccess($id)) {
                abort(404, 'File not found or access denied');
            }
            
            $file = File::where('uuid', $id)->firstOrFail();
            $storedPath = "uploads/$id";

            if (!Storage::disk('local')->exists($storedPath)) {
                throw new Exception('File not found');
            }

            return Storage::disk('local')->download($storedPath, $file->filename);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    public function view($id)
    {
        try {
            if (!$this->hasFileAccess($id)) {
                abort(404, 'File not found or access denied');
            }
            
            $file = File::where('uuid', $id)->firstOrFail();
            $storedPath = "uploads/$id";

            if (!Storage::disk('local')->exists($storedPath)) {
                throw new Exception('File not found');
            }

            $mime = Storage::disk('local')->mimeType($storedPath);
            $fileSize = Storage::disk('local')->size($storedPath);
            
            return response()->stream(function () use ($storedPath) {
                $stream = Storage::disk('local')->readStream($storedPath);
                
                if ($stream === false) {
                    throw new Exception('Unable to open file stream');
                }
                
                while (!feof($stream)) {
                    echo fread($stream, 8192); // Read in 8KB chunks
                    flush(); // Flush output to browser
                    
                    // Optional: Check if client disconnected
                    if (connection_aborted()) {
                        break;
                    }
                }
                
                fclose($stream);
            }, 200, [
                'Content-Type' => $mime,
                'Content-Length' => $fileSize,
                'Content-Disposition' => 'inline; filename="' . $file->filename . '"',
                'Cache-Control' => 'public, max-age=3600',
                'Accept-Ranges' => 'bytes'
            ]);
            
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    public function share(Request $request)
    {      
        $userId = Auth::id();

        try {
            $validated = $request->validate([
                'uuid' => 'required|string',
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Folder share failed: ' . $e->getMessage());
        }

        $uuid = $validated['uuid'];

        try {
            
            File::where('owner_id', $userId)
                ->where('uuid', $uuid)
                ->where('is_folder', true)
                ->update(['is_shared' => DB::raw('NOT is_shared')]);
            
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Folder share failed: ' . $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'folder shared successfully.');
    }

    public function create_folder(Request $request)
    {
        $validated = $request->validate([
            'filename' => 'required|string|min:1|max:255',
            'uuid' => 'required|string'
        ]);

        $uuid = $validated['uuid'];

        $userId = Auth::id();
        if ($validated['uuid'] != 0 && !File::where('owner_id', $userId)->where('uuid', $uuid)->where('is_folder', true)->count()) {
            return redirect()->back();
        }
        
        try {
            File::create([
                'uuid' => (new Client())->generateId(16),
                'filename' => $validated['filename'],
                'parent_uuid' => $uuid,
                'owner_id' => auth()->id(),
                'file_size' => 0,
                'is_folder' => true,
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Folder already exists.');
        }

        return redirect()->back()->with('success', 'Folder "' . $validated['filename'] . '" created!');
    }
}
