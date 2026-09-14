<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeFileDocument;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class EmployeeFileDocumentController extends Controller
{
    /**
     * Store uploaded employee documents.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => __('Unauthorized.')], 401);
        }

        $user = Auth::user();
        $isEmployee = ($user->type === 'employee');

        $validator = Validator::make($request->all(), [
            'employee_id'   => 'required|exists:employees,id',
            'document_type' => 'required|string|max:100',
            'document_name' => 'nullable|string|max:255',
            'documents'     => 'required|array|min:1',
            'documents.*'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ], [
            'documents.required'   => __('Please select at least one file to upload.'),
            'documents.*.mimes'    => __('Only PDF, JPG, JPEG, and PNG files are allowed.'),
            'documents.*.max'      => __('Each file size must not exceed 5MB.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $employeeId = (int) $request->input('employee_id');
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => __('Employee not found.')], 440);
        }

        // Permission check: Only Company or users with explicit Create Employee File Document permission
        $canCreate = $user->can('Create Employee File Document') || $user->type === 'company';

        if (!$canCreate) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $docType = $request->input('document_type', 'Other');
        $customDocName = trim($request->input('document_name', ''));

        $dir = 'uploads/employee_documents/';
        $storagePath = storage_path('app/public/' . $dir);
        $publicPath  = public_path($dir);

        if (!file_exists($storagePath)) {
            @mkdir($storagePath, 0777, true);
        }
        if (!file_exists($publicPath)) {
            @mkdir($publicPath, 0777, true);
        }

        $createdDocs = [];
        $files = $request->file('documents');

        foreach ($files as $fileObj) {
            if ($fileObj && $fileObj->isValid()) {
                $filenameWithExt = $fileObj->getClientOriginalName();
                $rawFilename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $cleanFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $rawFilename);
                $extension = strtolower($fileObj->getClientOriginalExtension());
                $fileNameToStore = $cleanFilename . '_' . time() . '_' . rand(100, 999) . '.' . $extension;

                $fileSize = $fileObj->getSize();
                $mimeType = $fileObj->getMimeType();

                // Save to storage/app/public/uploads/employee_documents/
                $fileObj->move($storagePath, $fileNameToStore);

                // Copy to public fallback directory
                @copy($storagePath . '/' . $fileNameToStore, $publicPath . '/' . $fileNameToStore);

                $docDisplayName = !empty($customDocName) ? $customDocName : $filenameWithExt;

                $fileDoc = EmployeeFileDocument::create([
                    'employee_id'   => $employee->id,
                    'document_type' => $docType,
                    'document_name' => $docDisplayName,
                    'file_path'     => $dir . $fileNameToStore,
                    'file_name'     => $fileNameToStore,
                    'mime_type'     => $mimeType,
                    'file_size'     => $fileSize,
                    'uploaded_by'   => $user->id,
                ]);

                $fileDoc->load('uploader');
                $createdDocs[] = $fileDoc;
            }
        }

        return response()->json([
            'success'   => true,
            'message'   => __('Document(s) uploaded successfully.'),
            'documents' => $createdDocs,
        ]);
    }

    /**
     * Download an uploaded document.
     */
    public function download($id)
    {
        $doc = EmployeeFileDocument::findOrFail($id);
        $user = Auth::user();

        // Permission check: Requires Download permission or company user
        $canDownload = $user->can('Download Employee File Document') || $user->type === 'company';

        if (!$canDownload) {
            abort(403, __('Permission denied.'));
        }

        $dir = 'uploads/employee_documents/';
        $storageFile = storage_path('app/public/' . $dir . $doc->file_name);
        $publicFile  = public_path($dir . $doc->file_name);

        $path = null;
        if (file_exists($storageFile)) {
            $path = $storageFile;
        } elseif (file_exists($publicFile)) {
            $path = $publicFile;
        }

        if (!$path) {
            return redirect()->back()->with('error', __('File not found on server.'));
        }

        return response()->download($path, $doc->file_name, [
            'Content-Type' => $doc->mime_type ?? 'application/octet-stream',
        ]);
    }

    /**
     * Preview inline document (PDF or Image).
     */
    public function preview($id)
    {
        $doc = EmployeeFileDocument::findOrFail($id);
        $user = Auth::user();

        $canView = $user->can('View Employee File Document') || $user->can('Manage Employee File Document') || $user->type === 'company';

        if (!$canView) {
            abort(403, __('Permission denied.'));
        }

        $dir = 'uploads/employee_documents/';
        $storageFile = storage_path('app/public/' . $dir . $doc->file_name);
        $publicFile  = public_path($dir . $doc->file_name);

        $path = null;
        if (file_exists($storageFile)) {
            $path = $storageFile;
        } elseif (file_exists($publicFile)) {
            $path = $publicFile;
        }

        if (!$path) {
            return redirect()->back()->with('error', __('File not found on server.'));
        }

        return response()->file($path, [
            'Content-Type' => $doc->mime_type ?? 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $doc->file_name . '"',
        ]);
    }

    /**
     * Delete an uploaded document.
     */
    public function destroy($id)
    {
        $doc = EmployeeFileDocument::findOrFail($id);
        $user = Auth::user();

        $canDelete = $user->can('Delete Employee File Document') || $user->type === 'company';

        if (!$canDelete) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $dir = 'uploads/employee_documents/';
        $storageFile = storage_path('app/public/' . $dir . $doc->file_name);
        $publicFile  = public_path($dir . $doc->file_name);

        if (file_exists($storageFile)) {
            @unlink($storageFile);
        }
        if (file_exists($publicFile)) {
            @unlink($publicFile);
        }

        $doc->delete();

        return response()->json([
            'success' => true,
            'message' => __('Document deleted successfully.')
        ]);
    }
}
