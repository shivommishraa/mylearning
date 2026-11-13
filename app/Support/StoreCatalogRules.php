<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StoreCatalogRules
{
    private const RESTRICTED_TERMS = [
        'vegetable', 'vegetables', 'fruit', 'fruits', 'produce', 'meat', 'fish', 'chicken',
        'poultry', 'egg', 'eggs', 'mango', 'mangoes', 'spinach', 'tomato', 'tomatoes',
        'avocado', 'avocados', 'potato', 'potatoes', 'onion', 'onions', 'carrot', 'carrots',
        'cabbage', 'cauliflower', 'broccoli', 'cucumber', 'lettuce', 'brinjal', 'eggplant',
        'apple', 'apples', 'banana', 'bananas', 'orange', 'oranges', 'grape', 'grapes',
        'berry', 'berries', 'watermelon', 'papaya', 'guava', 'prawn', 'prawns', 'shrimp',
        'seafood', 'beef', 'pork', 'mutton', 'lamb',
    ];

    public static function isRestricted(string $value): bool
    {
        $normalized = Str::lower($value);

        if (preg_match('/\bvegetable\s+oil\b/u', $normalized)) {
            $normalized = preg_replace('/\bvegetable\s+oil\b/u', 'edible oil', $normalized);
        }

        if (preg_match('/\b(?:ketchup|sauce)\b/u', $normalized)) {
            $normalized = preg_replace('/\btomatoes?\b/u', 'seasoning', $normalized);
        }

        $terms = implode('|', array_map(
            static fn (string $term): string => preg_quote($term, '/'),
            self::RESTRICTED_TERMS,
        ));

        return preg_match('/(?<![\pL\pN])(?:'.$terms.')(?![\pL\pN])/u', $normalized) === 1;
    }

    public static function constrainStoreProducts(Builder $query): Builder
    {
        $query->where(function (Builder $products) {
            foreach (self::RESTRICTED_TERMS as $term) {
                if ($term === 'vegetable') {
                    $products->where(fn (Builder $name) => $name->where('name', 'not like', '%vegetable%')
                        ->orWhere(fn (Builder $oil) => $oil->where('name', 'like', '%vegetable%')->where('name', 'like', '%oil%')));
                } elseif ($term === 'egg') {
                    $products->where(fn (Builder $name) => $name->where('name', 'not like', '%egg%')
                        ->orWhere('name', 'like', '%eggless%'));
                } elseif ($term === 'tomato' || $term === 'tomatoes') {
                    $products->where(fn (Builder $name) => $name->where('name', 'not like', '%'.$term.'%')
                        ->orWhere(fn (Builder $sauce) => $sauce->where('name', 'like', '%'.$term.'%')
                            ->where(fn (Builder $type) => $type->where('name', 'like', '%sauce%')->orWhere('name', 'like', '%ketchup%'))));
                } else {
                    $products->where('name', 'not like', '%'.$term.'%');
                }
            }
        });

        return $query->whereDoesntHave('category', function (Builder $category) {
            $category->where(function (Builder $name) {
                foreach (self::RESTRICTED_TERMS as $term) {
                    $name->orWhere('name', 'like', '%'.$term.'%')
                        ->orWhere('slug', 'like', '%'.$term.'%');
                }
            });
        });
    }

    public static function forbiddenMessage(): string
    {
        return 'SSG MART only lists daily household and grocery essentials. This category or product is not allowed.';
    }
}
