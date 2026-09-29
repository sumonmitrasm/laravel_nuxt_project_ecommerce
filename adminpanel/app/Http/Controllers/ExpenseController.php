<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    private const CATEGORIES = ['ads', 'delivery', 'office', 'salary', 'other'];

    public function index(Request $request)
    {
        $period = $request->validate(['period' => ['nullable', Rule::in(['today', 'month', 'year', 'all'])]])['period'] ?? 'month';
        [$from, $to, $label] = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay(), 'Today'],
            'year' => [now()->startOfYear(), now()->endOfYear(), 'This year'],
            'all' => [null, null, 'All time'],
            default => [now()->startOfMonth(), now()->endOfMonth(), 'This month'],
        };
        $base = Expense::query()->when($from, fn ($query) => $query->whereBetween('expense_date', [$from, $to]));
        $expenses = (clone $base)->latest('expense_date')->paginate(20)->withQueryString();
        $total = (float) (clone $base)->sum('amount');
        $byCategory = (clone $base)->selectRaw('category, SUM(amount) as total')->groupBy('category')->pluck('total', 'category');
        return view('admin.expense.index', ['expenses' => $expenses, 'total' => $total, 'byCategory' => $byCategory, 'period' => $period, 'periodLabel' => $label, 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request)
    {
        $expense = Expense::create($this->validated($request) + ['admin_id' => Auth::guard('admin')->id()]);
        if ($request->expectsJson()) return response()->json(['message' => 'Expense added successfully.']);
        return back()->with('success', 'Expense added successfully.');
    }

    public function edit(Expense $expense)
    {
        return view('admin.expense.edit', ['expense' => $expense, 'categories' => self::CATEGORIES]);
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validated($request));
        if ($request->expectsJson()) return response()->json(['message' => 'Expense updated successfully.']);
        return back()->with('success', 'Expense updated successfully.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $expense->delete();
        if ($request->expectsJson()) return response()->json(['message' => 'Expense deleted successfully.']);
        return back()->with('success', 'Expense deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'title' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
