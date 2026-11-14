<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Support\SalesMath;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public const PRESETS = [
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_year'  => 'This year',
        'last_year'  => 'Last year',
        'all'        => 'All time',
        'custom'     => 'Custom dates',
    ];

    public function index(Request $request)
    {
        [$from, $to, $preset] = $this->range($request);
        $category = $this->category($request);

        $query = $this->filtered($from, $to, $category);
        $total = (float) (clone $query)->sum('amount');

        // Totals by category ignore the category filter, so the bars always
        // show how the whole period divides up.
        $byCategory = $this->filtered($from, $to, null)
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')
            ->pluck('total', 'category')->map(fn ($v) => (float) $v)->all();

        // The same length of time just before, for "was it more or less?".
        $before = null;

        if ($from && in_array($preset, ['this_month', 'last_month', 'this_year', 'last_year'], true)) {
            [$prevFrom, $prevTo] = str_contains($preset, 'month')
                ? [$from->copy()->subMonthNoOverflow()->startOfMonth(), $from->copy()->subMonthNoOverflow()->endOfMonth()]
                : [$from->copy()->subYear()->startOfYear(), $from->copy()->subYear()->endOfYear()];

            $before = [
                'label' => str_contains($preset, 'month') ? $prevFrom->format('F Y') : $prevFrom->format('Y'),
                'total' => (float) $this->filtered($prevFrom, $prevTo, $category)->sum('amount'),
            ];
        }

        return view('admin.expenses.index', [
            'rows'       => $query->orderByDesc('spent_on')->orderByDesc('id')->paginate(50)->withQueryString(),
            'total'      => $total,
            'allTotal'   => (float) array_sum($byCategory),
            'byCategory' => $byCategory,
            'byMonth'    => $this->byMonth($from, $to, $category),
            'before'     => $before,
            'from'       => $from,
            'to'         => $to,
            'preset'     => $preset,
            'presets'    => self::PRESETS,
            'category'   => $category,
            'categories' => Expense::CATEGORIES,
            'query'      => array_filter([
                'range' => $preset, 'category' => $category,
                'from'  => $preset === 'custom' ? $from?->toDateString() : null,
                'to'    => $preset === 'custom' ? $to?->toDateString() : null,
            ]),
        ]);
    }

    /** The filtered list as a spreadsheet. A CSV opens in Excel and imports into Google Sheets. */
    public function export(Request $request)
    {
        [$from, $to, $preset] = $this->range($request);
        $category = $this->category($request);

        $rows  = $this->filtered($from, $to, $category)->orderBy('spent_on')->orderBy('id')->get();
        $label = $preset === 'all' ? 'all-time' : $from->format('Y-m-d') . '-to-' . $to->format('Y-m-d');
        $name  = 'expenses-' . $label . ($category ? '-' . $category : '') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // so Excel reads Bangla notes correctly
            fputcsv($out, ['Date', 'Category', 'Note', 'Amount']);

            foreach ($rows as $row) {
                fputcsv($out, array_map([SalesMath::class, 'csvCell'], [
                    $row->spent_on->format('Y-m-d'),
                    $row->category_label,
                    (string) $row->note,
                    number_format((float) $row->amount, 2, '.', ''),
                ]));
            }

            fputcsv($out, []);
            fputcsv($out, ['', '', 'Total', number_format((float) $rows->sum('amount'), 2, '.', '')]);
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
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

        // Land on a view that actually contains the new entry.
        $date  = Carbon::parse($data['spent_on']);
        $range = $date->isSameMonth(now()) ? 'this_month' : ($date->isSameYear(now()) ? 'this_year' : 'all');

        return redirect()->route('admin.expenses.index', ['range' => $range])->with('status', __('Expense added.'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('status', __('Expense removed.'));
    }

    /** @return array{0:?Carbon, 1:?Carbon, 2:string} */
    private function range(Request $request): array
    {
        $preset = $request->query('range', 'this_month');
        $preset = isset(self::PRESETS[$preset]) ? $preset : 'this_month';

        if ($preset === 'custom') {
            try {
                $from = Carbon::parse((string) $request->query('from'))->startOfDay();
                $to   = Carbon::parse((string) $request->query('to'))->endOfDay();

                if ($from->lte($to)) {
                    return [$from, $to, 'custom'];
                }
            } catch (\Throwable $e) {
                // fall through to this month
            }

            $preset = 'this_month';
        }

        return match ($preset) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth(), $preset],
            'this_year'  => [now()->startOfYear(), now()->endOfYear(), $preset],
            'last_year'  => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear(), $preset],
            'all'        => [null, null, $preset],
            default      => [now()->startOfMonth(), now()->endOfMonth(), 'this_month'],
        };
    }

    private function category(Request $request): ?string
    {
        $category = $request->query('category');

        return is_string($category) && isset(Expense::CATEGORIES[$category]) ? $category : null;
    }

    private function filtered(?Carbon $from, ?Carbon $to, ?string $category)
    {
        return Expense::query()
            ->when($from && $to, fn ($q) => $q->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()]))
            ->when($category, fn ($q) => $q->where('category', $category));
    }

    /** Month totals inside the chosen dates, newest first. */
    private function byMonth(?Carbon $from, ?Carbon $to, ?string $category): array
    {
        return $this->filtered($from, $to, $category)->get(['spent_on', 'amount'])
            ->groupBy(fn ($e) => $e->spent_on->format('Y-m'))
            ->map(fn ($g, $key) => [
                'label' => Carbon::createFromFormat('Y-m-d', $key . '-01')->format('F Y'),
                'count' => $g->count(),
                'total' => (float) $g->sum('amount'),
            ])
            ->sortKeysDesc()->values()->all();
    }
}
