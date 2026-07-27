<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Expense Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .body {
            padding: 30px;
        }
        .summary-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        .summary-card .amount {
            font-size: 32px;
            font-weight: bold;
            color: #dc2626;
        }
        .summary-card .label {
            font-size: 14px;
            color: #6b7280;
            margin-top: 4px;
        }
        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .category-item:last-child {
            border-bottom: none;
        }
        .category-name {
            font-weight: 500;
            color: #374151;
        }
        .category-amount {
            font-weight: 600;
            color: #dc2626;
        }
        .no-expenses {
            text-align: center;
            color: #6b7280;
            padding: 30px 0;
            font-size: 16px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            background: #f9fafb;
            border-radius: 8px;
            margin-top: 12px;
            font-weight: 700;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Daily Expense Report</h1>
            <p>{{ now()->format('l, d F Y') }}</p>
        </div>
        <div class="body">
            <p style="font-size: 16px; color: #374151; margin-bottom: 20px;">
                Hi {{ $data['userName'] }}, here is what you spent on <strong>{{ now()->format('l, d F Y') }}</strong>.
            </p>

            @if($data['totalExpenses'] > 0)
                <div class="summary-card">
                    <div class="label">Total Spent Today</div>
                    <div class="amount">Rp {{ number_format($data['totalExpenses'], 0, ',', '.') }}</div>
                </div>

                <h3 style="margin: 0 0 12px; color: #374151;">Expenses by Category</h3>
                <ul class="category-list">
                    @foreach($data['expensesByCategory'] as $item)
                        <li class="category-item">
                            <span class="category-name">
                                @if($item->category)
                                    {{ $item->category->name }}
                                @else
                                    Uncategorized
                                @endif
                            </span>
                            <span class="category-amount">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="total-row">
                    <span>Total</span>
                    <span style="color: #dc2626;">Rp {{ number_format($data['totalExpenses'], 0, ',', '.') }}</span>
                </div>
            @else
                <div class="no-expenses">
                    🎉 No expenses recorded today!
                </div>
            @endif
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'FinanceApp') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
