<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        try {
            $month = Carbon::createFromFormat('Y-m-d', $request->query('month', now()->format('Y-m')) . '-01')->startOfMonth();
        } catch (\Throwable $e) {
            $month = now()->startOfMonth();
        }

        $rows = Expense::whereBetween('spent_on', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->orderByDesc('spent_on')->orderByDesc('id')->get();

        return view('admin.expenses.index', [
            'month'      => $month,
            'rows'       => $rows,
            'total'      => (float) $rows->sum('amount'),
            'byCategory' => $rows->groupBy('category')->map(fn ($g) => (float) $g->sum('amount'))->sortDesc()->all(),
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'spent_on' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'amount'   => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'note'     => ['nullable', 'string', 'max:200'],
        ]);

        Expense::create($data + ['user_id' => $request->user()->id]);

        return redirect()
            ->route('admin.expenses.index', ['month' => Carbon::parse($data['spent_on'])->format('Y-m')])
            ->with('status', __('Expense added.'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('status', __('Expense removed.'));
    }
}
