<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\SocialiteController;
use Laravel\Fortify\Features;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

// Social Auth Routes
Route::middleware(['web'])->group(function () {
    Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->middleware(['guest'])
        ->name('socialite.redirect');
        
    Route::get('auth/{provider}/callback', [SocialiteController::class, 'callback'])
        ->middleware(['web', 'guest'])
        ->name('socialite.callback');
});

// Protected Routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Welcome Route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::get('/debug-routes', function() {
    return [
        'has_2fa_route' => Route::has('two-factor.login'),
        'all_routes' => collect(Route::getRoutes())->map(function($route) {
            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName()
            ];
        })->filter(function($route) {
            return str_contains($route['uri'], 'two-factor');
        })->values()
    ];
});


// Add near your other debug routes
Route::get('/debug-all', function() {
    return [
        'session' => [
            'id' => session()->getId(),
            'all' => session()->all(),
            'login_id' => session('login.id'),
        ],
        'auth' => [
            'check' => Auth::check(),
            'id' => Auth::id(),
            'user' => Auth::user() ? [
                'id' => Auth::user()->id,
                'email' => Auth::user()->email,
                'has_2fa' => !empty(Auth::user()->two_factor_secret),
                'confirmed_2fa' => !empty(Auth::user()->two_factor_confirmed_at),
            ] : null,
        ],
        'routes' => [
            'has_2fa_route' => Route::has('two-factor.login'),
            'current_url' => request()->url(),
        ],
        'config' => [
            'fortify_features' => config('fortify.features'),
            'session_driver' => config('session.driver'),
        ]
    ];
});


Route::get('/debug-session', function() {
    return [
        'session_id' => session()->getId(),
        'all_session_data' => session()->all(),
        'auth' => [
            'check' => Auth::check(),
            'id' => Auth::id(),
        ],
        '2fa_required' => session('login.id'),
    ];
});



Route::get('/test-profile-photo', function () {
    $user = auth()->user();
    
    // Test storage
    $testFile = 'test-profile-photo.txt';
    Storage::disk('public')->put($testFile, 'test content');
    
    // Get all directories in public disk
    $directories = Storage::disk('public')->directories();
    
    // Get all files in public disk
    $files = Storage::disk('public')->files();
    
    // Check if profile-photos directory exists
    $hasProfilePhotosDir = Storage::disk('public')->exists('profile-photos');
    
    // Database check
    $hasPhotoColumn = Schema::hasColumn('users', 'profile_photo_path');
    
    return [
        'user_id' => $user->id,
        'current_photo_path' => $user->profile_photo_path,
        'storage_test_file_exists' => Storage::disk('public')->exists($testFile),
        'directories' => $directories,
        'files' => $files,
        'has_profile_photos_dir' => $hasProfilePhotosDir,
        'has_photo_column' => $hasPhotoColumn,
        'storage_path' => storage_path('app/public'),
        'public_path' => public_path('storage'),
    ];
});


Route::middleware(['auth'])->group(function () {
    Route::get('/test-storage-config', function () {
        return [
            'filesystems_config' => config('filesystems'),
            'storage_path_exists' => is_dir(storage_path('app/public')),
            'public_storage_link_exists' => is_dir(public_path('storage')),
            'storage_permissions' => [
                'storage_app' => substr(sprintf('%o', fileperms(storage_path('app'))), -4),
                'storage_app_public' => is_dir(storage_path('app/public')) ? 
                    substr(sprintf('%o', fileperms(storage_path('app/public'))), -4) : 'directory doesn\'t exist',
            ],
            'can_write_to_storage' => is_writable(storage_path('app/public')),
        ];
    })->name('test.storage.config');
});


Route::get('/debug-profile', function() {
    $user = auth()->user();
    
    return [
        'user' => [
            'id' => $user->id,
            'profile_photo_path' => $user->profile_photo_path,
            'profile_photo_url' => $user->profile_photo_url,
        ],
        'storage' => [
            'profile_photos_exists' => Storage::disk('public')->exists('profile-photos'),
            'files_in_profile_photos' => Storage::disk('public')->files('profile-photos'),
            'profile_photo_exists' => $user->profile_photo_path ? 
                Storage::disk('public')->exists($user->profile_photo_path) : null,
        ],
        'jetstream_config' => [
            'manages_profile_photos' => config('jetstream.features') && in_array(
                Laravel\Jetstream\Features::profilePhotos(), 
                config('jetstream.features')
            ),
            'profile_photo_disk' => config('jetstream.profile_photo_disk'),
        ],
    ];
});