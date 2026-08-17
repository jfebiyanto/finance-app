<?php

namespace App\Http\Controllers;

use App\Mail\DatabaseBackupMail;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BackupController extends Controller
{
    /**
     * Show the backup page (download or email).
     */
    public function index()
    {
        $database = DB::connection()->getDatabaseName();
        $tableCount = count(DB::select('SHOW TABLES'));

        return view('backup.index', compact('database', 'tableCount'));
    }

    /**
     * Generate the SQL dump and return it as a downloadable .sql file.
     */
    public function download()
    {
        $dump = app(DatabaseBackupService::class)->dump();
        $filename = 'finance_app_backup_'.now()->format('Y-m-d_His').'.sql';

        return response($dump)
            ->header('Content-Type', 'application/sql')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Content-Length', (string) strlen($dump));
    }

    /**
     * Generate the SQL dump and email it to the requested address.
     */
    public function email(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $dump = app(DatabaseBackupService::class)->dump();
        $filename = 'finance_app_backup_'.now()->format('Y-m-d_His').'.sql';

        try {
            Mail::to($validated['email'])->send(new DatabaseBackupMail($filename, $dump));
        } catch (\Throwable $e) {
            logger()->error('Failed to email database backup: '.$e->getMessage());

            return back()->with('error', 'Failed to send the backup email. Please check the mail configuration and try again.');
        }

        return back()->with('success', 'Database backup sent to '.$validated['email'].'.');
    }
}
