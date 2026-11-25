<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContentPage;
use App\Models\Coupon;
use App\Models\HomeSection;
use App\Models\NavigationItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SSGMartSeeder extends Seeder
{
    private const MODULES = [
        'dashboard', 'products', 'categories', 'orders', 'coupons', 'customers',
        'content', 'pages', 'brands', 'menus', 'settings', 'enquiries', 'activity', 'admins', 'welcome_popup',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::MODULES as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                Permission::firstOrCreate(['name' => "{$module}.{$action}", 'guard_name' => 'web']);
            }
        }
        Permission::firstOrCreate(['name' => 'products.settings', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'welcome_popup.manage', 'guard_name' => 'web']);

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());
        $adminRole->syncPermissions([]);

        $admin = User::firstOrCreate(
            ['email' => env('SSG_SUPER_ADMIN_EMAIL', 'admin@ssgmart.local')],
            [
                'name' => 'SSG MART Super Admin',
                'password' => env('SSG_SUPER_ADMIN_PASSWORD', 'ChangeMe@123!'),
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles($superAdminRole);

        $categoryData = [
            ['name' => 'Grocery & Staples', 'slug' => 'grocery-staples', 'image' => null],
            ['name' => 'Atta & Rice', 'slug' => 'atta-rice', 'image' => null],
            ['name' => 'Dal & Pulses', 'slug' => 'dal-pulses', 'image' => null],
            ['name' => 'Oils & Ghee', 'slug' => 'oils-ghee', 'image' => null],
            ['name' => 'Masala & Spices', 'slug' => 'masala-spices', 'image' => null],
            ['name' => 'Snacks & Namkeen', 'slug' => 'snacks-namkeen', 'image' => null],
            ['name' => 'Biscuits & Chocolates', 'slug' => 'biscuits-chocolates', 'image' => null],
            ['name' => 'Tea & Coffee', 'slug' => 'tea-coffee', 'image' => null],
            ['name' => 'Cold Drinks', 'slug' => 'cold-drinks', 'image' => null],
            ['name' => 'Packaged Foods', 'slug' => 'packaged-foods', 'image' => null],
            ['name' => 'Personal Care', 'slug' => 'personal-care', 'image' => null],
            ['name' => 'Home Cleaning', 'slug' => 'home-cleaning', 'image' => null],
            ['name' => 'Toys', 'slug' => 'toys', 'image' => null],
            ['name' => 'Stationery', 'slug' => 'stationery', 'image' => null],
            ['name' => 'Household Essentials', 'slug' => 'household-essentials', 'image' => null],
            ['name' => 'UP Jaggery & Sweeteners', 'slug' => 'up-jaggery', 'image' => null],
        ];

        foreach ($categoryData as $index => $data) {
            Category::firstOrCreate(['slug' => $data['slug']], $data + ['sort_order' => $index]);
        }

        $brands = [
            ['Aashirvaad', 'aashirvaad'], ['Shakti Bhog', 'shakti-bhog'], ['Tata Sampann', 'tata-sampann'],
            ['Tata Salt', 'tata-salt'], ['Dhara', 'dhara'], ['Fortune', 'fortune'], ['Patanjali', 'patanjali'],
            ['India Gate', 'india-gate'], ['MDH', 'mdh'], ['Amul', 'amul'], ["Haldiram's", 'haldirams'],
            ["Lay's", 'lays'], ['Parle', 'parle'], ['Britannia', 'britannia'], ['Cadbury', 'cadbury'],
            ['Tata Tea', 'tata-tea'], ['Nescafé', 'nescafe'], ['Thums Up', 'thums-up'],
            ['Maggi', 'maggi'], ['Kissan', 'kissan'], ['Vim', 'vim'], ['Surf Excel', 'surf-excel'], ['Harpic', 'harpic'],
            ['Lizol', 'lizol'], ['Dove', 'dove'], ['Colgate', 'colgate'], ['Lifebuoy', 'lifebuoy'],
            ['Nivea', 'nivea'], ['Classmate', 'classmate'], ['Hot Wheels', 'hot-wheels'],
            ['Wonderchef', 'wonderchef'], ['Faber-Castell', 'faber-castell'],
            ['UP Local', 'up-local'],
        ];

        foreach ($brands as [$name, $slug]) {
            Brand::firstOrCreate(['slug' => $slug], ['name' => $name, 'is_active' => true]);
        }

        $products = [
            ['Tata Salt Iodised Crystal Salt', 'grocery-staples', 'tata-salt', 'SSG-SALT-01', 24, 28, '1 kg', 'Everyday iodised salt for the meals your family loves.', true],
            ['Tata Sampann Sugar', 'grocery-staples', 'tata-sampann', 'SSG-SUGAR-01', 48, 52, '1 kg', 'Fine-grain sugar for tea, sweets and everyday cooking.', false],
            ['Aashirvaad Shudh Chakki Atta', 'atta-rice', 'aashirvaad', 'SSG-ATTA-01', 265, 299, '5 kg', 'Whole wheat flour for soft rotis and everyday home cooking.', true],
            ['Shakti Bhog Chakki Atta', 'atta-rice', 'shakti-bhog', 'SSG-SHAKTI-ATTA-01', 249, 279, '5 kg', 'Whole wheat chakki atta for everyday rotis and parathas.', true],
            ['India Gate Classic Basmati Rice', 'atta-rice', 'india-gate', 'SSG-RICE-01', 189, 219, '1 kg', 'Aromatic long-grain basmati rice, packed for your pantry.', true],
            ['India Gate Dubar Basmati Rice', 'atta-rice', 'india-gate', 'SSG-RICE-DUBAR-01', 149, 169, '1 kg', 'Everyday basmati rice for pulao and family meals.', false],
            ['Aashirvaad Maida', 'atta-rice', 'aashirvaad', 'SSG-MAIDA-01', 38, 42, '500 g', 'Finely milled refined wheat flour for home baking and recipes.', false],
            ['Tata Sampann Toor Dal', 'dal-pulses', 'tata-sampann', 'SSG-TOOR-01', 145, 165, '1 kg', 'Unpolished toor dal for satisfying everyday meals.', true],
            ['Tata Sampann Moong Dal', 'dal-pulses', 'tata-sampann', 'SSG-MOONG-01', 132, null, '1 kg', 'Carefully packed moong dal for wholesome home cooking.', false],
            ['Tata Sampann Chana Dal', 'dal-pulses', 'tata-sampann', 'SSG-CHANA-01', 92, 105, '500 g', 'A pantry staple for dals, snacks and everyday North Indian meals.', false],
            ['Dhara Kachi Ghani Mustard Oil', 'oils-ghee', 'dhara', 'SSG-OIL-02', 159, 179, '1 litre', 'A bold, aromatic mustard oil for everyday North Indian cooking.', true],
            ['Fortune Refined Sunflower Oil', 'oils-ghee', 'fortune', 'SSG-SUNFLOWER-01', 145, 160, '1 litre', 'A light refined sunflower oil for everyday cooking.', false],
            ['Patanjali Cow Ghee', 'oils-ghee', 'patanjali', 'SSG-GHEE-01', 325, 349, '500 ml', 'Rich, comforting ghee for cooking and family recipes.', true],
            ['MDH Garam Masala', 'masala-spices', 'mdh', 'SSG-MASALA-01', 78, 89, '100 g', 'A warming North Indian spice blend for everyday home cooking.', true],
            ['MDH Haldi Powder', 'masala-spices', 'mdh', 'SSG-HALDI-01', 45, 50, '100 g', 'Ground turmeric for the everyday masalas in your kitchen.', false],
            ['Patanjali Besan (Gram Flour)', 'masala-spices', 'patanjali', 'SSG-BESAN-01', 92, null, '500 g', 'Finely milled gram flour for pakoras, kadhi and household recipes.', false],
            ["Haldiram's Aloo Bhujia", 'snacks-namkeen', 'haldirams', 'SSG-BHUJIA-01', 55, 60, '200 g', 'A crunchy namkeen favourite for tea time and sharing.', true],
            ["Lay's Classic Salted Chips", 'snacks-namkeen', 'lays', 'SSG-CHIPS-01', 20, null, '52 g', 'Light, crispy salted potato chips in a handy snack pack.', false],
            ['Parle-G Original Biscuits', 'biscuits-chocolates', 'parle', 'SSG-BISCUIT-01', 10, null, '250 g', 'The classic glucose biscuits for a quick cup-of-tea companion.', true],
            ['Parle 20-20 Butter Cookies', 'biscuits-chocolates', 'parle', 'SSG-PARLE-2020-01', 30, 35, '150 g', 'Crisp butter cookies for tea time and sharing at home.', false],
            ['Britannia Good Day Cashew Cookies', 'biscuits-chocolates', 'britannia', 'SSG-COOKIE-01', 35, 40, '200 g', 'Buttery, crisp cashew cookies for a little everyday treat.', false],
            ['Cadbury Dairy Milk Chocolate', 'biscuits-chocolates', 'cadbury', 'SSG-CHOC-01', 40, null, '80 g', 'Smooth milk chocolate, made for sharing a small happy moment.', true],
            ['Tata Tea Premium Leaf Tea', 'tea-coffee', 'tata-tea', 'SSG-TEA-01', 149, 165, '500 g', 'A full-bodied daily tea blend for your morning cup.', true],
            ['Nescafé Classic Instant Coffee', 'tea-coffee', 'nescafe', 'SSG-COFFEE-01', 185, null, '100 g', 'Rich instant coffee with a bold aroma and smooth finish.', false],
            ['Thums Up Strong Cola', 'cold-drinks', 'thums-up', 'SSG-THUMS-01', 40, null, '750 ml', 'A chilled, bold cola drink for your everyday get-togethers.', true],
            ['Thums Up Strong Cola Family Pack', 'cold-drinks', 'thums-up', 'SSG-THUMS-2L', 90, null, '2 litre', 'A share-size bottle of bold cola for family meals and gatherings.', false],
            ['Maggi 2-Minute Masala Noodles', 'packaged-foods', 'maggi', 'SSG-NOODLE-01', 14, 15, '70 g', 'Quick-cooking masala noodles for a convenient snack or light meal.', true],
            ['Kissan Tomato Ketchup', 'packaged-foods', 'kissan', 'SSG-KETCHUP-01', 99, 110, '500 g', 'A tangy table sauce for sandwiches, snacks and everyday meals.', false],
            ['Vim Lemon Dishwash Gel', 'home-cleaning', 'vim', 'SSG-VIM-01', 115, 129, '500 ml', 'A concentrated dishwash gel for everyday kitchen clean-up.', true],
            ['Surf Excel Easy Wash Detergent', 'home-cleaning', 'surf-excel', 'SSG-SURF-01', 112, 125, '1 kg', 'A reliable everyday laundry detergent for family clothes.', true],
            ['Harpic Power Plus Toilet Cleaner', 'home-cleaning', 'harpic', 'SSG-HARPIC-01', 105, 119, '500 ml', 'A powerful toilet-cleaning liquid for your home-care routine.', false],
            ['Lizol Disinfectant Floor Cleaner', 'home-cleaning', 'lizol', 'SSG-LIZOL-01', 125, 145, '500 ml', 'A fresh-scented floor cleaner for regular household care.', false],
            ['Dove Daily Shine Shampoo', 'personal-care', 'dove', 'SSG-DOVE-01', 189, 210, '340 ml', 'A nourishing daily shampoo for soft, manageable hair.', true],
            ['Colgate Strong Teeth Toothpaste', 'personal-care', 'colgate', 'SSG-COLGATE-01', 98, 110, '200 g', 'A family-size fluoride toothpaste for the daily oral-care routine.', true],
            ['Lifebuoy Total Handwash Refill', 'personal-care', 'lifebuoy', 'SSG-LIFE-01', 79, 89, '750 ml', 'A convenient handwash refill for everyday family use.', false],
            ['Nivea Fresh Active Deodorant', 'personal-care', 'nivea', 'SSG-NIVEA-01', 199, 225, '150 ml', 'A clean, fresh everyday deodorant in a portable spray can.', false],
            ['Classmate Single Line Notebook', 'stationery', 'classmate', 'SSG-NOTE-01', 55, null, '172 pages', 'A durable single-line notebook for notes, lists and schoolwork.', true],
            ['Faber-Castell Colour Pencils', 'stationery', 'faber-castell', 'SSG-PENCIL-01', 120, 140, '12 colours', 'Bright colour pencils for creative projects and everyday drawing.', false],
            ['Hot Wheels Die-Cast Toy Car', 'toys', 'hot-wheels', 'SSG-TOY-01', 149, null, '1 piece', 'A detailed die-cast toy car for play and collecting.', true],
            ['Wonderchef Aluminium Foil Roll', 'household-essentials', 'wonderchef', 'SSG-FOIL-01', 95, 110, '9 m', 'A handy kitchen foil roll for wrapping and everyday food storage.', false],
            ['Muzaffarnagar Jaggery (Gur)', 'up-jaggery', 'up-local', 'SSG-GUR-01', 65, null, '500 g', 'Packed jaggery from Muzaffarnagar, Uttar Pradesh, for traditional tea and home recipes.', true],
            ['Muzaffarnagar Gur Powder', 'up-jaggery', 'up-local', 'SSG-GUR-POWDER-01', 78, null, '500 g', 'Uttar Pradesh jaggery in an easy-to-use powder for chai and home recipes.', false],
        ];

        foreach ($products as [$name, $category, $brand, $sku, $price, $compare, $unit, $description, $featured]) {
            Product::firstOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => Category::where('slug', $category)->value('id'),
                    'brand_id' => Brand::where('slug', $brand)->value('id'),
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => $description,
                    'unit' => $unit,
                    'price' => $price,
                    'compare_at_price' => $compare,
                    'stock' => 30,
                    'image' => null,
                    'is_featured' => $featured,
                    'is_active' => true,
                ],
            );
        }

        Coupon::firstOrCreate(
            ['code' => 'SSG10'],
            [
                'description' => 'Save on pantry, personal care and household essentials.',
                'type' => 'percent',
                'value' => 10,
                'minimum_order' => 299,
                'maximum_discount' => 100,
                'is_active' => true,
            ],
        );
        Coupon::where('code', 'FRESH10')->update(['is_active' => false]);

        Banner::firstOrCreate(
            ['title' => 'Everyday essentials for your home'],
            [
                'eyebrow' => 'TRUSTED INDIAN BRANDS · GREATER NOIDA',
                'subtitle' => 'Atta, chawal, dal, chai and everyday household essentials, all close to home.',
                'image' => null,
                'button_text' => 'Shop daily essentials',
                'button_url' => '/shop',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        $testimonials = [
            ['name' => 'Ananya S.', 'location' => 'Greater Noida', 'quote' => 'My pantry staples arrive right on time, and the ordering experience is wonderfully simple.', 'rating' => 5],
            ['name' => 'Rohit M.', 'location' => 'Bisrakh Jalalpur', 'quote' => 'Finally, a neighbourhood store that makes everyday grocery shopping feel effortless.', 'rating' => 5],
            ['name' => 'Meera K.', 'location' => 'Sector 1', 'quote' => 'It is easy to find the household essentials we need for the week, all in one place.', 'rating' => 5],
        ];

        foreach ($testimonials as $data) {
            Testimonial::firstOrCreate(['name' => $data['name']], $data + ['is_active' => true]);
        }

        foreach (config('ssg.defaults') as $key => $value) {
            SiteSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => in_array($key, ['delivery_fee', 'free_delivery_minimum']) ? 'delivery' : (in_array($key, ['show_product_prices', 'enable_add_to_cart']) ? 'products' : 'general')],
            );
        }

        foreach ([
            ['title' => 'About SSG MART', 'slug' => 'about-us', 'body' => "SSG MART is your neighbourhood daily-needs store in Greater Noida. We bring together trusted pantry staples, packaged foods, personal care and household essentials.\n\nFind us at ".config('ssg.defaults.address')."\nCall us at ".config('ssg.defaults.phone').' or write to '.config('ssg.defaults.email').'.'],
            ['title' => 'Delivery information', 'slug' => 'delivery-information', 'body' => 'We deliver across our neighbourhood in Greater Noida. Delivery charges and free-delivery thresholds are shown in your basket before you place an order. Add accurate delivery details at checkout so our team can find you.'],
            ['title' => 'Privacy policy', 'slug' => 'privacy-policy', 'body' => 'We use your account and delivery details to fulfil orders and support your shopping experience. We do not store payment-card details. Configure your production privacy notice and applicable data-protection contact before launch.'],
            ['title' => 'Terms of service', 'slug' => 'terms-of-service', 'body' => 'Product availability, prices and promotions may change. Orders are confirmed subject to stock and delivery availability. Please contact SSG MART if you need help with an order. Configure final legal terms for your business before launch.'],
        ] as $page) {
            ContentPage::firstOrCreate(
                ['slug' => $page['slug']],
                $page + ['meta_title' => $page['title'].' · SSG MART', 'meta_description' => 'Learn more about '.$page['title'].' at SSG MART.', 'is_published' => true],
            );
        }

        foreach ([
            ['eyebrow' => 'THE EVERYDAY LINE-UP', 'title' => 'Everyday essentials, picked for you', 'subtitle' => 'Trusted home and pantry favourites, ready for your weekly basket.', 'type' => 'featured', 'category_id' => null, 'product_limit' => 8, 'sort_order' => 0],
            ['eyebrow' => 'THE PANTRY SHELF', 'title' => 'The pantry, beautifully stocked', 'subtitle' => 'Staples for the meals and moments that make a home.', 'type' => 'category', 'category_id' => Category::where('slug', 'atta-rice')->value('id'), 'product_limit' => 6, 'sort_order' => 1],
            ['eyebrow' => 'GOOD VALUE, EVERY DAY', 'title' => 'Little savings, big difference', 'subtitle' => 'A few offers on the things you reach for most.', 'type' => 'deals', 'category_id' => null, 'product_limit' => 6, 'sort_order' => 2],
            ['eyebrow' => 'POPULAR IN THE NEIGHBOURHOOD', 'title' => 'Most-loved essentials', 'subtitle' => 'The products local households order again and again.', 'type' => 'best_sellers', 'category_id' => null, 'product_limit' => 6, 'sort_order' => 3],
            ['eyebrow' => 'JUST ADDED TO THE STORE', 'title' => 'New to the shelves', 'subtitle' => 'Discover something useful for your home.', 'type' => 'latest', 'category_id' => null, 'product_limit' => 6, 'sort_order' => 4],
        ] as $section) {
            HomeSection::firstOrCreate(['title' => $section['title']], $section + ['is_active' => true]);
        }

        foreach ([
            ['label' => 'Shop all', 'url' => '/shop', 'location' => 'header', 'sort_order' => 0],
            ['label' => 'Our neighbourhood', 'url' => '/contact', 'location' => 'header', 'sort_order' => 1],
            ['label' => 'Contact us', 'url' => '/contact', 'location' => 'footer', 'sort_order' => 0],
            ['label' => 'Delivery information', 'url' => '/pages/delivery-information', 'location' => 'footer', 'sort_order' => 1],
        ] as $item) {
            NavigationItem::firstOrCreate(
                ['label' => $item['label'], 'location' => $item['location']],
                $item + ['is_active' => true],
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
