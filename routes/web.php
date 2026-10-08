<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'index'])->name('store.home');
Route::get('/carrito', [StoreController::class, 'cart'])->name('cart.index');
Route::post('/carrito/{product}', [StoreController::class, 'addToCart'])->name('cart.add');
Route::post('/carrito/{product}/disminuir', [StoreController::class, 'decreaseCartItem'])->name('cart.decrease');
Route::delete('/carrito/{product}', [StoreController::class, 'removeFromCart'])->name('cart.remove');
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/seguimiento-pedido', [StoreController::class, 'trackOrders'])->name('tracking.orders');

Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
Route::post('/admin/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
    Route::post('categorias', [AdminCategoryController::class, 'store'])->name('categorias.store');
    Route::get('categorias', [AdminCategoryController::class, 'index'])->name('categorias.index');
    Route::get('categorias/{category}/editar', [AdminCategoryController::class, 'edit'])->name('categorias.edit');
    Route::put('categorias/{category}', [AdminCategoryController::class, 'update'])->name('categorias.update');
    Route::delete('categorias/{category}', [AdminCategoryController::class, 'destroy'])->name('categorias.destroy');
    Route::resource('productos', AdminProductController::class)->except(['show'])->parameters(['productos' => 'product']);
    Route::get('pedidos', [AdminOrderController::class, 'index'])->name('pedidos.index');
    Route::get('pedidos/{order}', [AdminOrderController::class, 'show'])->name('pedidos.show');
    Route::patch('pedidos/{order}', [AdminOrderController::class, 'update'])->name('pedidos.update');
});
