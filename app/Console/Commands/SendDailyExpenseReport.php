<?php

namespace App\Console\Commands;

use App\Mail\DailyExpenseReport;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendDailyExpenseReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:daily-expense {--user= : Send report to a specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily expense report to all users at the end of the day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');
        $specificUserId = $this->option('user');

        $users = $specificUserId
            ? User::where('id', $specificUserId)->get()
            : User::all();

        if ($users->isEmpty()) {
            $this->warn('No users found.');
            return Command::SUCCESS;
        }

        $sentCount = 0;

        foreach ($users as $user) {
            $totalExpenses = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereDate('transaction_date', $today)
                ->sum('amount');

            $expensesByCategory = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
                ->with('category')
                ->where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereDate('transaction_date', $today)
                ->groupBy('category_id')
                ->orderBy('total', 'desc')
                ->get();

            $data = [
                'userName' => $user->name,
                'totalExpenses' => $totalExpenses,
                'expensesByCategory' => $expensesByCategory,
            ];

            Mail::to($user->email)->send(new DailyExpenseReport($data));
            $sentCount++;

            $this->info("Report sent to {$user->email}");
        }

        $this->info("Done! Sent {$sentCount} report(s).");
        return Command::SUCCESS;
    }
}
