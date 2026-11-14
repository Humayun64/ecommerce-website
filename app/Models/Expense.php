<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Money the shop spent that is not the cost of the products themselves. */
class Expense extends Model
{
    protected $fillable = ['spent_on', 'category', 'amount', 'note', 'user_id'];

    protected $casts = [
        'spent_on' => 'date',
        'amount'   => 'decimal:2',
    ];

    public const CATEGORIES = [
        'ads'       => 'Advertising',
        'courier'   => 'Courier and delivery',
        'packaging' => 'Packaging',
        'salary'    => 'Salaries',
        'rent'      => 'Rent and utilities',
        'software'  => 'Software and hosting',
        'fees'      => 'Payment and bank fees',
        'other'     => 'Other',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }
}
