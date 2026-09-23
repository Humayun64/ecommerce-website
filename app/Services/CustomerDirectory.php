<?php

namespace App\Services;

use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Builds the customer list out of orders rather than user accounts.
 *
 * Most orders here are placed as guests, so grouping by account would hide
 * the majority of real customers. The phone number is the identity.
 */
class CustomerDirectory
{
    private const DEAD = ['cancelled', 'returned'];

    public function list(Request $request)
    {
        $dead = "'" . implode("','", self::DEAD) . "'";

        $query = DB::table('orders')
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->groupBy('customer_phone')
            ->select('customer_phone as phone')
            ->selectRaw('MAX(customer_name) as name')
            ->selectRaw('MAX(customer_email) as email')
            ->selectRaw('MAX(user_id) as user_id')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw("COALESCE(SUM(CASE WHEN status NOT IN ({$dead}) THEN total ELSE 0 END), 0) as spent")
            ->selectRaw("SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_count")
            ->selectRaw("SUM(CASE WHEN status IN ({$dead}) THEN 1 ELSE 0 END) as failed_count")
            ->selectRaw('MAX(created_at) as last_order_at');

        if ($term = trim((string) $request->search)) {
            $query->where(function ($q) use ($term) {
                $q->where('customer_phone', 'like', "%{$term}%")
                  ->orWhere('customer_name', 'like', "%{$term}%")
                  ->orWhere('customer_email', 'like', "%{$term}%");
            });
        }

        if ($request->type === 'guest') {
            $query->whereNull('user_id');
        } elseif ($request->type === 'registered') {
            $query->whereNotNull('user_id');
        }

        match ($request->sort) {
            'spent'  => $query->orderByRaw('spent DESC'),
            'orders' => $query->orderByRaw('orders_count DESC'),
            'name'   => $query->orderByRaw('name ASC'),
            default  => $query->orderByRaw('last_order_at DESC'),
        };

        $customers = $query->paginate(25)->withQueryString();

        $this->attachProfiles($customers->getCollection());

        return $customers;
    }

    /** Everything known about one phone number. */
    public function show(string $phone): array
    {
        $phone = CustomerProfile::normalise($phone);

        $orders = Order::withCount('items')
            ->where('customer_phone', $phone)
            ->latest()
            ->get();

        $live = $orders->whereNotIn('status', self::DEAD);

        return [
            'phone'     => $phone,
            'profile'   => CustomerProfile::firstOrNew(['phone' => $phone]),
            'user'      => User::where('phone', $phone)->first(),
            'orders'    => $orders,
            'name'      => $orders->first()?->customer_name,
            'email'     => $orders->pluck('customer_email')->filter()->first(),
            'spent'     => (float) $live->sum('total'),
            'delivered' => $orders->where('status', 'delivered')->count(),
            'failed'    => $orders->whereIn('status', self::DEAD)->count(),
            'reviews'   => Review::with('product')->where('reviewer_phone', $phone)->latest()->get(),
        ];
    }

    /** Registered accounts that have not bought anything yet. */
    public function accountsWithoutOrders(Request $request)
    {
        // Done with subqueries rather than a relation, so this works whatever
        // the User model happens to define.
        return User::where('is_admin', false)
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)->from('orders')->whereColumn('orders.user_id', 'users.id');
            })
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)->from('orders')
                  ->whereColumn('orders.customer_phone', 'users.phone')
                  ->whereNotNull('users.phone');
            })
            ->when($request->search, fn ($q, $term) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")))
            ->latest()
            ->paginate(25, ['*'], 'accounts')
            ->withQueryString();
    }

    /**
     * How often this customer refuses the parcel. On cash on delivery that
     * is the quietest way to lose money, so it earns a column of its own.
     */
    public static function refusalRate(int $delivered, int $failed): ?int
    {
        $settled = $delivered + $failed;

        return $settled > 0 ? (int) round($failed / $settled * 100) : null;
    }

    private function attachProfiles($rows): void
    {
        $phones = collect($rows)->pluck('phone')->all();

        $profiles = CustomerProfile::whereIn('phone', $phones)->get()->keyBy('phone');

        foreach ($rows as $row) {
            $profile          = $profiles->get($row->phone);
            $row->is_blocked  = (bool) ($profile?->is_blocked);
            $row->note        = $profile?->note;
        }
    }
}
