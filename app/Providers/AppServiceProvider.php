<?php

namespace App\Providers;

use App\Http\Controllers\API\OrderController;
use App\Models\Order;
use App\Models\User;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Policies\ProductPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderController::class);
        Gate::define('create', function (User $user) {
            // Grant access to any authenticated user, or check if the user is admin
            return $user !== null && ($user->is_admin || $user->id); // Allow all authenticated user           
        });
        Gate::define('update', function (User $user, Order $order) {
            return $user->id === $order->user_id || $user->is_admin;
        });

        Gate::define('delete', function (User $user, Order $order) {
            return $user->id === $order->user_id || $user->is_admin;
        });

        Gate::define('view', function (User $user, Order $order) {
            return $user->id === $order->user_id;
        });

        Gate::define('viewAny', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('mark-as-shipped', function (User $user) {
            return $user->isAdmin(); //(admin)
        });

        Gate::define('Mark-As-Delivered', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('Mark-As-Completed', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('mark-as-cancelled', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('mark-as-refunded', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('AddReview', function (User $user) {
            return $user !== null && !$user->is_admin;
        });

        Gate::define('UpdateReview', function (User $user) {
            return $user !== null && !$user->is_admin;
        });

        Gate::define('AddProductToWishlist', function (User $user) {
            return $user !== null && !$user->is_admin;
        });

        Gate::define('DeleteMyWishlist', function (User $user) {
            return $user !== null && !$user->is_admin;
        });


    }
}