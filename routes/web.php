<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'index']); // ログインページ

// ▼ 管理画面 ▼
Auth::routes([
    'register' => false,
    'reset' => false,
]);

Route::get('/admin/top', [App\Http\Controllers\Admin\TopController::class, 'index'])->name('index');

// ▼ ユーザー管理 ▼
Route::get('/admin/user', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
Route::get('/admin/user/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('edit');
Route::post('/admin/user/edit-proc', [App\Http\Controllers\Admin\UserController::class, 'editProc']);
Route::get('/admin/user/add', [App\Http\Controllers\Admin\UserController::class, 'add'])->name('add');
Route::post('/admin/user/add-proc', [App\Http\Controllers\Admin\UserController::class, 'addProc']);
Route::get('/admin/user/delete-confirm', [App\Http\Controllers\Admin\UserController::class, 'deleteConfirm'])->name('delete-confirm');
Route::post('/admin/user/delete-proc', [App\Http\Controllers\Admin\UserController::class, 'deleteProc']);
// ▲ ユーザー管理 ▲

// ▼ 記事管理 ▼
Route::get('/admin/article', [App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('index');
Route::get('/admin/article/private', [App\Http\Controllers\Admin\ArticleController::class, 'private']);
Route::get('/admin/article/publish', [App\Http\Controllers\Admin\ArticleController::class, 'publish']);
Route::get('/admin/article/edit', [App\Http\Controllers\Admin\ArticleController::class, 'edit'])->name('edit');
Route::post('/admin/article/upload-image', [App\Http\Controllers\Admin\ArticleController::class, 'uploadImage']);
Route::post('/admin/article/edit-proc', [App\Http\Controllers\Admin\ArticleController::class, 'editProc']);
Route::get('/admin/article/delete-confirm', [App\Http\Controllers\Admin\ArticleController::class, 'deleteConfirm'])->name('delete-confirm');
Route::post('/admin/article/delete-proc', [App\Http\Controllers\Admin\ArticleController::class, 'deleteProc']);
// ▲ 記事管理 ▲

// ▼ カテゴリ管理 ▼
Route::get('/admin/category', [App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
Route::get('/admin/category/edit', [App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('edit');
Route::post('/admin/category/edit-proc', [App\Http\Controllers\Admin\CategoryController::class, 'editProc']);
Route::get('/admin/category/delete-confirm', [App\Http\Controllers\Admin\CategoryController::class, 'deleteConfirm'])->name('delete-confirm');
Route::post('/admin/category/delete-proc', [App\Http\Controllers\Admin\CategoryController::class, 'deleteProc']);
// ▲ カテゴリ管理 ▲

// ▼ ファイル管理 ▼
Route::get('/admin/file', [App\Http\Controllers\Admin\FileController::class, 'index'])->name('index');
Route::get('/admin/file/edit', [App\Http\Controllers\Admin\FileController::class, 'edit'])->name('edit');
Route::post('/admin/file/edit-proc', [App\Http\Controllers\Admin\FileController::class, 'editProc']);
Route::get('/admin/file/delete-confirm', [App\Http\Controllers\Admin\FileController::class, 'deleteConfirm'])->name('delete-confirm');
Route::post('/admin/file/delete-proc', [App\Http\Controllers\Admin\FileController::class, 'deleteProc']);
// ▲ファイル管理 ▲
// ▲ 管理画面 ▲

// ▼ 公開画面 ▼
Route::get('/', [App\Http\Controllers\PublicController::class, 'index'])->name('index')->middleware('ogp')->middleware('sidebar'); // トップページ
Route::get('/category/{name}', [App\Http\Controllers\PublicController::class, 'category'])->middleware('ogp')->middleware('sidebar'); // カテゴリ
Route::get('/date/{year}/{month}', [App\Http\Controllers\PublicController::class, 'date'])->middleware('ogp')->middleware('sidebar'); // 日時
Route::get('/date/{year}', [App\Http\Controllers\PublicController::class, 'date'])->middleware('ogp')->middleware('sidebar'); // 日時
Route::get('/{year}/{month}/{path}', [App\Http\Controllers\PublicController::class, 'article'])
    ->where([
        'year' => '[0-9]{4}',
        'month' => '[0-9]{2}',
        'path' => '[a-zA-Z0-9-_]+'
    ]) // その他は記事アクセスとみなす
    ->middleware('ogp')
    ->middleware('sidebar');
// ▲ 公開画面 ▲

// ▼ 管理画面用エラー ▼
Route::fallback(function() {
    return response()->view('errors.404', [], 404);
});
// ▲ 管理画面用エラー ▲
