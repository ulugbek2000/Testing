<?php
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Auth::routes();

Route::get('/', function () {
    return 'good!';
});
Route::get('/logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

Route::get('/endpoints', function (){
    $routeCollection = Illuminate\Support\Facades\Route::getRoutes();

    foreach ($routeCollection as $value) {
        echo $value->getName();
        echo '  -  ';
        echo $value->uri();
        echo '<br>';
    }
    // return response()->json(Route::getRoutes());
});
// Route::post('/register',[RegisterController::class , 'store']);
